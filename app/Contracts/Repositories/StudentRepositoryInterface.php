<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

interface StudentRepositoryInterface
{
    /**
     * Return student query for users with students.view permission.
     */
    public function queryForUser(User $user): Builder;

    public function find(int $id): ?User;

    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     */
    public function create(array $attributes): User;

    /**
     * @param  array{name: string, email?: string, password?: string|null}  $attributes
     */
    public function update(User $student, array $attributes): User;

    public function delete(User $student): void;

    /**
     * Sync parent accounts linked to a student with pivot relationship.
     *
     * @param  array<int, array{name: string, email: string, phone?: string|null, relationship: string}>  $parents
     */
    public function syncParents(User $student, array $parents): void;
}
