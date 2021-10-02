<?php


namespace Latus\Installer\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Latus\Helpers\Paths;
use Latus\Installer\ConsoleInstaller;

class InstallCommand extends Command
{

    public const DEFAULT_PRESET = 'latus-installer.preset.json';

    protected static array $inputValidationRules = [
        'database' => [
            'host' => 'required|string|min:5',
            'username' => 'required|string|min:3|max:16',
            'database' => 'required|string|min:3|max:54',
            'password' => 'required|string|min:6|max:32',
            'port' => 'sometimes|integer|min:0|max:65535',
            'driver' => 'sometimes|in:mysql,postgres,sqlite,sqlsrv',
            'prefix' => 'sometimes|string|max:10',
        ],
        'app' => [
            'name' => 'required|string|min:3|max:255',
            'url' => 'required|url'
        ],
        'user' => [
            'name' => 'required|min:5|max:50',
            'email' => 'required|email',
            'password' => 'required|string|min:8|max:255|confirmed',
            'password_confirmation' => 'required'
        ]
    ];

    protected static array $presetValidationRules = [
        'database' => [
            'host' => 'required|string|min:5',
            'username' => 'required|string|min:3|max:16',
            'database' => 'required|string|min:3|max:54',
            'password' => 'required|string|min:6|max:32',
            'port' => 'sometimes|integer|min:0|max:65535',
            'driver' => 'sometimes|in:mysql,postgres,sqlite,sqlsrv',
            'prefix' => 'sometimes|string|max:10',
        ],
        'app' => [
            'name' => 'required|string|min:3|max:255',
            'url' => 'required|url'
        ],
        'user' => [
            'name' => 'required|min:5|max:50',
            'email' => 'required|email',
            'password' => 'required|string|min:8|max:255'
        ]
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'latus:install {--preset=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Guided latus installer, also available as web-installer under <your-website>/install';

    protected array $loadedPresetDetails = [];
    protected array $databaseDetails = [];
    protected array $appDetails = [];
    protected array $userDetails = [];

    public function __construct(
        protected ConsoleInstaller $installer,
    )
    {
        parent::__construct();
    }

    /**
     * @param array $values
     * @param array $rules
     * @throws \InvalidArgumentException
     */
    protected function validateValuesWithRules(array $values, array $rules)
    {
        $validator = Validator::make($values, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }

    protected function getPresetDetailsMaybeFail(string $section, array $rules = null): array|null
    {
        if (isset($this->loadedPresetDetails[$section])) {
            $details = $this->loadedPresetDetails[$section];

            try {
                $this->validateValuesWithRules($details, $rules ?? self::$presetValidationRules[$section]);
            } catch (\InvalidArgumentException $e) {
                $this->error('The loaded preset is missing one or more keys in the "' . $section . '" section:');
                $this->error($e->getMessage());
                $this->error('Please verify that the preset contains all required keys and try again.');

                exit(1);
            }

            $this->info('--- Loaded ' . $section . '-details from preset ---');

            return $details;
        }

        return null;
    }

    protected function askDatabaseDetails(): array
    {
        $details = [];
        if ($presetDetails = $this->getPresetDetailsMaybeFail('database')) {
            $details = $presetDetails;
        } else {
            $details = [
                'driver' => $this->ask('Driver (mysql,postgres,sqlite,sqlsrv)', 'mysql'),
                'host' => $this->ask('Host', 'localhost'),
                'username' => $this->ask('Username'),
                'database' => $this->ask('Database'),
                'password' => $this->ask('Password'),
                'port' => $this->ask('Port', 3306),
                'prefix' => $this->ask('Prefix', ''),
            ];

            try {
                $this->validateValuesWithRules($details, self::$inputValidationRules['database']);
            } catch (\InvalidArgumentException $e) {
                $this->error($e->getMessage());
                $this->askDatabaseDetails();
            }
        }

        if (!$this->installer->attemptConnectionWithDetails($details)) {
            $this->error('Database connection could not be established using the provided details');
            $this->askDatabaseDetails();
        }

        return $details;
    }

    protected function askUserDetails(): array
    {
        if ($presetDetails = $this->getPresetDetailsMaybeFail('user')) return $presetDetails;

        $details = [
            'name' => $this->ask('Username', 'admin'),
            'email' => $this->ask('Email'),
            'password' => $this->secret('Password'),
            'password_confirmation' => $this->secret('Confirm Password'),
        ];

        try {
            $this->validateValuesWithRules($details, self::$inputValidationRules['user']);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
            $this->askUserDetails();
        }

        return $details;
    }

    protected function askAppDetails(): array
    {

        if ($presetDetails = $this->getPresetDetailsMaybeFail('app')) return $presetDetails;

        $details = [
            'name' => $this->ask('Name'),
            'url' => $this->ask('URL'),
        ];

        try {
            $this->validateValuesWithRules($details, self::$inputValidationRules['app']);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
            $this->askAppDetails();
        }

        return $details;
    }

    /**
     * @throws FileNotFoundException
     */
    protected function loadPreset()
    {
        if (!$presetName = $this->option('preset')) {
            return;
        }

        if (!File::exists(Paths::basePath($presetName)) && !File::exists($presetName)) {
            throw new FileNotFoundException('A preset has been specified, but no file with that name or path exists. File: ' . $presetName);
        }

        $this->loadedPresetDetails = json_decode(
            File::get(
                File::exists(Paths::basePath($presetName))
                    ? Paths::basePath($presetName)
                    : $presetName
            ), true);

    }

    protected function addPresetThemesAndPluginsToInstaller()
    {
        if (isset($this->loadedPresetDetails['plugins']) && is_array($this->loadedPresetDetails['plugins'])) {
            $this->info('Loading plugins from preset...');
            foreach ($this->loadedPresetDetails['plugins'] as $plugin) {
                if (isset($plugin['name']) && isset($plugin['version'])) {
                    $this->installer->addPlugin($plugin['name'], $plugin['version']);
                }
            }
        }

        if (isset($this->loadedPresetDetails['themes']) && is_array($this->loadedPresetDetails['themes'])) {
            $this->info('Loading themes from preset...');
            foreach ($this->loadedPresetDetails['themes'] as $theme) {
                if (isset($theme['name']) && isset($theme['version'])) {
                    $activeForModules = $theme['active'] ?? [];
                    $this->installer->addTheme($theme['name'], $theme['version'], $activeForModules);
                }
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws FileNotFoundException
     */
    public function handle(): int
    {

        $this->loadPreset();

        $this->info('Welcome! This CLI will guide you through the installation of latus. This won\'t take long, I promise! ');

        $this->warn('#1 - Database Details');
        $this->databaseDetails = $this->askDatabaseDetails();

        $this->warn('#2 - Admin Account');
        $this->userDetails = $this->askUserDetails();

        $this->warn('#3 - Application');
        $this->appDetails = $this->askAppDetails();

        $this->commenceInstallation();

        return 0;
    }

    protected function commenceInstallation()
    {
        $this->installer->setCli($this);

        $this->info('Initiating installer...');
        $this->installer->build();

        $this->addPresetThemesAndPluginsToInstaller();

        try {
            $this->info('Attempting installation...');
            $this->installer->apply($this->databaseDetails, $this->appDetails, $this->userDetails);
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
            exit(1);
        }

        $this->installer->destroy();

        $this->info('Installation finished!');
    }


}