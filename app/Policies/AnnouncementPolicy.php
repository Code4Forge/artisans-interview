<?php

namespace App\Policies;

use App\Enums\AnnouncementAudience;
use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('announcements.view');
    }

    public function view(User $user, Announcement $announcement): bool
    {
        if (! $user->can('announcements.view')) {
            return false;
        }
        
        if ($user->hasRole('admin')) {
            return true;
        }
        
        if ((int) $announcement->author_id === (int) $user->id) {
            return true;
        }
        
        if (
            ($user->hasRole('teacher') && $announcement->audience === AnnouncementAudience::Teachers) || 
            ($user->hasRole('teacher') && $announcement->audience === AnnouncementAudience::Both)
        ) {
            return true;
        }
        
        if (
            ($user->hasRole('student') && $announcement->audience === AnnouncementAudience::Students) || 
            ($user->hasRole('student') && $announcement->audience === AnnouncementAudience::Both)
        ) {
            return true;
        }
        
        if (
            ($user->hasRole('parent') && $announcement->audience === AnnouncementAudience::Parents) || 
            ($user->hasRole('parent') && $announcement->audience === AnnouncementAudience::Both)
        ) {
            return true;
        }
        
        if ($announcement->audience === AnnouncementAudience::Custom) {
            return $announcement->recipients()->where('users.id', $user->id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('announcements.create');
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        if (! $user->can('announcements.delete')) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return (int) $announcement->author_id === (int) $user->id;
    }
}
