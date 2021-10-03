<?php


namespace Latus\Installer;


use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Latus\Installer\Database\DynamicSeeder;
use Latus\Installer\Events\ActiveModulesProvided;
use Latus\Installer\Events\AppDetailsProvided;
use Latus\Installer\Events\DatabaseDetailsProvided;
use Latus\Installer\Events\InstallableComposerRepositoryProvided;
use Latus\Installer\Events\InstallablePluginProvided;
use Latus\Installer\Events\InstallableThemeProvided;
use Latus\Installer\Events\UserDetailsProvided;
use Latus\Installer\Jobs\DisableWebInstaller;
use Latus\Plugins\Models\ComposerRepository;
use Latus\Plugins\Models\Theme;
use Latus\Plugins\Services\ComposerRepositoryService;
use Latus\Plugins\Services\ThemeService;
use Latus\Settings\Services\SettingService;
use Symfony\Component\Console\Command\Command;

abstract class Installer
{
    protected array $themes = [];
    protected array $plugins = [];
    protected array $activeModules = [];

    protected ComposerRepository $masterRepository;

    public function __construct(
        protected ComposerRepositoryService $composerRepositoryService,
        protected ThemeService              $themeService,
        protected SettingService            $settingService,
    )
    {
    }

    public function build()
    {
        app()->instance('latus-installer', $this);

        $this->addPlugin('latusprojects/latus-base-plugin', '0.1.0');
    }

    /**
     * @throws \Exception
     */
    public function apply(array $databaseDetails, array $appDetails, array $userDetails)
    {
        $this->provideDatabaseDetails($databaseDetails);

        $this->prepareDatabase();

        $this->provideAppDetails($appDetails);
        $this->provideUserDetails($userDetails);

        $this->installComposerPackages();
        $this->provideActiveModules();
    }

    public function destroy()
    {
        app()->bind('latus-installer', null);
        Config::set('database.connections.latus_installer', null);
        DisableWebInstaller::dispatchAfterResponse();
    }

    public function addTheme(string $theme, string $version, array $activeForModules = []): void
    {
        $this->themes[$theme] = $version;

        foreach ($activeForModules as $moduleContract => $moduleClass) {
            $this->activeModules[$moduleContract] = $moduleClass;
        }
    }

    public function addPlugin(string $plugin, string $version)
    {
        $this->plugins[$plugin] = $version;
    }

    protected function provideDatabaseDetails(array $details)
    {
        DatabaseDetailsProvided::dispatch([
            'host' => $details['host'],
            'username' => $details['username'],
            'database' => $details['database'],
            'password' => $details['password'],
            'port' => $details['port'] ?? 3306,
            'driver' => $details['driver'] ?? 'mysql',
            'prefix' => $details['prefix'] ?? '',
        ]);
    }

    protected function provideUserDetails(array $details)
    {
        UserDetailsProvided::dispatch([
            'name' => $details['name'],
            'email' => $details['email'],
            'password' => $details['password'],
        ]);
    }

    protected function provideAppDetails(array $details)
    {
        AppDetailsProvided::dispatch([
            'name' => $details['name'],
            'url' => $details['url']
        ]);
    }

    /**
     * @throws \Exception
     */
    protected function prepareDatabase()
    {
        $this->migrate();
        $this->seed();

        $this->createMasterRepository();
    }

    /**
     * @throws \Exception
     */
    protected function migrate()
    {
        match (Artisan::call('migrate:refresh')) {
            Command::FAILURE => throw new \Exception('Migration failed. Please verify that you are using a database version supported by Laravel.'),
            Command::INVALID => throw new \Exception('migrate:refresh command failed. This should not happen and might be a bug.'),
            default => 0
        };
    }

    /**
     * @throws \Exception
     */
    protected function seed()
    {
        match (Artisan::call('db:seed', ['--class' => DynamicSeeder::class])) {
            Command::FAILURE => throw new \Exception('Seeding failed. Please verify that you are using a database version supported by Laravel.'),
            Command::INVALID => throw new \Exception('db:seed command failed. This should not happen and might be a bug.'),
            default => 0
        };
    }

    protected function createMasterRepository()
    {
        InstallableComposerRepositoryProvided::dispatch([
            'name' => 'latusprojects.repo.repman.io',
            'url' => 'https://latusprojects.repo.repman.io',
            'type' => 'composer',
            'status' => ComposerRepository::STATUS_ACTIVATED
        ]);
    }

    protected function getMasterRepository(): ComposerRepository
    {
        if (!isset($this->{'masterRepository'})) {
            /**
             * @var ComposerRepository $masterRepository
             */
            $masterRepository = $this->composerRepositoryService->findByName('latusprojects.repo.repman.io');
            $this->masterRepository = $masterRepository;
        }

        return $this->masterRepository;
    }

    protected function installComposerPackages()
    {
        $masterRepositoryId = $this->getMasterRepository()->id;

        foreach ($this->themes as $themeName => $themeVersion) {
            InstallableThemeProvided::dispatch([
                'name' => $themeName,
                'supports' => [],
                'repository_id' => $masterRepositoryId,
                'target_version' => $themeVersion,
                'status' => Theme::STATUS_ACTIVE
            ]);
        }

        foreach ($this->plugins as $pluginName => $pluginVersion) {
            InstallablePluginProvided::dispatch([
                'name' => $pluginName,
                'repository_id' => $masterRepositoryId,
                'target_version' => $pluginVersion,
                'status' => Theme::STATUS_ACTIVE
            ]);
        }
    }

    protected function provideActiveModules()
    {
        ActiveModulesProvided::dispatch($this->getActiveModules());
    }

    protected function getActiveModules(): array
    {
        return $this->activeModules;
    }

    protected function setTemporaryConnectionDetails(array $details)
    {
        Config::set('database.connections.latus_installer', [
            'driver' => $details['driver'] ?? 'mysql',
            'host' => $details['host'],
            'database' => $details['database'],
            'username' => $details['username'],
            'password' => $details['password'],
            'port' => $details['port'] ?? 3306,
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => $details['prefix'] ?? '',
            'strict' => false,
        ]);
    }

    /**
     * Attempt a database connection using the given details
     *
     * @param array $details
     * @return bool
     */
    public function attemptConnectionWithDetails(array $details): bool
    {
        $this->setTemporaryConnectionDetails($details);

        if (!DB::connection('latus_installer') || !DB::connection('latus_installer')->getDatabaseName()) {
            return false;
        }

        return true;
    }
}