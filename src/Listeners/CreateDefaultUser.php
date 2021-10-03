<?php

namespace Latus\Installer\Listeners;

use Latus\Installer\Events\DefaultUserCreated;
use Latus\Installer\Events\UserDetailsProvided;
use Latus\Permissions\Services\UserService;

class CreateDefaultUser
{
    public function __construct(
        protected UserService $userService
    )
    {
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function handle(UserDetailsProvided $event,)
    {
        $user = $this->userService->createUser($event->details);

        DefaultUserCreated::dispatch($user);
    }
}