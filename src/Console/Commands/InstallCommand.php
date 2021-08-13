<?php


namespace Latus\Installer\Console\Commands;


use Illuminate\Console\Command;
use Latus\Installer\Installer;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'latus:install {--preset=' . self::DEFAULT_PRESET . '}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Guided latus installer, also available as web-installer under <your-website>/install';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function askDatabaseDetails(): array
    {

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->info('Welcome! This CLI will guide you through the installation of latus. This won\'t take long, I promise! ');

        $this->warn('#1 - Database Details');
        $database_details = $this->askDatabaseDetails();

        $this->warn('#2 - Admin Account');
        $user_details = $this->askUserDetails();

        $this->warn('#3 - Application');
        $app_details = $this->askAppDetails();

        $installer = new Installer($database_details, $user_details, $app_details);
        $installer->setCli($this);

        try {
            $installer->commenceInstallation();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        return 0;
    }
}