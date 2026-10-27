<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    public function queryByRole(string $role): Builder
    {
        return User::query()
            ->role($role)
            ->with('roles');
    }

    public function paginateByRole(string $role, int $perPage = 15): LengthAwarePaginator
    {
        return $this->queryByRole($role)
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function createWithRole(array $attributes, string $role): User
    {
        return DB::transaction(function () use ($attributes, $role): User {
            $user = User::query()->create($attributes);
            $user->assignRole($role);

            return $user->fresh(['roles']);
        });
    }

    public function update(User $user, array $attributes): User
    {
        if (! array_key_exists('password', $attributes) || blank($attributes['password'])) {
            unset($attributes['password']);
        }

        $user->update($attributes);

        return $user->fresh(['roles']);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
