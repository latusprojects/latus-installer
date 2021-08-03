<?php

namespace Latus\Installer\Database\Seeders;

use Illuminate\Database\Seeder;
use Latus\Permissions\Models\Permission;
use Latus\Permissions\Models\Role;
use Latus\Permissions\Services\PermissionService;
use Latus\Permissions\Services\RoleService;

class RolePermissionSeeder extends Seeder
{

    protected const ADMIN_PERMISSIONS = [
        'dashboard.*',
        'user.*',
        'user.role.*',
        'user.permission.*',
        'role.*',
        'role.permission.*',
        'permission.*',
        'plugin.*',
        'repository.*',
        'theme.*',
    ];

    public function __construct(
        protected RoleService $roleService,
        protected PermissionService $permissionService,
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
         */
        $admin_role = $this->roleService->findByName('admin');
        foreach (self::ADMIN_PERMISSIONS as $permission_name) {
            /**
             * @var Permission $permission
             */
            $permission = $this->permissionService->findByName($permission_name);
            $this->roleService->addPermissionToRole($admin_role, $permission);
        }
    }
}
