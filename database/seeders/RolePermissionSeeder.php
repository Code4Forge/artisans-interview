<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Complete list of permissions in the system.
     * To add a new permission, add it to this array and rerun the seeder.
     *
     * @var list<string>
     */
    public const PERMISSIONS = [
        'dashboard.view',
        'roles.view',
        'roles.create',
        'roles.edit',
        'roles.delete',
        'roles.permissions',
        'roles.manage',
        'teachers.view',
        'teachers.create',
        'teachers.update',
        'teachers.delete',
        'students.view',
        'students.create',
        'students.update',
        'students.delete',
        'announcements.view',
        'announcements.create',
        'announcements.delete',
    ];

    /**
     * Default roles and their initial permissions.
     * Roles array has only 1 key which is permissions array for default role's permissions.
     *
     * @var array<string, array{permissions: list<string>}>
     */
    public const ROLES = [
        'admin' => [
            'permissions' => [
                'dashboard.view',
                'roles.view',
                'roles.create',
                'roles.edit',
                'roles.delete',
                'roles.permissions',
                'roles.manage',
                'teachers.view',
                'teachers.create',
                'teachers.update',
                'teachers.delete',
                'students.view',
                'students.create',
                'students.update',
                'students.delete',
                'announcements.view',
                'announcements.create',
                'announcements.delete',
            ],
        ],
        'teacher' => [
            'permissions' => [
                'dashboard.view',
                'students.view',
                'students.create',
                'students.update',
                'students.delete',
                'announcements.view',
                'announcements.create',
                'announcements.delete',
            ],
        ],
        'student' => [
            'permissions' => [
                'dashboard.view',
                'announcements.view',
            ],
        ],
        'parent' => [
            'permissions' => [
                'dashboard.view',
                'announcements.view',
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. Create permissions
        foreach (self::PERMISSIONS as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        // 2. Create roles and sync permissions
        foreach (self::ROLES as $roleName => $data) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($data['permissions']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
