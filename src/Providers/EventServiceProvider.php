<?php

namespace Latus\Installer\Providers;

use Latus\Installer\Events\AppDetailsProvided;
use Latus\Installer\Events\UserDetailsProvided;
use Latus\Installer\Listeners\CreateDefaultUser;
use Latus\Installer\Listeners\SetupApp;

class EventServiceProvider extends \Illuminate\Events\EventServiceProvider
{
    protected array $listeners = [
        UserDetailsProvided::class => [
            CreateDefaultUser::class
        ],
        AppDetailsProvided::class => [
            SetupApp::class
        ]
    ];
}