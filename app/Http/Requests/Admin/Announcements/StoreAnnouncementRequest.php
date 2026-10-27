<?php

namespace App\Http\Requests\Admin\Announcements;

use App\Enums\AnnouncementAudience;
use App\Policies\AnnouncementPolicy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\Enum;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(AnnouncementPolicy::class)->create($this->user());
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'audience' => ['required', new Enum(AnnouncementAudience::class)],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp'],
            'recipient_ids' => ['nullable', 'array'],
            'recipient_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array{title: string, body: string, audience: string}
     */
    public function announcementAttributes(): array
    {
        return $this->safe()->only(['title', 'body', 'audience']);
    }

    public function attachmentFile(): ?UploadedFile
    {
        return $this->file('attachment');
    }

    /**
     * @return list<int>
     */
    public function recipientIds(): array
    {
        return array_map('intval', $this->input('recipient_ids', []));
    }
}
