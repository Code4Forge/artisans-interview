<?php

namespace App\Services;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Exceptions\RoleAssignedToUsersException;
use Exception;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(private RoleRepositoryInterface $roles) {}

    /**
     * @param  array{name: string}  $attributes
     */
    public function create(array $attributes): Role
    {
        return $this->roles->create($attributes);
    }

    /**
     * @param  array{name: string}  $attributes
     */
    public function update(Role $role, array $attributes): Role
    {
        return $this->roles->update($role, $attributes);
    }

    /**
     * @param  list<string>  $permissions
     */
    public function updatePermissions(Role $role, array $permissions): Role
    {
        $role = $this->roles->syncPermissions($role, $permissions);

        foreach ($role->users as $user) {
            $user->syncRolePermissionsDirectly(sync: true);
        }

        return $role;
    }

    /**
     * @throws RoleAssignedToUsersException
     * @throws Exception
     */
    public function delete(Role $role): void
    {
        if ($role->users()->exists()) {
            throw new RoleAssignedToUsersException($role->name, $role->users()->count());
        }

        if ($role->name === 'admin') {
            throw new Exception('The primary admin role cannot be deleted.');
        }

        $this->roles->delete($role);
    }
}
