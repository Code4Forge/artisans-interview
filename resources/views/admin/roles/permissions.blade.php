@extends('layouts.app')

@section('title', ucfirst($role->name) . " Role's Permissions")

@section('content')
    <div class="flex flex-col gap-6 w-full">
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 capitalize">{{ $role->name }} Role's Permissions</h1>
                <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    <a href="{{ route('admin.dashboard.index') }}" class="hover:text-slate-700 transition-colors">Dashboard</a>
                    <span>&rarr;</span>
                    <a href="{{ route('admin.roles.index') }}" class="hover:text-slate-700 transition-colors">Roles</a>
                    <span>&rarr;</span>
                    <span class="text-slate-700 font-semibold">Permissions</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    onclick="expandAll(true)"
                    class="rounded-md bg-cyan-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-cyan-700 transition-colors"
                >
                    Expand All
                </button>
                <button
                    type="button"
                    onclick="expandAll(false)"
                    class="rounded-md bg-purple-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-purple-700 transition-colors"
                >
                    Collapse All
                </button>
                <button
                    type="button"
                    onclick="selectAllPermissions(true)"
                    class="rounded-md bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-emerald-700 transition-colors"
                >
                    Select All
                </button>
                <button
                    type="button"
                    onclick="selectAllPermissions(false)"
                    class="rounded-md bg-rose-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 transition-colors"
                >
                    Unselect All
                </button>
            </div>
        </div>

        <form id="role-permissions-form" action="{{ route('admin.roles.permissions.update', $role) }}" method="POST" onsubmit="handlePermissionsSubmit(event)">
            @csrf
            @method('PUT')

            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-900 capitalize">Permissions for {{ $role->name }}</h2>
                <button
                    type="submit"
                    id="save-permissions-btn"
                    class="rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 transition-colors"
                >
                    Save Permissions
                </button>
            </div>

            <div class="space-y-4">
                @foreach ($groupedPermissions as $group => $permissions)
                    @php
                        $groupId = \Illuminate\Support\Str::slug($group);
                        $count = count($permissions);
                        $checkedCount = collect($permissions)->filter(fn($p) => in_array($p['name'], $assignedPermissions, true))->count();
                    @endphp
                    <div class="border border-slate-200 bg-white shadow-xs">
                        <!-- Module Header Accordion Trigger -->
                        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    id="group-check-{{ $groupId }}"
                                    onchange="toggleGroupCheck('{{ $groupId }}', this.checked)"
                                    {{ $checkedCount === $count ? 'checked' : '' }}
                                    class="h-4 w-4 rounded-sm border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                >
                                <label for="group-check-{{ $groupId }}" class="text-sm font-bold text-slate-900 cursor-pointer select-none">
                                    {{ $group }}
                                </label>
                                <span class="rounded-md bg-purple-600 px-2 py-0.5 text-xs font-bold text-white">
                                    {{ $count }}
                                </span>
                            </div>

                            <button
                                type="button"
                                onclick="toggleAccordion('{{ $groupId }}')"
                                class="p-1 text-slate-500 hover:text-slate-800 transition-colors"
                                aria-label="Toggle section"
                            >
                                <svg id="arrow-{{ $groupId }}" class="h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>

                        <!-- Module Permissions List -->
                        <div id="content-{{ $groupId }}" class="p-5">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                                @foreach ($permissions as $perm)
                                    @php
                                        $isChecked = in_array($perm['name'], $assignedPermissions, true);
                                        $isAdminRequired = $role->name === 'admin' && in_array($perm['name'], ['roles.manage', 'dashboard.view'], true);
                                    @endphp
                                    <label class="flex items-start gap-2.5 p-2 rounded-md hover:bg-slate-50 transition-colors cursor-pointer {{ $isAdminRequired ? 'opacity-70' : '' }}">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $perm['name'] }}"
                                            data-group="{{ $groupId }}"
                                            {{ $isChecked ? 'checked' : '' }}
                                            {{ $isAdminRequired ? 'checked onclick="return false;"' : '' }}
                                            onchange="updateGroupParentCheck('{{ $groupId }}')"
                                            class="perm-checkbox mt-0.5 h-4 w-4 rounded-sm border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                        >
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-slate-800 select-none">
                                                {{ $perm['label'] }}
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end">
                <button
                    type="submit"
                    id="save-permissions-btn-bottom"
                    class="rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 transition-colors"
                >
                    Save Permissions
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function toggleAccordion(groupId) {
        const content = document.getElementById(`content-${groupId}`);
        const arrow = document.getElementById(`arrow-${groupId}`);
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            arrow.classList.remove('-rotate-90');
        } else {
            content.classList.add('hidden');
            arrow.classList.add('-rotate-90');
        }
    }

    function expandAll(expand) {
        const contents = document.querySelectorAll('[id^="content-"]');
        const arrows = document.querySelectorAll('[id^="arrow-"]');
        contents.forEach(c => {
            if (expand) c.classList.remove('hidden');
            else c.classList.add('hidden');
        });
        arrows.forEach(a => {
            if (expand) a.classList.remove('-rotate-90');
            else a.classList.add('-rotate-90');
        });
    }

    function toggleGroupCheck(groupId, state) {
        const checkboxes = document.querySelectorAll(`input[data-group="${groupId}"]`);
        checkboxes.forEach(cb => {
            if (cb.getAttribute('onclick') !== 'return false;') {
                cb.checked = state;
            }
        });
    }

    function updateGroupParentCheck(groupId) {
        const checkboxes = document.querySelectorAll(`input[data-group="${groupId}"]`);
        const groupCheck = document.getElementById(`group-check-${groupId}`);
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        groupCheck.checked = allChecked;
    }

    function selectAllPermissions(state) {
        const checkboxes = document.querySelectorAll('.perm-checkbox');
        checkboxes.forEach(cb => {
            if (cb.getAttribute('onclick') !== 'return false;') {
                cb.checked = state;
            }
        });
        const groupChecks = document.querySelectorAll('[id^="group-check-"]');
        groupChecks.forEach(gc => gc.checked = state);
    }

    function handlePermissionsSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const btnTop = document.getElementById('save-permissions-btn');
        const btnBottom = document.getElementById('save-permissions-btn-bottom');

        btnTop.disabled = true;
        btnBottom.disabled = true;
        btnTop.textContent = 'Saving...';
        btnBottom.textContent = 'Saving...';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(result => {
            btnTop.disabled = false;
            btnBottom.disabled = false;
            btnTop.textContent = 'Save Permissions';
            btnBottom.textContent = 'Save Permissions';

            if (result.status >= 200 && result.status < 300 && result.body.success) {
                if (window.toastr) window.toastr.success(result.body.message);
            } else {
                if (window.toastr) window.toastr.error(result.body.message || 'Failed to update permissions.');
            }
        })
        .catch(err => {
            btnTop.disabled = false;
            btnBottom.disabled = false;
            btnTop.textContent = 'Save Permissions';
            btnBottom.textContent = 'Save Permissions';
            if (window.toastr) window.toastr.error('A network error occurred.');
        });
    }
</script>
@endpush

