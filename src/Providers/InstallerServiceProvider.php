<?php

namespace Latus\Installer\Providers;

use Illuminate\Support\ServiceProvider;
use Latus\Installer\Console\Commands\InstallCommand;
use Latus\Installer\Database\Seeders\DatabaseSeeder;
use Latus\Installer\Installer;
use Latus\Installer\Providers\Traits\RegistersSeeders;

class InstallerServiceProvider extends ServiceProvider
{
    use RegistersSeeders;

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            InstallCommand::class,
        ]);

        $this->registerSeeders([
            DatabaseSeeder::class,
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (!Installer::isInstalled()) {
            //$this->loadRoutesFrom(__DIR__ . '/../routes');
        }
    }
}
