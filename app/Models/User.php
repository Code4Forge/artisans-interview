<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'avatar_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use HasRoles {
        assignRole as traitAssignRole;
        syncRoles as traitSyncRoles;
        removeRole as traitRemoveRole;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path
            ? Storage::disk('public')->url($this->avatar_path)
            : null;
    }

    public function primaryRole(): ?string
    {
        return once(function (): ?string {
            $name = $this->roles()->value('name');

            return is_string($name) ? $name : null;
        });
    }

    public function primaryRoleLabel(): string
    {
        return ucfirst($this->primaryRole() ?? 'User');
    }

    public function roleSummary(): string
    {
        return match ($this->primaryRole()) {
            'admin' => 'Manage teachers, roles & permissions, and review students, parents, and announcements.',
            'teacher' => 'Manage students and parents, and post announcements.',
            'student' => 'Read announcements and view school notices.',
            'parent' => 'Read announcements sent to parents.',
            default => 'School management portal user.',
        };
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(self::class, 'teacher_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(self::class, 'teacher_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'author_id');
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'parent_student', 'student_id', 'parent_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'parent_student', 'parent_id', 'student_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    public function assignRole(...$roles): static
    {
        $this->traitAssignRole(...$roles);

        $this->syncRolePermissionsDirectly();

        return $this;
    }

    public function syncRoles(...$roles): static
    {
        $this->traitSyncRoles(...$roles);

        $this->syncRolePermissionsDirectly(sync: true);

        return $this;
    }

    public function removeRole(...$role): static
    {
        $this->traitRemoveRole(...$role);

        $this->syncRolePermissionsDirectly(sync: true);

        return $this;
    }

    /**
     * Auto-apply all permissions belonging to the user's assigned roles directly to the user.
     */
    public function syncRolePermissionsDirectly(bool $sync = false): void
    {
        if (! $this->exists) {
            return;
        }

        $this->unsetRelation('roles');
        $this->unsetRelation('permissions');

        $rolePermissions = $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn ($role) => $role->permissions)
            ->unique('id');

        if ($sync) {
            $this->syncPermissions($rolePermissions);
        } elseif ($rolePermissions->isNotEmpty()) {
            $this->givePermissionTo($rolePermissions);
        }
    }
}
