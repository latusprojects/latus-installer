<?php

namespace Latus\Installer\Providers;

use Illuminate\Support\ServiceProvider;
use Latus\Installer\Console\Commands\InstallCommand;
use Latus\Installer\Installer;

class InstallerServiceProvider extends ServiceProvider
{
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
