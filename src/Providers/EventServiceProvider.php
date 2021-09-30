<?php

namespace Latus\Installer\Providers;

use Latus\Installer\Events\AppDetailsProvided;
use Latus\Installer\Events\InstallableComposerRepositoryProvided;
use Latus\Installer\Events\InstallablePluginProvided;
use Latus\Installer\Events\InstallableThemeProvided;
use Latus\Installer\Events\UserDetailsProvided;
use Latus\Installer\Listeners\CreateDefaultUser;
use Latus\Installer\Listeners\InstallComposerRepository;
use Latus\Installer\Listeners\InstallPlugin;
use Latus\Installer\Listeners\InstallTheme;
use Latus\Installer\Listeners\SetupApp;

class EventServiceProvider extends \Illuminate\Events\EventServiceProvider
{
    protected array $listeners = [
        UserDetailsProvided::class => [
            CreateDefaultUser::class
        ],
        AppDetailsProvided::class => [
            SetupApp::class
        ],
        InstallableThemeProvided::class => [
            InstallTheme::class
        ],
        InstallablePluginProvided::class => [
            InstallPlugin::class
        ],
        InstallableComposerRepositoryProvided::class => [
            InstallComposerRepository::class
        ],
    ];
}