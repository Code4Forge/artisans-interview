<x-mail::message>
# Hello {{ $recipientName }},

**{{ $authorName }}** has posted a new announcement:

## {{ $title }}

{{ $body }}

@if ($announcement->attachment_path)
> **Attached File:** `{{ $announcement->attachment_name }}` (see email attachments to download)

<x-mail::button :url="route('admin.announcements.download', $announcement)">
Download Attachment
</x-mail::button>
@endif

<x-mail::button :url="url('/login')">
Log in to School Portal
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
