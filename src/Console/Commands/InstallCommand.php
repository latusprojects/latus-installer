<?php


namespace Latus\Installer\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Latus\Helpers\Paths;
use Latus\Installer\Installer;

class InstallCommand extends Command
{

    public const DEFAULT_PRESET = 'latus-installer.preset.json';

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

    protected function getPresetDetailsMaybeFail(string $section, array $rules): array|null
    {
        if (isset($this->loadedPresetDetails[$section])) {
            $details = $this->loadedPresetDetails[$section];

            try {
                Installer::validateValuesWithRules($details, $rules);
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
        if ($presetDetails = $this->getPresetDetailsMaybeFail('database', Installer::DATABASE_DETAILS_VALIDATION_RULES)) return $presetDetails;

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
            Installer::verifyDatabaseDetails($details);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
            $this->askDatabaseDetails();
        }

        return $details;
    }

    protected function askUserDetails(): array
    {
        if ($presetDetails = $this->getPresetDetailsMaybeFail('user', Installer::USER_DETAILS_VALIDATION_RULES)) return $presetDetails;

        $details = [
            'username' => $this->ask('Username', 'admin'),
            'email' => $this->ask('Email'),
            'password' => $this->secret('Password'),
            'password_confirmation' => $this->secret('Confirm Password'),
        ];

        try {
            Installer::tryUserDetails($details);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
            $this->askUserDetails();
        }

        return $details;
    }

    protected function askAppDetails(): array
    {

        if ($presetDetails = $this->getPresetDetailsMaybeFail('app', Installer::APP_DETAILS_VALIDATION_RULES)) return $presetDetails;

        $details = [
            'name' => $this->ask('Name'),
            'url' => $this->ask('App-URL'),
        ];

        try {
            Installer::tryAppDetails($details);
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

        $this->fillUpDefaultUserPresetDetails();
        $this->fillUpDefaultDatabasePresetDetails();
    }

    /**
     * If a preset is provided and the key 'user' exists, merge the preset with default user-details that are optional but required for validation
     */
    protected function fillUpDefaultUserPresetDetails(): void
    {
        if (isset($this->loadedPresetDetails['user']) && isset($this->loadedPresetDetails['user']['password'])) {
            $this->loadedPresetDetails['user']['password_confirmation'] = $this->loadedPresetDetails['user']['password'];
        }
    }

    /**
     * If a preset is provided and the key 'database' exists, merge the preset with default database-details that are optional but required for validation
     */
    protected function fillUpDefaultDatabasePresetDetails(): void
    {
        if (isset($this->loadedPresetDetails['database'])) {
            $this->loadedPresetDetails['database'] = $this->loadedPresetDetails['database'] + [
                    'port' => 3306,
                    'driver' => 'mysql',
                    'prefix' => ''
                ];
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
        $database_details = $this->askDatabaseDetails();

        $this->warn('#2 - Admin Account');
        $user_details = $this->askUserDetails();

        $this->warn('#3 - Application');
        $app_details = $this->askAppDetails();

        $installer = new Installer($database_details, $user_details, $app_details);
        $installer->setCli($this);

        if (
            isset($this->loadedPresetDetails['theme']) &&
            isset($this->loadedPresetDetails['theme']['name']) &&
            isset($this->loadedPresetDetails['theme']['version'])
        ) {
            $installer->setTheme($this->loadedPresetDetails['theme']['name'], $this->loadedPresetDetails['theme']['version']);
        }

        try {
            $installer->commenceInstallation();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        return 0;
    }
}