@extends('layouts.app')

@section('title', 'Add Role')

@section('content')
    <div class="flex w-full flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add New Role</h1>
                <p class="text-sm text-slate-500">Create a new user role and assign custom permissions.</p>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors">
                &larr; Back to list
            </a>
        </div>

        <!-- Inline error box for AJAX validation failures -->
        <div id="form-error-box" class="hidden border border-red-200 bg-red-50 p-3 text-xs text-red-700 rounded-md">
            <ul id="form-error-list" class="list-disc list-inside space-y-1"></ul>
        </div>

        <form id="role-create-form" method="POST" action="{{ route('admin.roles.store') }}" onsubmit="handleAjaxSubmit(event)" class="flex w-full flex-col gap-6 border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            @csrf
            @include('admin.roles._form')
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.roles.index') }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" id="submit-btn" class="rounded-md bg-indigo-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 transition-colors">
                    Create Role
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function handleAjaxSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('submit-btn');
        const errorBox = document.getElementById('form-error-box');
        const errorList = document.getElementById('form-error-list');

        const nameInput = form.querySelector('[name="name"]');

        const clientErrors = [];
        if (!nameInput.value.trim()) {
            clientErrors.push('Role name is required.');
        } else if (nameInput.value.trim().length < 2) {
            clientErrors.push('Role name must be at least 2 characters.');
        }

        if (clientErrors.length > 0) {
            errorList.innerHTML = '';
            clientErrors.forEach(msg => {
                const li = document.createElement('li');
                li.textContent = msg;
                errorList.appendChild(li);
            });
            errorBox.classList.remove('hidden');
            if (window.toastr) {
                window.toastr.error(clientErrors[0]);
            }
            return;
        }

        errorBox.classList.add('hidden');
        errorList.innerHTML = '';
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

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
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Role';

            if (result.status >= 200 && result.status < 300 && result.body.success) {
                if (window.toastr) {
                    window.toastr.success(result.body.message || 'Role created successfully.');
                }
                setTimeout(() => {
                    window.location.href = '{{ route('admin.roles.index') }}';
                }, 700);
            } else {
                const errors = result.body.errors || result.body.message || 'Validation error.';
                if (typeof errors === 'object') {
                    for (const key in errors) {
                        errors[key].forEach(msg => {
                            const li = document.createElement('li');
                            li.textContent = msg;
                            errorList.appendChild(li);
                        });
                    }
                } else {
                    const li = document.createElement('li');
                    li.textContent = errors;
                    errorList.appendChild(li);
                }
                errorBox.classList.remove('hidden');
                if (window.toastr) {
                    window.toastr.error('Please resolve the errors below.');
                }
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Role';
            const li = document.createElement('li');
            li.textContent = 'A network error occurred. Please try again.';
            errorList.appendChild(li);
            errorBox.classList.remove('hidden');
        });
    }
</script>
@endpush

