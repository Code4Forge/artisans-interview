<?php

namespace App\Repositories;

use App\Contracts\Repositories\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleRepository implements RoleRepositoryInterface
{
    public function queryAll(): Builder
    {
        return Role::query()->with('permissions');
    }

    public function create(array $attributes): Role
    {
        $role = Role::create([
            'name' => strtolower(trim($attributes['name'])),
            'guard_name' => 'web',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }

    public function update(Role $role, array $attributes): Role
    {
        $role->update([
            'name' => strtolower(trim($attributes['name'])),
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role->fresh(['permissions']);
    }

    public function syncPermissions(Role $role, array $permissions): Role
    {
        // Protect primary admin from losing essential abilities
        if ($role->name === 'admin') {
            if (! in_array('roles.manage', $permissions, true)) {
                $permissions[] = 'roles.manage';
            }
            if (! in_array('dashboard.view', $permissions, true)) {
                $permissions[] = 'dashboard.view';
            }
        }

        $role->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role->fresh(['permissions']);
    }

    public function delete(Role $role): void
    {
        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
