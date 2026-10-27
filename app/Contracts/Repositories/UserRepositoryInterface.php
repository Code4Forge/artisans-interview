<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface UserRepositoryInterface
{
    public function paginateByRole(string $role, int $perPage = 15): LengthAwarePaginator;

    public function queryByRole(string $role): Builder;

    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     */
    public function createWithRole(array $attributes, string $role): User;

    /**
     * @param  array{name: string, email?: string, password?: string|null, avatar_path?: string|null}  $attributes
     */
    public function update(User $user, array $attributes): User;

    public function delete(User $user): void;
}
