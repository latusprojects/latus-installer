<?php

namespace Latus\Installer\Listeners;

use Latus\Installer\Events\DefaultUserCreated;
use Latus\Permissions\Models\Role;
use Latus\Permissions\Services\RoleService;
use Latus\Permissions\Services\UserService;

class AssignRoleToDefaultUser
{
    public function __construct(
        protected RoleService $roleService,
        protected UserService $userService,
    )
    {
    }

    public function handle(DefaultUserCreated $event)
    {
        $this->userService->addRoleToUser($event->user, $this->getRole());
    }

    protected function getRole(): Role
    {
        /**
         * @var Role $role
         */
        $role = $this->roleService->findByName('admin');
        return $role;
    }
}