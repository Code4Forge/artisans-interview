<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface
{
    public function queryAll(): Builder;

    public function create(array $attributes): Role;

    public function update(Role $role, array $attributes): Role;

    public function syncPermissions(Role $role, array $permissions): Role;

    public function delete(Role $role): void;
}
