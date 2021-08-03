<?php


namespace Latus\Installer;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Latus\Installer\Database\DynamicSeeder;
use Latus\Permissions\Models\User;
use Latus\Permissions\Repositories\Contracts\UserRepository;
use Latus\Permissions\Services\UserService;
use Latus\Plugins\Composer\Conductor;
use Latus\Plugins\Composer\ProxyPackage;
use Latus\Plugins\Exceptions\ComposerCLIException;
use Latus\Plugins\Models\ComposerRepository;
use Latus\Plugins\Services\ComposerRepositoryService;
use Latus\Plugins\Services\ThemeService;
use Symfony\Component\Console\Command\Command;

class Installer
{

    public const DEFAULT_THEME = 'latusprojects/latus-2021-theme';
    public const DEFAULT_THEME_VERSION = 'dev-develop';

    protected \Illuminate\Console\Command|null $command = null;
    protected ComposerRepositoryService $composerRepositoryService;
    protected ThemeService $themeService;

    public function __construct(
        protected array $database_details,
        protected array $user_details,
        protected array $app_details,
    )
    {
        $this->composerRepositoryService = app(ComposerRepositoryService::class);
        $this->themeService = app(ThemeService::class);
    }

    protected const DATABASE_DETAILS_VALIDATION_RULES = [
        'host' => 'required|string|min:5',
        'username' => 'required|string|min:3|max:16',
        'database' => 'required|string|min:3|max:54',
        'password' => 'required|string|min:6|max:32',
        'port' => 'required|integer|min:0|max:65535',
        'driver' => 'required|in:mysql,postgres,sqlite,sqlsrv',
        'prefix' => 'sometimes|string|max:10',
    ];

    protected const APP_DETAILS_VALIDATION_RULES = [
        'name' => 'required|string|min:3|max:255',
        'url' => 'required|url'
    ];

    protected const USER_DETAILS_VALIDATION_RULES = [
        'username' => 'required|min:5|max:50',
        'email' => 'required|email',
        'password' => 'required|string|min:8|max:255|confirmed',
        'password_confirmation' => 'required'
    ];

    /**
     * @throws \Exception
     */
    public static function createTestMockup()
    {
        $installer = new self([], [
            'name' => 'Max Mustermann',
            'email' => 'test@unit.test',
            'password' => Hash::make('password'),
        ], []);

        $installer->runMigrations();

        $installer->fillDatabase();

    }

    public static function isInstalled(): bool
    {
        return stream_resolve_include_path(base_path('.installed')) !== false;
    }

    public static function setIsInstalled(bool $value)
    {
        if ($value) {
            File::put(base_path('.installed'), '');
            return;
        }
        if (self::isInstalled()) {
            File::delete(base_path('.installed'));
        }
    }

    protected static function setInstallerDatabaseConfig(array $values)
    {
        Config::set('database.connections.latus_installer', [
            'driver' => $values['driver'],
            'host' => $values['host'],
            'database' => $values['database'],
            'username' => $values['username'],
            'password' => $values['password'],
            'port' => $values['port'],
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => $values['prefix'],
            'strict' => false,
        ]);
    }

    protected static function validateValuesWithRules(array $values, array $rules): bool
    {
        $validator = Validator::make($values, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        return true;
    }

    public static function tryUserDetails(array $values): bool
    {
        return self::validateValuesWithRules($values, self::USER_DETAILS_VALIDATION_RULES);
    }

    public static function tryAppDetails(array $values): bool
    {
        return self::validateValuesWithRules($values, self::APP_DETAILS_VALIDATION_RULES);
    }

    public static function verifyDatabaseDetails(array $values): bool
    {
        self::validateValuesWithRules($values, self::DATABASE_DETAILS_VALIDATION_RULES);

        self::setInstallerDatabaseConfig($values);

        if (DB::connection('latus_installer')->getDatabaseName()) {
            return true;
        }

        return false;
    }

    protected function runMigrations()
    {
        $this->printToConsole('Running migrations...');

        match (Artisan::call('migrate:refresh')) {
            Command::FAILURE => throw new \Exception('Migration failed. Please verify that you are using a database version supported by Laravel.'),
            Command::INVALID => throw new \Exception('Migration failed. There were invalid migrations. Please verify that all external packages\'s migrations are valid.'),
            default => 0
        };

        $this->printToConsole('Migrations complete!');
    }

    protected function updateEnvironment()
    {
        $this->printToConsole('Updating environment...');

        DotenvEditor::setKeys([
            'APP_NAME' => $this->app_details['name'],
            'APP_URL' => $this->app_details['url'],

            'DB_CONNECTION' => $this->database_details['driver'],
            'DB_HOST' => $this->database_details['host'],
            'DB_PORT' => $this->database_details['port'],
            'DB_DATABASE' => $this->database_details['database'],
            'DB_USERNAME' => $this->database_details['username'],
            'DB_PASSWORD' => $this->database_details['password'],
            'DB_DRIVER' => $this->database_details['driver'],
            'DB_PREFIX' => $this->database_details['prefix'],
        ]);

        DotenvEditor::save();

        $this->printToConsole('Environment updated!');
    }

    protected function tryDetails()
    {
        self::tryAppDetails($this->app_details);
        self::tryUserDetails($this->user_details);
        self::verifyDatabaseDetails($this->database_details);
    }

    protected function insertUser(UserService $userService): Model
    {
        return $userService->createUser([
            'name' => $this->user_details['username'],
            'email' => $this->user_details['email'],
            'password' => Hash::make($this->user_details['password']),
        ]);
    }

    protected function fillDatabase()
    {
        $this->printToConsole('Filling database...');

        $this->printToConsole('Seeding...');
        Artisan::call('db:seed', ['--class' => DynamicSeeder::class]);
        $this->printToConsole('Seeded!');

        $this->printToConsole('Creating user with specified details...');
        /**
         * @var User $user
         */
        $user = $this->insertUser(new UserService(app()->make(UserRepository::class)));
        $this->printToConsole('User created!');

    }

    protected function createComposerRepository(): ComposerRepository
    {
        return $this->composerRepositoryService->createRepository([
            'name' => 'latusprojects.repo.repman.io',
            'url' => 'https://latusprojects.repo.repman.io'
        ]);
    }

    /**
     * @throws ComposerCLIException
     */
    protected function createAndInstallDefaultTheme()
    {
        $this->printToConsole('Installing default theme "' . self::DEFAULT_THEME . '"...');

        $repository = $this->createComposerRepository();

        $theme = $this->themeService->createTheme([
            'name' => self::DEFAULT_THEME,
            'supports' => [],
            'repository_id' => $repository->id,
            'target_version' => self::DEFAULT_THEME_VERSION
        ]);

        $proxyPackage = new ProxyPackage($repository, $theme);

        /**
         * @var Conductor $conductor
         */
        $conductor = app(Conductor::class);
        $conductor->installOrUpdatePackage($proxyPackage);

        $this->printToConsole('Theme installed!');
    }

    /**
     * @throws ComposerCLIException
     * @throws \Exception
     */
    public function commenceInstallation()
    {
        $this->tryDetails();

        $this->updateEnvironment();

        $this->runMigrations();

        $this->fillDatabase();

        $this->createAndInstallDefaultTheme();

    }

    protected function printToConsole(string $message, string $type = 'info')
    {
        if ($this->command !== null) {
            match ($type) {
                'warn' => $this->command->warn($message),
                'error' => $this->command->error($message),
                default => $this->command->info($message),
            };
        }
    }

    public function setCli(\Illuminate\Console\Command $command)
    {
        $this->command = $command;
    }
}