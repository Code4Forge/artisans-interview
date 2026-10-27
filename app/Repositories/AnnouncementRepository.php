<?php

namespace App\Repositories;

use App\Contracts\Repositories\AnnouncementRepositoryInterface;
use App\Enums\AnnouncementAudience;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AnnouncementRepository implements AnnouncementRepositoryInterface
{
    public function queryForUser(User $user): Builder
    {
        $query = Announcement::query()->with('author');

        if ($user->hasRole('admin')) {
            return $query->orderByDesc('created_at')->orderByDesc('id');
        }

        if ($user->hasRole('teacher')) {
            // Teacher sees their own posts, admin posts targeted to teachers, or posts where they are selected in custom recipients
            return $query->where(function (Builder $q) use ($user) {
                $q->where('author_id', $user->id)
                    ->orWhere('audience', AnnouncementAudience::Teachers->value)
                    ->orWhere(function (Builder $sub) use ($user) {
                        $sub->where('audience', AnnouncementAudience::Custom->value)
                            ->whereHas('recipients', fn ($r) => $r->where('users.id', $user->id));
                    });
            })->orderByDesc('created_at')->orderByDesc('id');
        }

        if ($user->hasRole('student')) {
            return $query->where(function (Builder $q) use ($user) {
                $q->whereIn('audience', [
                    AnnouncementAudience::Students->value,
                    AnnouncementAudience::Both->value,
                ])->orWhere(function (Builder $sub) use ($user) {
                    $sub->where('audience', AnnouncementAudience::Custom->value)
                        ->whereHas('recipients', fn ($r) => $r->where('users.id', $user->id));
                });
            })->orderByDesc('created_at')->orderByDesc('id');
        }

        if ($user->hasRole('parent')) {
            return $query->where(function (Builder $q) use ($user) {
                $q->whereIn('audience', [
                    AnnouncementAudience::Parents->value,
                    AnnouncementAudience::Both->value,
                ])->orWhere(function (Builder $sub) use ($user) {
                    $sub->where('audience', AnnouncementAudience::Custom->value)
                        ->whereHas('recipients', fn ($r) => $r->where('users.id', $user->id));
                });
            })->orderByDesc('created_at')->orderByDesc('id');
        }

        return $query->whereRaw('1 = 0');
    }

    public function find(int $id): ?Announcement
    {
        return Announcement::query()->with('author')->find($id);
    }

    public function create(array $attributes, array $recipientIds = []): Announcement
    {
        $announcement = Announcement::query()->create($attributes);

        if (! empty($recipientIds)) {
            $announcement->recipients()->sync($recipientIds);
        }

        return $announcement->fresh(['author', 'recipients']);
    }

    public function update(Announcement $announcement, array $attributes, array $recipientIds = []): Announcement
    {
        $announcement->update($attributes);

        if (array_key_exists('audience', $attributes) && $attributes['audience'] === AnnouncementAudience::Custom->value) {
            $announcement->recipients()->sync($recipientIds);
        } elseif (array_key_exists('audience', $attributes)) {
            $announcement->recipients()->detach();
        }

        return $announcement->fresh(['author', 'recipients']);
    }

    public function delete(Announcement $announcement): void
    {
        if ($announcement->attachment_path) {
            Storage::disk('public')->delete($announcement->attachment_path);
        }

        $this->cancelPendingQueuedEmails($announcement);

        $announcement->recipients()->detach();
        $announcement->delete();
    }

    /**
     * Purge any pending queued email jobs from the database queue for this announcement.
     */
    protected function cancelPendingQueuedEmails(Announcement $announcement): void
    {
        if (! Schema::hasTable('jobs')) {
            return;
        }

        try {
            DB::table('jobs')
                ->where('payload', 'like', '%AnnouncementNotification%')
                ->get(['id', 'payload'])
                ->filter(function ($job) use ($announcement) {
                    $payload = (string) $job->payload;

                    return str_contains($payload, 'Announcement')
                        && (
                            str_contains($payload, '"id":'.$announcement->id)
                            || str_contains($payload, 'i:'.$announcement->id.';')
                        );
                })
                ->each(function ($job) {
                    DB::table('jobs')->where('id', $job->id)->delete();
                });
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
