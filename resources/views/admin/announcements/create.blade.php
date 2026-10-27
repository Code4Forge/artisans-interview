@extends('layouts.app')

@section('title', 'New Announcement')

@push('styles')
    <style>
        /* Multiple Selection Container */
        .select2-container--default .select2-selection--multiple {
            display: flex !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            min-height: 42px !important;
            padding: 4px 32px 4px 8px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.375rem !important;
            background-color: #ffffff !important;
            box-sizing: border-box !important;
            position: relative !important;
            transition: all 0.15s ease-in-out !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2) !important;
            outline: none !important;
        }

        /* Rendered list inside container */
        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: center !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            gap: 6px !important;
            list-style: none !important;
        }

        /* Selected Choice (Chip/Badge) */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            display: inline-flex !important;
            align-items: center !important;
            position: relative !important;
            float: none !important;
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            color: #1e293b !important;
            border-radius: 0.375rem !important;
            padding: 2px 6px 2px 8px !important;
            font-size: 0.75rem !important;
            font-weight: 500 !important;
            line-height: 1.25rem !important;
            gap: 6px !important;
            margin: 2px 0 !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }

        /* Choice Display Text & Role Tag */
        .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            padding: 0 !important;
            margin: 0 !important;
            order: 1 !important;
            cursor: default !important;
        }

        /* Remove "x" Button on Choice Chip */
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: static !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            order: 2 !important;
            border: none !important;
            background: transparent !important;
            color: #94a3b8 !important;
            font-size: 14px !important;
            font-weight: bold !important;
            line-height: 1 !important;
            width: 16px !important;
            height: 16px !important;
            border-radius: 9999px !important;
            cursor: pointer !important;
            padding: 0 !important;
            margin: 0 !important;
            transition: all 0.15s ease-in-out !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            background-color: #fee2e2 !important;
            color: #ef4444 !important;
        }

        /* Clear All Button on the far right */
        .select2-container--default .select2-selection--multiple .select2-selection__clear {
            position: absolute !important;
            right: 8px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            margin: 0 !important;
            padding: 0 !important;
            font-size: 16px !important;
            font-weight: bold !important;
            color: #94a3b8 !important;
            line-height: 1 !important;
            cursor: pointer !important;
            transition: color 0.15s ease-in-out !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__clear:hover {
            color: #ef4444 !important;
        }

        /* Inline Search Input & Placeholder Centering */
        .select2-container--default .select2-search--inline {
            display: inline-flex !important;
            align-items: center !important;
            flex: 1 1 auto !important;
            margin: 0 !important;
            padding: 0 !important;
            float: none !important;
        }
        .select2-container--default .select2-search--inline .select2-search__field {
            margin: 0 !important;
            padding: 0 4px !important;
            height: 28px !important;
            line-height: 28px !important;
            border: none !important;
            outline: none !important;
            background: transparent !important;
            box-shadow: none !important;
            font-size: 0.875rem !important;
            color: #1e293b !important;
            vertical-align: middle !important;
            box-sizing: border-box !important;
            width: 100% !important;
        }
        .select2-container--default .select2-search--inline .select2-search__field::placeholder {
            color: #94a3b8 !important;
            line-height: 28px !important;
        }

        /* Center alignment for placeholder when no items are selected */
        .select2-container--default .select2-search--inline:only-child .select2-search__field {
            text-align: center !important;
        }
        .select2-container--default.select2-container--focus .select2-search--inline:only-child .select2-search__field {
            text-align: left !important;
        }
        .select2-container--default .select2-selection__choice ~ .select2-search--inline .select2-search__field {
            text-align: left !important;
        }

        /* Dropdown Styling */
        .select2-dropdown {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            z-index: 50;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #cbd5e1;
            border-radius: 0.375rem;
            padding: 6px 10px;
            font-size: 0.8125rem;
            outline: none;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #6366f1;
            color: white;
        }
        .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: #f1f5f9;
            color: #64748b;
        }
    </style>
@endpush

@section('content')
    <div class="flex w-full flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">New Announcement</h1>
            </div>
            <a href="{{ route('admin.announcements.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors">
                &larr; Back to list
            </a>
        </div>

        <div id="form-error-box" class="hidden border border-red-200 bg-red-50 p-3 text-xs text-red-700 rounded-md">
            <ul id="form-error-list" class="list-disc list-inside space-y-1"></ul>
        </div>

        <form id="announcement-create-form" method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data" onsubmit="handleAjaxSubmit(event)" class="flex w-full flex-col gap-6 border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            @csrf
            @include('admin.announcements._form')
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.announcements.index') }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" id="submit-btn" class="rounded-md bg-indigo-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 transition-colors">
                    Post Announcement
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    let $recipientsSelect = null;

    function formatUserOption(state) {
        if (!state.id) {
            return state.text;
        }
        const $ = window.jQuery || window.$;
        const element = state.element;
        const name = element ? element.getAttribute('data-name') : state.name;
        const email = element ? element.getAttribute('data-email') : state.email;
        const role = (element ? element.getAttribute('data-role') : state.role || 'user').toLowerCase();

        let badgeColor = 'bg-slate-100 text-slate-800 border-slate-200';
        if (role === 'teacher') badgeColor = 'bg-amber-100 text-amber-800 border-amber-200';
        else if (role === 'student') badgeColor = 'bg-blue-100 text-blue-800 border-blue-200';
        else if (role === 'parent') badgeColor = 'bg-purple-100 text-purple-800 border-purple-200';

        if (!$) {
            return `${name || state.text} (${email || ''}) [${role}]`;
        }

        return $(`
            <div class="flex items-center justify-between py-1 text-xs">
                <div class="flex flex-col min-w-0 pr-2">
                    <span class="font-semibold text-slate-900 truncate">${name || state.text}</span>
                    <span class="text-[11px] text-slate-500 truncate">${email || ''}</span>
                </div>
                <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold uppercase border ${badgeColor}">
                    ${role}
                </span>
            </div>
        `);
    }

    function formatUserSelection(state) {
        if (!state.id) {
            return state.text;
        }
        const element = state.element;
        const name = element ? element.getAttribute('data-name') : (state.name || (state.text ? state.text.split('(')[0].trim() : ''));
        return name || state.text;
    }

    function initRecipientsSelect() {
        const $ = window.jQuery || window.$;
        if (!$ || typeof $.fn.select2 !== 'function') {
            return;
        }
        const $el = $('#recipient_ids');
        if ($el.length && !$el.hasClass('select2-hidden-accessible')) {
            $recipientsSelect = $el.select2({
                placeholder: 'Search and select recipients by name, email, or role...',
                allowClear: true,
                width: '100%',
                closeOnSelect: false,
                templateResult: formatUserOption,
                templateSelection: formatUserSelection
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initRecipientsSelect();

        const btnSelectAll = document.getElementById('btn-select-all');
        if (btnSelectAll) {
            btnSelectAll.addEventListener('click', function() {
                const $ = window.jQuery || window.$;
                if ($ && $recipientsSelect) {
                    const allValues = $('#recipient_ids option').map(function() {
                        return $(this).val();
                    }).get();
                    $recipientsSelect.val(allValues).trigger('change');
                    document.querySelector('.select2-selection--multiple')?.classList.remove('!border-red-500');
                }
            });
        }

        const btnClearAll = document.getElementById('btn-clear-all');
        if (btnClearAll) {
            btnClearAll.addEventListener('click', function() {
                const $ = window.jQuery || window.$;
                if ($ && $recipientsSelect) {
                    $recipientsSelect.val(null).trigger('change');
                }
            });
        }
    });

    function handleAudienceChange(val) {
        const wrapper = document.getElementById('custom-recipients-wrapper');
        if (!wrapper) return;
        if (val === 'custom') {
            wrapper.classList.remove('hidden');
            initRecipientsSelect();
        } else {
            wrapper.classList.add('hidden');
        }
    }

    function handleAjaxSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('submit-btn');
        const errorBox = document.getElementById('form-error-box');
        const errorList = document.getElementById('form-error-list');
        const $ = window.jQuery || window.$;

        const title = form.querySelector('[name="title"]').value.trim();
        const body = form.querySelector('[name="body"]').value.trim();
        const audience = form.querySelector('[name="audience"]').value;

        errorBox.classList.add('hidden');
        errorList.innerHTML = '';
        const errors = [];

        document.querySelector('.select2-selection--multiple')?.classList.remove('!border-red-500');

        if (!title) errors.push('The title field is required.');
        if (!body) errors.push('The announcement body field is required.');
        if (!audience) errors.push('The audience field is required.');

        if (audience === 'custom') {
            let selectedRecipients = [];
            if ($ && $recipientsSelect) {
                selectedRecipients = $('#recipient_ids').val() || [];
            } else {
                const selectEl = document.getElementById('recipient_ids');
                selectedRecipients = selectEl ? Array.from(selectEl.selectedOptions).map(o => o.value) : [];
            }

            if (selectedRecipients.length === 0) {
                errors.push('Please select at least one recipient for custom announcement.');
                document.querySelector('.select2-selection--multiple')?.classList.add('!border-red-500');
            }
        }

        if (errors.length > 0) {
            errors.forEach(err => {
                const li = document.createElement('li');
                li.textContent = err;
                errorList.appendChild(li);
            });
            errorBox.classList.remove('hidden');
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
            if (window.toastr) window.toastr.error(errors[0]);
            return;
        }

        submitBtn.disabled = true;
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Posting...';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: formData
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(result => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;

            if (result.status >= 200 && result.status < 300 && result.body.success) {
                if (window.toastr) window.toastr.success(result.body.message);
                setTimeout(() => {
                    window.location.href = '{{ route('admin.announcements.index') }}';
                }, 800);
            } else {
                let errs = [];
                if (result.body.errors) {
                    Object.values(result.body.errors).forEach(eArr => errs.push(...eArr));
                } else if (result.body.message) {
                    errs.push(result.body.message);
                } else {
                    errs.push('An unexpected error occurred.');
                }
                errs.forEach(err => {
                    const li = document.createElement('li');
                    li.textContent = err;
                    errorList.appendChild(li);
                });
                errorBox.classList.remove('hidden');
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
                if (window.toastr) window.toastr.error(errs[0]);
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            const li = document.createElement('li');
            li.textContent = 'Network or server error. Please try again.';
            errorList.appendChild(li);
            errorBox.classList.remove('hidden');
            if (window.toastr) window.toastr.error('Network or server error.');
        });
    }
</script>
@endpush
