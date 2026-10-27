<?php

namespace App\Mail;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

#[DeleteWhenMissingModels]
class AnnouncementNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Delete the job if its models no longer exist.
     */
    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public Announcement $announcement,
        public User $recipient,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Announcement: '.$this->announcement->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.announcements.notification',
            with: [
                'recipientName' => $this->recipient->name,
                'authorName' => $this->announcement->author?->name ?? 'School Staff',
                'title' => $this->announcement->title,
                'body' => $this->announcement->body,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if ($this->announcement->attachment_path) {
            $path = Storage::disk('public')->path($this->announcement->attachment_path);
            if (file_exists($path)) {
                return [
                    Attachment::fromPath($path)
                        ->as($this->announcement->attachment_name ?: 'attachment'),
                ];
            }
        }

        return [];
    }
}
