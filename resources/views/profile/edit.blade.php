@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
    <div class="flex w-full flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Profile</h1>
                <p class="text-sm text-slate-500">Update your account information, profile photo, and password.</p>
            </div>
            <a href="{{ route('profile.show') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors">
                &larr; Back to Profile
            </a>
        </div>

        <form id="profile-edit-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" onsubmit="validateProfileForm(event)" class="flex w-full flex-col gap-6 border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            @csrf
            @method('PUT')

            <div class="flex flex-wrap items-center gap-5 border-b border-slate-100 pb-6">
                @if ($user->avatarUrl())
                    <img id="avatar-preview" src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-full object-cover ring-4 ring-indigo-50 shadow-sm">
                @else
                    <div id="avatar-fallback" class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-2xl font-bold text-white shadow-inner">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <img id="avatar-preview" src="" alt="{{ $user->name }}" class="hidden h-20 w-20 rounded-full object-cover ring-4 ring-indigo-50 shadow-sm">
                @endif
                <div>
                    <label for="avatar" class="cursor-pointer inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors">
                        <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Upload Photo
                    </label>
                    <input id="avatar" name="avatar" type="file" accept="image/*" class="sr-only" onchange="previewAvatar(this)">
                    <p class="mt-1 text-xs text-slate-500">JPG, PNG, or WEBP up to 2MB.</p>
                    @error('avatar')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="flex flex-col gap-1.5">
                    <label for="name" class="text-sm font-semibold text-slate-700">Full Name <span class="text-red-500">*</span></label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $user->name) }}"
                        class="w-full rounded-md border px-3.5 py-2.5 text-sm outline-none transition-shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 @error('name') border-red-500 @else border-slate-300 @enderror"
                        required
                    >
                    @error('name')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="email" class="text-sm font-semibold text-slate-700">Email Address</label>
                    <input
                        id="email"
                        type="email"
                        value="{{ $user->email }}"
                        disabled
                        class="w-full rounded-md border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-500 cursor-not-allowed"
                    >
                    <p class="text-xs text-slate-500">Email address cannot be changed.</p>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-5">
                <h3 class="text-sm font-semibold text-slate-900">Change Password</h3>
                <p class="text-xs text-slate-500">Leave blank if you don't wish to change your password.</p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="flex flex-col gap-1.5">
                    <label for="password" class="text-sm font-semibold text-slate-700">New Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        class="w-full rounded-md border px-3.5 py-2.5 text-sm outline-none transition-shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 @error('password') border-red-500 @else border-slate-300 @enderror"
                    >
                    @error('password')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Confirm New Password</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        class="w-full rounded-md border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition-shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                    >
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('profile.show') }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="rounded-md bg-indigo-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <script>
        function validateProfileForm(e) {
            const name = document.getElementById('name').value.trim();
            const password = document.getElementById('password').value;
            const passwordConf = document.getElementById('password_confirmation').value;

            if (!name) {
                e.preventDefault();
                if (window.toastr) {
                    window.toastr.error('Full Name is required.');
                }
                return false;
            }

            if (password) {
                if (password.length < 8) {
                    e.preventDefault();
                    if (window.toastr) {
                        window.toastr.error('Password must be at least 8 characters.');
                    }
                    return false;
                }
                if (password !== passwordConf) {
                    e.preventDefault();
                    if (window.toastr) {
                        window.toastr.error('Password confirmation does not match.');
                    }
                    return false;
                }
            }
        }

        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatar-preview');
                    const fallback = document.getElementById('avatar-fallback');
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if (fallback) {
                        fallback.classList.add('hidden');
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
