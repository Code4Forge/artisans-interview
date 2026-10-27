@extends('layouts.app')

@section('title', 'Announcement Details')

@section('content')
    <div class="flex w-full flex-col gap-6">
        <!-- Top Breadcrumb & Actions Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Announcement Details</h1>
                <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    <a href="{{ route('admin.dashboard.index') }}" class="hover:text-slate-700 transition-colors">Dashboard</a>
                    <span>&rarr;</span>
                    <a href="{{ route('admin.announcements.index') }}" class="hover:text-slate-700 transition-colors">Announcements</a>
                    <span>&rarr;</span>
                    <span class="text-slate-700 font-semibold">{{ Str::limit($announcement->title, 35) }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.announcements.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors">
                    &larr; Back to list
                </a>

                @php
                    $user = auth()->user();
                    $canDelete = $user->can('announcements.delete') && ($user->hasRole('admin') || (int) $announcement->author_id === (int) $user->id);
                @endphp

                @if ($canDelete)
                    <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" class="inline" onsubmit="return confirmDeleteAnnouncement(event)">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-red-500 transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Announcement
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Main Announcement Card -->
        <div class="flex w-full flex-col gap-6 border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <!-- Header: Title and Audience Badge -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-5">
                <div class="flex flex-col gap-1.5">
                    <span class="text-xs font-semibold tracking-wider uppercase text-slate-400">Title</span>
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">{{ $announcement->title }}</h2>
                </div>

                @php
                    $audienceBadge = match ($announcement->audience) {
                        \App\Enums\AnnouncementAudience::Teachers => ['bg-amber-100 text-amber-800 border-amber-200', 'Teachers'],
                        \App\Enums\AnnouncementAudience::Students => ['bg-blue-100 text-blue-800 border-blue-200', 'Students'],
                        \App\Enums\AnnouncementAudience::Parents => ['bg-purple-100 text-purple-800 border-purple-200', 'Parents'],
                        \App\Enums\AnnouncementAudience::Both => ['bg-emerald-100 text-emerald-800 border-emerald-200', 'Students & Parents'],
                        \App\Enums\AnnouncementAudience::Custom => ['bg-indigo-100 text-indigo-800 border-indigo-200', 'Custom Selection'],
                    };
                @endphp
                <div class="self-start sm:self-center">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold border {{ $audienceBadge[0] }}">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        Audience: {{ $audienceBadge[1] }}
                    </span>
                </div>
            </div>

            <!-- Author & Metadata Details Bar -->
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-slate-200 bg-slate-50/70 p-4 text-xs">
                <div class="flex items-center gap-3">
                    @if ($announcement->author?->avatar_path)
                        <img src="{{ Storage::url($announcement->author->avatar_path) }}" alt="{{ $announcement->author->name }}" class="h-10 w-10 rounded-full object-cover border border-slate-200">
                    @else
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 font-bold text-indigo-700 border border-indigo-200">
                            {{ strtoupper(substr($announcement->author?->name ?? 'A', 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900 text-sm">{{ $announcement->author?->name ?? 'School Staff' }}</span>
                            @php
                                $authorRole = $announcement->author?->roles->first()?->name ?? 'staff';
                            @endphp
                            <span class="inline-flex items-center rounded-md bg-white border border-slate-200 px-1.5 py-0.5 text-2xs font-semibold capitalize text-slate-600">
                                {{ $authorRole }}
                            </span>
                        </div>
                        <span class="text-slate-500">{{ $announcement->author?->email ?? '—' }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 text-slate-500">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Posted on <strong class="text-slate-700">{{ $announcement->created_at?->format('M d, Y') }}</strong> at <strong class="text-slate-700">{{ $announcement->created_at?->format('h:i A') }}</strong> ({{ $announcement->created_at?->diffForHumans() }})</span>
                </div>
            </div>

            <!-- Announcement Content Body -->
            <div class="flex flex-col gap-2">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Announcement Content</h3>
                <div class="rounded-lg border border-slate-200 bg-slate-50/40 p-5 text-sm text-slate-800 leading-relaxed whitespace-pre-wrap font-normal">
                    {{ $announcement->body }}
                </div>
            </div>

            <!-- Attachment Section -->
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Attachment</h3>
                    @if ($announcement->attachment_path)
                        <span class="text-xs text-slate-400">1 attachment included</span>
                    @endif
                </div>

                @if ($announcement->attachment_path)
                    @php
                        $ext = strtolower(pathinfo($announcement->attachment_name ?: '', PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true);
                        $isPdf = $ext === 'pdf';
                        $imageUrl = Storage::url($announcement->attachment_path);
                    @endphp

                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xs">
                        <!-- Attachment Meta Header Bar -->
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/75 px-4 py-3 sm:px-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $isImage ? 'bg-indigo-100 text-indigo-600' : ($isPdf ? 'bg-red-100 text-red-600' : 'bg-slate-100 text-slate-600') }}">
                                    @if ($isImage)
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    @elseif ($isPdf)
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-800 break-all">{{ $announcement->attachment_name ?: 'Attachment' }}</span>
                                    <span class="text-xs text-slate-400">
                                        {{ strtoupper($ext) }} {{ $isImage ? 'Image' : 'File' }} &bull; Available for download
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                @if ($isImage)
                                    <a
                                        href="{{ $imageUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        View Full Size
                                    </a>
                                @endif

                                <a
                                    href="{{ route('admin.announcements.download', $announcement) }}"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 transition-colors"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download Attachment
                                </a>
                            </div>
                        </div>

                        <!-- Image Preview Area (if image) -->
                        @if ($isImage)
                            <div class="flex flex-col items-center justify-center p-6 sm:p-8 bg-slate-50/40">
                                <a
                                    href="{{ $imageUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="Click to view full image in new tab"
                                    class="group relative inline-block overflow-hidden rounded-lg border border-slate-200 bg-white p-2 shadow-xs transition hover:border-indigo-300 hover:shadow-md"
                                >
                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $announcement->attachment_name }}"
                                        class="max-h-96 w-auto object-contain rounded transition group-hover:opacity-95"
                                    >
                                    <div class="absolute inset-0 flex items-center justify-center bg-indigo-900/0 opacity-0 transition group-hover:bg-indigo-900/20 group-hover:opacity-100">
                                        <span class="inline-flex items-center gap-1 rounded-md bg-white/95 px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-sm backdrop-blur">
                                            <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                                            </svg>
                                            Click to open full size
                                        </span>
                                    </div>
                                </a>
                                <span class="mt-2.5 text-2xs font-medium text-slate-400">Click image to expand and view in high resolution</span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="rounded-lg border border-dashed border-slate-200 p-4 text-xs text-slate-400 italic">
                        No attachment was provided with this announcement.
                    </div>
                @endif
            </div>

            <!-- Audience Details / Selected Recipients Section -->
            @php
                $audienceUsers = $audienceUsers ?? $announcement->audienceUsers();
            @endphp
            <div class="flex flex-col gap-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Audience Scope & Delivery</h3>
                <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-4 text-xs text-slate-700 flex items-start gap-3">
                    <svg class="h-5 w-5 text-indigo-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        @if ($announcement->audience === \App\Enums\AnnouncementAudience::Teachers)
                            <p class="font-semibold text-slate-800">Broadcast to all Teachers</p>
                            <p class="text-slate-500 mt-0.5">This announcement is visible to all active teachers in the portal and queued for their email notifications.</p>
                        @elseif ($announcement->audience === \App\Enums\AnnouncementAudience::Students)
                            <p class="font-semibold text-slate-800">Broadcast to Students</p>
                            <p class="text-slate-500 mt-0.5">This announcement is visible to enrolled students and queued for their email notifications.</p>
                        @elseif ($announcement->audience === \App\Enums\AnnouncementAudience::Parents)
                            <p class="font-semibold text-slate-800">Broadcast to Parents & Guardians</p>
                            <p class="text-slate-500 mt-0.5">This announcement is targeted to parents/guardians and queued for email delivery.</p>
                        @elseif ($announcement->audience === \App\Enums\AnnouncementAudience::Both)
                            <p class="font-semibold text-slate-800">Broadcast to Students & Parents</p>
                            <p class="text-slate-500 mt-0.5">This announcement is targeted to students and their linked parents/guardians with email notifications queued.</p>
                        @elseif ($announcement->audience === \App\Enums\AnnouncementAudience::Custom)
                            <p class="font-semibold text-slate-800">Custom Recipient Selection</p>
                            <p class="text-slate-500 mt-0.5">This announcement is specifically targeted to selected teachers, students, or parents.</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-2 mt-2">
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Selected Audience Recipients ({{ $audienceUsers->count() }})
                        </h4>
                        <p class="text-xs text-slate-500">Users who were selected as an audience to receive this announcement.</p>
                    </div>

                    @if ($audienceUsers->count() > 6)
                        <div class="relative w-64">
                            <input
                                type="text"
                                id="show-recipient-filter"
                                oninput="filterShowRecipients(this.value)"
                                placeholder="Filter recipients by name, email, role..."
                                class="w-full rounded-md border border-slate-300 bg-white pl-8 pr-3 py-1 text-xs outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            >
                            <svg class="h-3.5 w-3.5 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    @endif
                </div>

                <div id="show-recipients-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 max-h-80 overflow-y-auto p-2 border border-slate-200 bg-slate-50/30 rounded-lg">
                    @forelse ($audienceUsers as $recipient)
                        @php
                            $roleName = $recipient->roles->first()?->name ?? 'user';
                            $badgeColor = match ($roleName) {
                                'teacher' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'student' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'parent' => 'bg-purple-100 text-purple-800 border-purple-200',
                                default => 'bg-slate-100 text-slate-800 border-slate-200',
                            };
                        @endphp
                        <div class="show-recipient-card flex items-start justify-between gap-2 p-2.5 rounded-md border border-slate-200 bg-white" data-search="{{ strtolower($recipient->name . ' ' . $recipient->email . ' ' . $roleName) }}">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-2xs font-bold text-slate-600">
                                    {{ strtoupper(substr($recipient->name, 0, 1)) }}
                                </div>
                                <div class="flex flex-col truncate">
                                    <span class="text-xs font-bold text-slate-800 truncate">{{ $recipient->name }}</span>
                                    <span class="text-2xs text-slate-500 truncate">{{ $recipient->email }}</span>
                                    @if ($recipient->phone)
                                        <span class="text-2xs text-slate-400 truncate">{{ $recipient->phone }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="shrink-0 inline-flex items-center rounded px-1.5 py-0.5 text-2xs font-semibold uppercase border {{ $badgeColor }}">
                                {{ $roleName }}
                            </span>
                        </div>
                    @empty
                        <div class="col-span-full p-4 text-center text-xs text-slate-400 italic">
                            No recipients found for this audience.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Footer Actions Bar -->
            <div class="flex items-center justify-between border-t border-slate-100 pt-5">
                <a href="{{ route('admin.announcements.index') }}" class="rounded-md border border-slate-300 px-3.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    &larr; Back to list
                </a>

                @if ($canDelete)
                    <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" class="inline" onsubmit="return confirmDeleteAnnouncement(event)">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-md bg-red-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-red-500 transition-colors">
                            Delete Announcement
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function confirmDeleteAnnouncement(e) {
        if (!confirm('Are you sure you want to permanently delete this announcement? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
        return true;
    }

    function filterShowRecipients(search) {
        const query = (search || '').toLowerCase().trim();
        document.querySelectorAll('.show-recipient-card').forEach(card => {
            const dataSearch = card.getAttribute('data-search') || '';
            if (!query || dataSearch.includes(query)) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }
</script>
@endpush

