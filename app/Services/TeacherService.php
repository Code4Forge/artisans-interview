<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

class TeacherService
{
    public function __construct(private UserRepositoryInterface $users) {}

    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     */
    public function create(array $attributes): User
    {
        return $this->users->createWithRole($attributes, 'teacher');
    }

    /**
     * @param  array{name: string, email?: string, password?: string|null}  $attributes
     */
    public function update(User $teacher, array $attributes): User
    {
        return $this->users->update($teacher, $attributes);
    }

    public function delete(User $teacher): void
    {
        $this->users->delete($teacher);
    }
}
