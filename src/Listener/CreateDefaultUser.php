<?php

namespace Latus\Installer\Listeners;

use Latus\Installer\Events\DefaultUserCreated;
use Latus\Installer\Events\UserDetailsProvided;
use Latus\Permissions\Services\UserService;

class CreateDefaultUser
{

    /**
     * @throws \InvalidArgumentException
     */
    public function handle(UserDetailsProvided $event, UserService $userService)
    {
        $user = $userService->createUser($event->details);

        DefaultUserCreated::dispatch(['user' => $user]);
    }
}