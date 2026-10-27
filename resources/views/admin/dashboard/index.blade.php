@extends('layouts.app')

@section('title', 'Dashboard — ' . $roleLabel)

@section('content')
<div class="flex w-full flex-col gap-6">
    <!-- Welcome Hero Card -->
    <section class="flex flex-col gap-2.5 rounded-xl border border-slate-200 bg-white p-6 sm:p-8 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-indigo-700 border border-indigo-100">
                    {{ $roleLabel }}
                </span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs text-slate-500">{{ now()->format('l, F j, Y') }}</span>
            </div>

            @if (!empty($permissions['announcements_create']))
                <a href="{{ route('admin.announcements.create') }}" class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    New Announcement
                </a>
            @endif
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="text-sm text-slate-600 max-w-3xl leading-relaxed">{{ $roleSummary }}</p>
    </section>

    <!-- Permission-Based Metric / Count Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Teachers Count Card -->
        @if (!empty($permissions['teachers_view']))
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-xs transition hover:border-amber-300">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Teachers</span>
                    <span class="text-2xl font-bold text-slate-900">{{ number_format($counts['teachers'] ?? 0) }}</span>
                    <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center gap-1 text-2xs font-semibold text-amber-600 hover:text-amber-700 hover:underline">
                        View directory &rarr;
                    </a>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-50 text-amber-600 border border-amber-100">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
        @endif

        <!-- Students Count Card -->
        @if (isset($counts['students']))
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-xs transition hover:border-blue-300">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{ $role === 'teacher' ? 'My Students' : ($role === 'parent' ? 'My Children' : 'Students') }}
                    </span>
                    <span class="text-2xl font-bold text-slate-900">{{ number_format($counts['students']) }}</span>
                    @if (!empty($permissions['students_view']))
                        <a href="{{ route('admin.students.index') }}" class="inline-flex items-center gap-1 text-2xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                            Manage students &rarr;
                        </a>
                    @else
                        <span class="text-2xs text-slate-400">Enrolled student(s)</span>
                    @endif
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 text-blue-600 border border-blue-100">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                </div>
            </div>
        @endif

        <!-- Parents Count Card -->
        @if (isset($counts['parents']))
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-xs transition hover:border-purple-300">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{ $role === 'student' ? 'My Parents' : ($role === 'teacher' ? 'Linked Parents' : 'Parents') }}
                    </span>
                    <span class="text-2xl font-bold text-slate-900">{{ number_format($counts['parents']) }}</span>
                    <span class="text-2xs text-slate-400">
                        {{ $role === 'student' ? 'Guardians contact' : 'Active parent contacts' }}
                    </span>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-50 text-purple-600 border border-purple-100">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        @endif

        <!-- Announcements Count Card -->
        @if (!empty($permissions['announcements_view']))
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-xs transition hover:border-indigo-300">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{ $role === 'teacher' ? 'My Announcements' : 'Announcements' }}
                    </span>
                    <span class="text-2xl font-bold text-slate-900">
                        {{ number_format($counts['my_announcements'] ?? ($counts['announcements'] ?? 0)) }}
                    </span>
                    <a href="{{ route('admin.announcements.index') }}" class="inline-flex items-center gap-1 text-2xs font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">
                        View bulletin &rarr;
                    </a>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                </div>
            </div>
        @endif

        <!-- Roles & Permissions Card (for admin) -->
        @if (!empty($permissions['roles_manage']))
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-xs transition hover:border-emerald-300">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Roles &amp; Access</span>
                    <span class="text-2xl font-bold text-slate-900">{{ number_format($counts['roles'] ?? 4) }}</span>
                    <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-1 text-2xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">
                        Configure roles &rarr;
                    </a>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
        @endif
    </div>

    <!-- Charts & Visual Analytics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart 1: Announcements by Audience -->
        @if (!empty($charts['audience']))
            <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">
                            {{ $role === 'teacher' ? 'My Announcements by Target Audience' : 'Announcements by Audience' }}
                        </h2>
                        <p class="text-xs text-slate-400">Distribution across recipient scopes</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-2xs font-semibold text-slate-600">
                        {{ array_sum($charts['audience']['data']) }} Total
                    </span>
                </div>

                <div class="relative flex items-center justify-center h-64">
                    <canvas id="audienceChart"></canvas>
                </div>

                <!-- Fallback Legend / Pill Indicators -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-2 border-t border-slate-100 text-xs">
                    @foreach ($charts['audience']['labels'] as $idx => $label)
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full shrink-0" style="background-color: {{ $charts['audience']['colors'][$idx] }}"></span>
                            <span class="text-slate-600 truncate">{{ $label }}:</span>
                            <strong class="text-slate-800">{{ $charts['audience']['data'][$idx] }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Chart 2: Monthly Announcements Trend -->
        @if (!empty($charts['monthly']))
            <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Monthly Announcements Volume</h2>
                        <p class="text-xs text-slate-400">Posting activity over the past 6 months</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-2xs font-semibold text-indigo-700">
                        Past 6 Months
                    </span>
                </div>

                <div class="relative flex items-center justify-center h-64">
                    <canvas id="monthlyChart"></canvas>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs text-slate-500">
                    <span>Peak Month: <strong>{{ $charts['monthly']['labels'][array_search(max($charts['monthly']['data'] ?: [0]), $charts['monthly']['data'] ?: [0])] ?? 'N/A' }}</strong></span>
                    <span>Total Posted: <strong>{{ array_sum($charts['monthly']['data']) }}</strong></span>
                </div>
            </div>
        @endif



        <!-- Admin Chart: System Users by Role -->
        @if (!empty($charts['role_distribution']))
            <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">User Distribution by Role</h2>
                        <p class="text-xs text-slate-400">Active accounts registered in school SMS</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-2xs font-semibold text-slate-600">
                        {{ array_sum($charts['role_distribution']['data']) }} Total Users
                    </span>
                </div>

                <div class="relative flex items-center justify-center h-64">
                    <canvas id="roleDistributionChart"></canvas>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-slate-100 text-xs">
                    @foreach ($charts['role_distribution']['labels'] as $idx => $label)
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full shrink-0" style="background-color: {{ $charts['role_distribution']['colors'][$idx] }}"></span>
                            <span class="text-slate-600 truncate">{{ $label }}:</span>
                            <strong class="text-slate-800">{{ $charts['role_distribution']['data'][$idx] }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Teacher Chart: Parent Linkage Status -->
        @if (!empty($charts['parent_linkage']))
            <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Assigned Students Parent Linkage</h2>
                        <p class="text-xs text-slate-400">Proportion of your assigned students with linked parents</p>
                    </div>
                </div>

                <div class="relative flex items-center justify-center h-64">
                    <canvas id="parentLinkageChart"></canvas>
                </div>

                <div class="flex items-center justify-around pt-2 border-t border-slate-100 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                        <span class="text-slate-600">Linked Parents:</span>
                        <strong class="text-slate-800">{{ $charts['parent_linkage']['data'][0] }}</strong>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-rose-500"></span>
                        <span class="text-slate-600">No Parent Linked:</span>
                        <strong class="text-slate-800">{{ $charts['parent_linkage']['data'][1] }}</strong>
                    </div>
                </div>
            </div>
        @endif

        <!-- Student Chart: Attachment vs Text Announcements -->
        @if (!empty($charts['attachments']))
            <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Announcements Format</h2>
                        <p class="text-xs text-slate-400">Breakdown of announcements with attachments vs text</p>
                    </div>
                </div>

                <div class="relative flex items-center justify-center h-64">
                    <canvas id="attachmentsChart"></canvas>
                </div>

                <div class="flex items-center justify-around pt-2 border-t border-slate-100 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-indigo-500"></span>
                        <span class="text-slate-600">With Downloadable Attachment:</span>
                        <strong class="text-slate-800">{{ $charts['attachments']['data'][0] }}</strong>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-slate-300"></span>
                        <span class="text-slate-600">Text Notice:</span>
                        <strong class="text-slate-800">{{ $charts['attachments']['data'][1] }}</strong>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Role-Specific Information & Quick Activity Lists -->
    <!-- 1. Student Personal Overview -->
    @if (!empty($student_info))
        <div class="grid grid-cols-1 gap-6">
            <!-- Linked Parents Card -->
            <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">My Linked Parents / Guardians</h3>
                <div class="flex flex-col gap-2">
                    @forelse ($student_info['parents'] as $parent)
                        <div class="flex items-center justify-between p-3 rounded-lg border border-slate-200 bg-slate-50/70 text-xs">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-100 text-purple-700 font-bold">
                                    {{ strtoupper(substr($parent->name, 0, 1)) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800">{{ $parent->name }}</span>
                                    <span class="text-slate-500">{{ $parent->email }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-flex rounded-md bg-purple-50 px-2 py-0.5 font-semibold text-purple-700 border border-purple-100">
                                    {{ $parent->pivot?->relationship ?? 'Parent' }}
                                </span>
                                @if ($parent->phone)
                                    <span class="text-2xs text-slate-400 mt-0.5">{{ $parent->phone }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic">No linked parents configured.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- 2. Parent Overview of Children -->
    @if (!empty($parent_info))
        <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">My Enrolled Children</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @forelse ($parent_info['children'] as $child)
                    <div class="flex flex-col gap-2 p-4 rounded-lg border border-slate-200 bg-slate-50/60">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-700 font-bold">
                                {{ strtoupper(substr($child->name, 0, 1)) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-900">{{ $child->name }}</span>
                                <span class="text-xs text-slate-500">{{ $child->email }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic col-span-full">No children linked to this account.</p>
                @endforelse
            </div>
        </div>
    @endif

    <!-- 3. Recent Announcements Table -->
    @if (!empty($recent['announcements']) && count($recent['announcements']) > 0)
        <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Recent Announcements</h3>
                    <p class="text-xs text-slate-400">Latest notices and communications</p>
                </div>
                <a href="{{ route('admin.announcements.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                    View all announcements &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 uppercase tracking-wider font-semibold">
                            <th class="py-2.5 px-3">Title</th>
                            <th class="py-2.5 px-3">Author</th>
                            <th class="py-2.5 px-3">Audience</th>
                            <th class="py-2.5 px-3">Attachment</th>
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recent['announcements'] as $item)
                            @php
                                $badgeColor = match ($item->audience) {
                                    \App\Enums\AnnouncementAudience::Teachers => 'bg-amber-100 text-amber-800',
                                    \App\Enums\AnnouncementAudience::Students => 'bg-blue-100 text-blue-800',
                                    \App\Enums\AnnouncementAudience::Parents => 'bg-purple-100 text-purple-800',
                                    \App\Enums\AnnouncementAudience::Both => 'bg-emerald-100 text-emerald-800',
                                    \App\Enums\AnnouncementAudience::Custom => 'bg-indigo-100 text-indigo-800',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-2.5 px-3 font-semibold text-slate-900">
                                    <a href="{{ route('admin.announcements.show', $item) }}" class="hover:text-indigo-600">
                                        {{ Str::limit($item->title, 40) }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-3 text-slate-600">{{ $item->author?->name ?? 'System' }}</td>
                                <td class="py-2.5 px-3">
                                    <span class="inline-flex items-center rounded px-2 py-0.5 text-2xs font-semibold {{ $badgeColor }}">
                                        {{ $item->audience->label() }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-slate-500">
                                    @if ($item->attachment_path)
                                        <a href="{{ route('admin.announcements.download', $item) }}" class="inline-flex items-center gap-1 text-indigo-600 hover:underline">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                            File
                                        </a>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-slate-500">{{ $item->created_at?->format('M d, Y') }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    <a href="{{ route('admin.announcements.show', $item) }}" class="inline-flex items-center gap-1 text-2xs font-semibold text-indigo-600 hover:text-indigo-800">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    // Default global Chart.js styling
    Chart.defaults.font.family = "'Instrument Sans', ui-sans-serif, system-ui, sans-serif";
    Chart.defaults.color = '#64748b';

    // 1. Audience Doughnut Chart
    const audienceCanvas = document.getElementById('audienceChart');
    if (audienceCanvas) {
        new Chart(audienceCanvas, {
            type: 'doughnut',
            data: {
                labels: @json($charts['audience']['labels'] ?? []),
                datasets: [{
                    data: @json($charts['audience']['data'] ?? []),
                    backgroundColor: @json($charts['audience']['colors'] ?? []),
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 14 }
                    }
                },
                cutout: '68%'
            }
        });
    }

    // 2. Monthly Activity Bar Chart
    const monthlyCanvas = document.getElementById('monthlyChart');
    if (monthlyCanvas) {
        new Chart(monthlyCanvas, {
            type: 'bar',
            data: {
                labels: @json($charts['monthly']['labels'] ?? []),
                datasets: [{
                    label: 'Announcements',
                    data: @json($charts['monthly']['data'] ?? []),
                    backgroundColor: '#6366f1',
                    borderRadius: 6,
                    maxBarThickness: 32,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 3. Admin: User Role Distribution Chart
    const roleDistCanvas = document.getElementById('roleDistributionChart');
    if (roleDistCanvas) {
        new Chart(roleDistCanvas, {
            type: 'doughnut',
            data: {
                labels: @json($charts['role_distribution']['labels'] ?? []),
                datasets: [{
                    data: @json($charts['role_distribution']['data'] ?? []),
                    backgroundColor: @json($charts['role_distribution']['colors'] ?? []),
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 12 }
                    }
                },
                cutout: '68%'
            }
        });
    }

    // 5. Teacher: Parent Linkage Status Chart
    const parentLinkageCanvas = document.getElementById('parentLinkageChart');
    if (parentLinkageCanvas) {
        new Chart(parentLinkageCanvas, {
            type: 'doughnut',
            data: {
                labels: @json($charts['parent_linkage']['labels'] ?? []),
                datasets: [{
                    data: @json($charts['parent_linkage']['data'] ?? []),
                    backgroundColor: @json($charts['parent_linkage']['colors'] ?? []),
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 12 }
                    }
                },
                cutout: '68%'
            }
        });
    }

    // 6. Student: Attachments Chart
    const attachmentsCanvas = document.getElementById('attachmentsChart');
    if (attachmentsCanvas) {
        new Chart(attachmentsCanvas, {
            type: 'doughnut',
            data: {
                labels: @json($charts['attachments']['labels'] ?? []),
                datasets: [{
                    data: @json($charts['attachments']['data'] ?? []),
                    backgroundColor: @json($charts['attachments']['colors'] ?? []),
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 12 }
                    }
                },
                cutout: '68%'
            }
        });
    }
});
</script>
@endpush
