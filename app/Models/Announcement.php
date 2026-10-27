<?php

namespace App\Models;

use App\Enums\AnnouncementAudience;
use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

#[Fillable(['author_id', 'title', 'body', 'audience', 'attachment_path', 'attachment_name'])]
class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory;

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Users selected when audience is 'custom'.
     */
    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_user')->withTimestamps();
    }

    /**
     * Get the resolved collection of users targeted by this announcement.
     *
     * @return Collection<int, User>
     */
    public function audienceUsers(): Collection
    {
        if ($this->audience === AnnouncementAudience::Custom) {
            return $this->relationLoaded('recipients')
                ? $this->recipients
                : $this->recipients()->with('roles')->get();
        }

        $author = $this->relationLoaded('author') ? $this->author : $this->author()->first();
        $isTeacherAuthor = $author && $author->hasRole('teacher') && ! $author->hasRole('admin');

        $users = collect();

        if ($this->audience === AnnouncementAudience::Teachers) {
            $users = User::role('teacher')->with('roles')->get();
        }

        if (in_array($this->audience, [AnnouncementAudience::Students, AnnouncementAudience::Both], true)) {
            $studentQuery = User::role('student')->with('roles');
            $users = $users->merge($studentQuery->get());
        }

        if (in_array($this->audience, [AnnouncementAudience::Parents, AnnouncementAudience::Both], true)) {
            $parentQuery = User::role('parent')->with('roles');
            $users = $users->merge($parentQuery->get());
        }

        return $users->unique('id')->values();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'audience' => AnnouncementAudience::class,
        ];
    }
}
