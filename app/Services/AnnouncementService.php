<?php

namespace App\Services;

use App\Contracts\Repositories\AnnouncementRepositoryInterface;
use App\Enums\AnnouncementAudience;
use App\Mail\AnnouncementNotification;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AnnouncementService
{
    public function __construct(private AnnouncementRepositoryInterface $announcements) {}

    /**
     * @param  array{title: string, body: string, audience: string}  $attributes
     * @param  list<int>  $recipientIds
     */
    public function create(array $attributes, ?UploadedFile $attachment, User $author, array $recipientIds = []): Announcement
    {
        return DB::transaction(function () use ($attributes, $attachment, $author, $recipientIds): Announcement {
            $attributes['author_id'] = $author->id;

            if ($attachment) {
                $path = $attachment->store('announcements', 'public');
                $attributes['attachment_path'] = $path;
                $attributes['attachment_name'] = $attachment->getClientOriginalName();
            }

            $announcement = $this->announcements->create($attributes, $recipientIds);

            // Send emails to the targeted audience
            $this->dispatchAnnouncementEmails($announcement, $author, $recipientIds);

            return $announcement;
        });
    }

    /**
     * @param  array{title: string, body: string, audience?: string}  $attributes
     * @param  list<int>  $recipientIds
     */
    public function update(Announcement $announcement, array $attributes, ?UploadedFile $attachment, bool $removeAttachment = false, array $recipientIds = []): Announcement
    {
        return DB::transaction(function () use ($announcement, $attributes, $attachment, $removeAttachment, $recipientIds): Announcement {
            if ($removeAttachment && $announcement->attachment_path) {
                Storage::disk('public')->delete($announcement->attachment_path);
                $attributes['attachment_path'] = null;
                $attributes['attachment_name'] = null;
            }

            if ($attachment) {
                if ($announcement->attachment_path) {
                    Storage::disk('public')->delete($announcement->attachment_path);
                }
                $attributes['attachment_path'] = $attachment->store('announcements', 'public');
                $attributes['attachment_name'] = $attachment->getClientOriginalName();
            }

            return $this->announcements->update($announcement, $attributes, $recipientIds);
        });
    }

    public function delete(Announcement $announcement): void
    {
        $this->announcements->delete($announcement);
    }

    /**
     * Send email notifications for the announcement based on its audience.
     *
     * @param  list<int>  $recipientIds
     */
    private function dispatchAnnouncementEmails(Announcement $announcement, User $author, array $recipientIds = []): void
    {
        $recipients = collect();

        // 0. If custom audience
        if ($announcement->audience === AnnouncementAudience::Custom) {
            $recipients = User::whereIn('id', $recipientIds)->get();
        } else {
            // Note: Per requirements, announcements targeted to Teachers are bulletin-only on login (no emails dispatched)

            // 1. If audience includes students
            if (in_array($announcement->audience, [AnnouncementAudience::Students, AnnouncementAudience::Both])) {
                $studentQuery = User::role('student');
                $recipients = $recipients->merge($studentQuery->get());
            }

            // 2. If audience includes parents
            if (in_array($announcement->audience, [AnnouncementAudience::Parents, AnnouncementAudience::Both])) {
                $parentQuery = User::role('parent');
                $recipients = $recipients->merge($parentQuery->get());
            }
        }

        // Unique by id to avoid duplicate emails
        $recipients = $recipients->unique('id');

        $delaySeconds = 0;
        foreach ($recipients as $recipient) {
            if ($recipient->email) {
                // Throttle queued delivery by 2 seconds between emails to respect SMTP sandbox rate limits
                $mailable = (new AnnouncementNotification($announcement, $recipient))->delay(now()->addSeconds($delaySeconds));
                Mail::to($recipient->email)->queue($mailable);
                $delaySeconds += 2;
            }
        }
    }
}
