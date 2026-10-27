@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
    <div class="flex flex-col gap-6 w-full">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Announcements</h1>
                <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    <a href="{{ route('admin.dashboard.index') }}" class="hover:text-slate-700 transition-colors">Dashboard</a>
                    <span>&rarr;</span>
                    <span class="text-slate-700 font-semibold">Announcements</span>
                </div>
            </div>

            @can('announcements.create')
                <a
                    href="{{ route('admin.announcements.create') }}"
                    class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Announcement
                </a>
            @endcan
        </div>

        <div class="w-full border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
            <div class="w-full overflow-x-auto">
                <table id="announcements-table" class="w-full text-left text-sm">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Audience</th>
                            <th>Attachment</th>
                            <th>Posted</th>
                            <th class="w-24 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let announcementsDataTable = null;

    document.addEventListener('DOMContentLoaded', function () {
        const table = document.getElementById('announcements-table');
        if (table && window.DataTable) {
            announcementsDataTable = new window.DataTable(table, {
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('admin.announcements.index') }}',
                columns: [
                    { data: 'title', name: 'title', className: 'font-semibold text-slate-900' },
                    { data: 'author_name', name: 'author.name', orderable: false },
                    { data: 'audience_label', name: 'audience', orderable: false },
                    { data: 'has_attachment', name: 'attachment_name', orderable: false },
                    { data: 'formatted_created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[4, 'desc']],
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    search: "Search:",
                    searchPlaceholder: "Search announcements...",
                    lengthMenu: "Show _MENU_ entries"
                }
            });
        }
    });

    window.handleAjaxDelete = function(form) {
        const url = form.getAttribute('action');
        const token = form.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(result => {
            if (result.status >= 200 && result.status < 300 && result.body.success) {
                if (window.toastr) window.toastr.success(result.body.message);
                if (announcementsDataTable) announcementsDataTable.ajax.reload(null, false);
            } else {
                const msg = result.body.message || 'Cannot delete announcement.';
                if (window.toastr) {
                    window.toastr.error(msg);
                } else if (window.Swal) {
                    window.Swal.fire('Cannot Delete', msg, 'error');
                }
            }
        })
        .catch(err => {
            if (window.toastr) {
                window.toastr.error('Network error. Unable to delete.');
            }
        });
    };
</script>
@endpush
