<?php

namespace App\Policies;

use App\Models\User;

class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('teachers.view');
    }

    public function view(User $user, User $teacher): bool
    {
        return $user->can('teachers.view')
            && $teacher->hasRole('teacher');
    }

    public function create(User $user): bool
    {
        return $user->can('teachers.create');
    }

    public function update(User $user, User $teacher): bool
    {
        return $user->can('teachers.update')
            && $teacher->hasRole('teacher');
    }

    public function delete(User $user, User $teacher): bool
    {
        return $user->can('teachers.delete')
            && $teacher->hasRole('teacher');
    }
}
