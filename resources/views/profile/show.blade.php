@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="flex w-full flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">User Profile</h1>
                <p class="text-sm text-slate-500">View and manage your account information.</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 transition-colors">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                Edit Profile
            </a>
        </div>

        <div class="w-full border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="flex flex-wrap items-center gap-5 border-b border-slate-100 pb-6">
                @if ($user->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-full object-cover ring-4 ring-indigo-50 shadow-sm">
                @else
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-2xl font-bold text-white shadow-inner">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $user->email }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 pt-6 sm:grid-cols-2 md:grid-cols-3">
                <div class="border border-slate-100 bg-slate-50/50 p-4">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Full Name</span>
                    <p class="mt-1 text-base font-semibold text-slate-800">{{ $user->name }}</p>
                </div>
                <div class="border border-slate-100 bg-slate-50/50 p-4">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Email Address</span>
                    <p class="mt-1 text-base font-semibold text-slate-800 truncate">{{ $user->email }}</p>
                    <span class="mt-0.5 inline-block text-xs text-slate-400">(Email address is locked)</span>
                </div>
                <div class="border border-slate-100 bg-slate-50/50 p-4">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Member Since</span>
                    <p class="mt-1 text-base font-semibold text-slate-800">{{ $user->created_at?->format('M d, Y') ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
