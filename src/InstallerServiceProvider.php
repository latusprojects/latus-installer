<?php

namespace Latus\Installer;

use Illuminate\Support\ServiceProvider;
use Latus\Installer\Console\Commands\InstallCommand;

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
