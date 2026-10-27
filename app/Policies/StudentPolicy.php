<?php

namespace App\Policies;

use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('students.view');
    }

    public function view(User $user, User $student): bool
    {
        return $user->can('students.view') && $student->hasRole('student');
    }

    public function create(User $user): bool
    {
        return $user->can('students.create');
    }

    public function update(User $user, User $student): bool
    {
        return $user->can('students.update') && $student->hasRole('student');
    }

    public function delete(User $user, User $student): bool
    {
        return $user->can('students.delete') && $student->hasRole('student');
    }
}
