<?php

namespace Latus\Installer\Providers;

use Latus\Installer\Events\AppDetailsProvided;
use Latus\Installer\Events\DatabaseDetailsProvided;
use Latus\Installer\Events\DefaultUserCreated;
use Latus\Installer\Events\InstallableComposerRepositoryProvided;
use Latus\Installer\Events\InstallablePluginProvided;
use Latus\Installer\Events\InstallableThemeProvided;
use Latus\Installer\Events\UserDetailsProvided;
use Latus\Installer\Listeners\AssignRoleToDefaultUser;
use Latus\Installer\Listeners\CreateDefaultUser;
use Latus\Installer\Listeners\InstallComposerRepository;
use Latus\Installer\Listeners\InstallPlugin;
use Latus\Installer\Listeners\InstallTheme;
use Latus\Installer\Listeners\SetTemporaryDatabaseConfig;
use Latus\Installer\Listeners\UpdateAppDetailsInEnvFile;
use Latus\Installer\Listeners\UpdateDatabaseDetailsInEnvFile;

class EventServiceProvider extends \Illuminate\Events\EventServiceProvider
{
    protected array $listeners = [
        UserDetailsProvided::class => [
            CreateDefaultUser::class
        ],
        AppDetailsProvided::class => [
            UpdateAppDetailsInEnvFile::class
        ],
        DatabaseDetailsProvided::class => [
            UpdateDatabaseDetailsInEnvFile::class,
            SetTemporaryDatabaseConfig::class,
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
        DefaultUserCreated::class => [
            AssignRoleToDefaultUser::class
        ]
    ];
}