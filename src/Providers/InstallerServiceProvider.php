<?php

namespace Latus\Installer\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Latus\Installer\Console\Commands\InstallCommand;
use Latus\Installer\Console\Commands\UpdatePluginCommand;
use Latus\Installer\Console\Commands\UpdateThemeCommand;
use Latus\Installer\Providers\Traits\RegistersSeeders;

class InstallerServiceProvider extends LaravelServiceProvider
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
            UpdatePluginCommand::class,
            UpdateThemeCommand::class,
        ]);

        $this->app->register(EventServiceProvider::class);

        $this->app->register(ViewServiceProvider::class);
    }

    protected function copyInstallerAssets()
    {
        if (!File::exists(public_path('assets/vendor/latus-installer'))) {
            File::copyDirectory(__DIR__ . '/../../resources/assets/dist', public_path('assets/vendor/latus-installer'));
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (defined('LATUS_INSTALLER')) {
            $this->app->booted(function () {
                $this->copyInstallerAssets();
                require __DIR__ . '/../../routes/web.php';
            });
        }
    }
}
