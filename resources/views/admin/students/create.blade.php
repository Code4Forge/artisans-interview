@extends('layouts.app')

@section('title', 'Add Student')

@section('content')
    <div class="flex w-full flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add New Student</h1>
            </div>
            <a href="{{ route('admin.students.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors">
                &larr; Back to list
            </a>
        </div>

        <!-- Inline error box for AJAX validation failures -->
        <div id="form-error-box" class="hidden border border-red-200 bg-red-50 p-3 text-xs text-red-700 rounded-md">
            <ul id="form-error-list" class="list-disc list-inside space-y-1"></ul>
        </div>

        <form id="student-create-form" method="POST" action="{{ route('admin.students.store') }}" onsubmit="handleAjaxSubmit(event)" class="flex w-full flex-col gap-6 border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            @csrf
            @include('admin.students._form')
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.students.index') }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" id="submit-btn" class="rounded-md bg-indigo-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 transition-colors">
                    Create Student
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    let parentIndex = {{ count(old('parents', [])) }};

    function checkEmptyParentsState() {
        const container = document.getElementById('parents-container');
        if (!container) return;
        const rows = container.querySelectorAll('.parent-row');
        container.querySelectorAll('#no-parents-hint').forEach(el => el.remove());
        if (rows.length === 0) {
            const hint = document.createElement('p');
            hint.id = 'no-parents-hint';
            hint.className = 'text-xs text-slate-400 italic py-2';
            hint.textContent = 'No parents linked. Click "Add More Parent" above to add one.';
            container.appendChild(hint);
        }
    }

    function addParentRow() {
        const container = document.getElementById('parents-container');
        if (!container) return;
        container.querySelectorAll('#no-parents-hint').forEach(el => el.remove());

        const row = document.createElement('div');
        row.className = 'parent-row flex flex-wrap sm:flex-nowrap items-end gap-3 p-3.5 rounded-md border border-slate-200 bg-slate-50/50';
        row.innerHTML = `
            <div class="w-full sm:w-1/4 flex flex-col gap-1">
                <label class="text-xs font-semibold text-slate-700">Parent Name <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    name="parents[${parentIndex}][name]"
                    placeholder="e.g. Mary Smith"
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                >
            </div>
            <div class="w-full sm:w-1/4 flex flex-col gap-1">
                <label class="text-xs font-semibold text-slate-700">Parent Email <span class="text-red-500">*</span></label>
                <input
                    type="email"
                    name="parents[${parentIndex}][email]"
                    placeholder="e.g. parent@school.test"
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                >
            </div>
            <div class="w-full sm:w-1/4 flex flex-col gap-1">
                <label class="text-xs font-semibold text-slate-700">Contact Number</label>
                <input
                    type="text"
                    name="parents[${parentIndex}][phone]"
                    placeholder="e.g. +1 555 123 4567"
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                >
            </div>
            <div class="w-full sm:w-1/5 flex flex-col gap-1">
                <label class="text-xs font-semibold text-slate-700">Relationship</label>
                <select
                    name="parents[${parentIndex}][relationship]"
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                >
                    @foreach (\App\Enums\RelationshipStatus::cases() as $rel)
                        <option value="{{ $rel->value }}">{{ $rel->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pb-1">
                <button
                    type="button"
                    onclick="removeParentRow(this)"
                    class="action-btn action-btn-delete"
                    title="Remove Parent"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        `;
        container.appendChild(row);
        parentIndex++;

        const newNameInput = row.querySelector('input[name*="[name]"]');
        if (newNameInput) {
            newNameInput.focus();
        }
    }

    function removeParentRow(btn) {
        const row = btn.closest('.parent-row');
        if (row) {
            row.remove();
        }
        checkEmptyParentsState();
    }

    function handleAjaxSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('submit-btn');
        const errorBox = document.getElementById('form-error-box');
        const errorList = document.getElementById('form-error-list');

        const nameInput = form.querySelector('[name="name"]');
        const emailInput = form.querySelector('[name="email"]');
        const passwordInput = form.querySelector('[name="password"]');
        const passwordConfInput = form.querySelector('[name="password_confirmation"]');

        errorBox.classList.add('hidden');
        errorList.innerHTML = '';
        const errors = [];

        // Reset previous red borders
        form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

        let firstInvalidInput = null;

        if (!nameInput.value.trim()) {
            errors.push('The student name field is required.');
            nameInput.classList.add('border-red-500');
            if (!firstInvalidInput) firstInvalidInput = nameInput;
        }
        if (!emailInput.value.trim()) {
            errors.push('The student email field is required.');
            emailInput.classList.add('border-red-500');
            if (!firstInvalidInput) firstInvalidInput = emailInput;
        }
        if (!passwordInput.value) {
            errors.push('The password field is required.');
            passwordInput.classList.add('border-red-500');
            if (!firstInvalidInput) firstInvalidInput = passwordInput;
        }
        if (passwordInput.value && passwordInput.value.length < 8) {
            errors.push('The password must be at least 8 characters.');
            passwordInput.classList.add('border-red-500');
            if (!firstInvalidInput) firstInvalidInput = passwordInput;
        }
        if (passwordInput.value !== passwordConfInput.value) {
            errors.push('The password field confirmation does not match.');
            passwordConfInput.classList.add('border-red-500');
            if (!firstInvalidInput) firstInvalidInput = passwordConfInput;
        }

        // Validate dynamic parent rows
        const parentRows = form.querySelectorAll('.parent-row');
        const parentEmails = [];
        const studentEmail = emailInput ? emailInput.value.trim().toLowerCase() : '';

        parentRows.forEach((row, idx) => {
            const pNameInput = row.querySelector('input[name*="[name]"]');
            const pEmailInput = row.querySelector('input[name*="[email]"]');
            const pName = pNameInput ? pNameInput.value.trim() : '';
            const pEmail = pEmailInput ? pEmailInput.value.trim().toLowerCase() : '';

            // If a row exists, it cannot be empty or incomplete
            if (!pName && !pEmail) {
                errors.push(`Parent row #${idx + 1} is empty. Please fill in Parent Name and Parent Email, or remove this row.`);
                if (pNameInput) {
                    pNameInput.classList.add('border-red-500');
                    if (!firstInvalidInput) firstInvalidInput = pNameInput;
                }
                if (pEmailInput) {
                    pEmailInput.classList.add('border-red-500');
                }
            } else if (!pName) {
                errors.push(`Parent row #${idx + 1}: Parent Name is required.`);
                if (pNameInput) {
                    pNameInput.classList.add('border-red-500');
                    if (!firstInvalidInput) firstInvalidInput = pNameInput;
                }
            } else if (!pEmail) {
                errors.push(`Parent row #${idx + 1}: Parent Email is required.`);
                if (pEmailInput) {
                    pEmailInput.classList.add('border-red-500');
                    if (!firstInvalidInput) firstInvalidInput = pEmailInput;
                }
            } else {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(pEmail)) {
                    errors.push(`Parent row #${idx + 1}: Please enter a valid email address.`);
                    if (pEmailInput) {
                        pEmailInput.classList.add('border-red-500');
                        if (!firstInvalidInput) firstInvalidInput = pEmailInput;
                    }
                } else if (pEmail === studentEmail) {
                    errors.push(`Parent row #${idx + 1}: Parent email cannot be the same as the student email.`);
                    if (pEmailInput) {
                        pEmailInput.classList.add('border-red-500');
                        if (!firstInvalidInput) firstInvalidInput = pEmailInput;
                    }
                } else if (parentEmails.includes(pEmail)) {
                    errors.push(`Duplicate parent email "${pEmail}". Each parent must have a unique email.`);
                    if (pEmailInput) {
                        pEmailInput.classList.add('border-red-500');
                        if (!firstInvalidInput) firstInvalidInput = pEmailInput;
                    }
                } else {
                    parentEmails.push(pEmail);
                }
            }
        });

        if (errors.length > 0) {
            errors.forEach(err => {
                const li = document.createElement('li');
                li.textContent = err;
                errorList.appendChild(li);
            });
            errorBox.classList.remove('hidden');
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
            if (firstInvalidInput) {
                firstInvalidInput.focus();
            }
            if (window.toastr) window.toastr.error(errors[0]);
            return;
        }

        submitBtn.disabled = true;
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Saving...';

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
                    window.location.href = '{{ route('admin.students.index') }}';
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

