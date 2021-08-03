<?php


namespace Latus\Installer\Database\Seeders;


use Illuminate\Database\Seeder;
use Latus\Permissions\Models\Role;
use Latus\Permissions\Models\User;
use Latus\Permissions\Services\RoleService;
use Latus\Permissions\Services\UserService;

class UserRoleSeeder extends Seeder
{

    public function __construct(
        protected UserService $userService,
        protected RoleService $roleService,
    )
    {
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /**
         * @var Role $admin_role
         * @var User $admin_user
         */
        $admin_role = $this->roleService->findByName('admin');
        $admin_user = $this->userService->find(1);

        $this->userService->addRoleToUser($admin_user, $admin_role);
    }
}