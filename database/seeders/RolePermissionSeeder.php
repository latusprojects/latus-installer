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
        'module.admin',
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
        'setting.*',
        'nav.*',
        'content.*',
        'content.setting.*',
        'content.page.*',
        'content.post.*',
        'content.event.*',
    ];

    public const USER_PERMISSIONS = [
        'module.admin',
        'dashboard.overview',
    ];

    public function __construct(
        protected RoleService       $roleService,
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
        $this->grantPermissionsToRole('admin', self::ADMIN_PERMISSIONS);
        $this->grantPermissionsToRole('user', self::USER_PERMISSIONS);
    }

    protected function grantPermissionsToRole(string $role, array $permissions)
    {
        /**
         * @var Role $role
         */
        $role = $this->roleService->findByName($role);
        foreach ($permissions as $permissionName) {
            /**
             * @var Permission $permission
             */
            $permission = $this->permissionService->findByName($permissionName);
            if (!$this->roleService->roleHasPermission($role, $permission)) {
                $this->roleService->addPermissionToRole($role, $permission);
            }
        }
    }
}
