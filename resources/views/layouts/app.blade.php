<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Home') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased font-sans">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <div class="flex items-center gap-4 sm:gap-8">
                <a href="{{ route('admin.dashboard.index') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/school-logo.webp') }}" alt="{{ config('app.name') }}" class="h-10 w-auto rounded-lg shadow-xs">
                </a>

                @canany([
                    'teachers.view',
                    'students.view',
                    'announcements.view',
                    'roles.manage',
                ])
                    <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
                        <a href="{{ route('admin.dashboard.index') }}" class="rounded-lg px-3 py-1.5 transition-colors {{ request()->routeIs('admin.dashboard.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Dashboard</a>
                        @can('teachers.view')
                            <a href="{{ route('admin.teachers.index') }}" class="rounded-lg px-3 py-1.5 transition-colors {{ request()->routeIs('admin.teachers.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Teachers</a>
                        @endcan
                        @can('students.view')
                            <a href="{{ route('admin.students.index') }}" class="rounded-lg px-3 py-1.5 transition-colors {{ request()->routeIs('admin.students.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Students</a>
                        @endcan
                        @can('announcements.view')
                            <a href="{{ route('admin.announcements.index') }}" class="rounded-lg px-3 py-1.5 transition-colors {{ request()->routeIs('admin.announcements.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Announcements</a>
                        @endcan
                        @can('roles.manage')
                            <a href="{{ route('admin.roles.index') }}" class="rounded-lg px-3 py-1.5 transition-colors {{ request()->routeIs('admin.roles.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">Roles & Permissions</a>
                        @endcan
                    </nav>
                @endcanany
            </div>

            <div class="flex items-center gap-2">
                <!-- Mobile Navigation Toggle Button -->
                @canany([
                    'teachers.view',
                    'students.view',
                    'announcements.view',
                    'roles.manage',
                ])
                    <button
                        type="button"
                        onclick="toggleMobileNav()"
                        class="md:hidden inline-flex items-center justify-center rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        aria-label="Toggle navigation menu"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                @endcanany

                <div class="relative">
                    <button
                        type="button"
                        id="user-menu-button"
                        aria-expanded="false"
                        aria-haspopup="true"
                        onclick="toggleUserMenu()"
                        class="group flex items-center gap-2 rounded-full p-1 text-sm font-medium transition-colors hover:ring-4 hover:ring-slate-100 focus:outline-none focus:ring-4 focus:ring-indigo-100"
                    >
                        @if (auth()->user()->avatarUrl())
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="h-8 w-8 sm:h-9 sm:w-9 rounded-full object-cover ring-2 ring-slate-200">
                        @else
                            <div class="flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 font-bold text-white shadow-sm text-xs sm:text-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <span class="hidden font-medium text-slate-700 md:inline-block pr-1 group-hover:text-slate-900">{{ auth()->user()->name }}</span>
                        <svg class="h-4 w-4 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div
                        id="user-menu-dropdown"
                        class="hidden absolute right-0 mt-2 w-52 origin-top-right rounded-2xl border border-slate-200/80 bg-white p-1.5 shadow-xl transition-all z-50"
                        role="menu"
                        aria-orientation="vertical"
                        aria-labelledby="user-menu-button"
                    >
                        <div class="border-b border-slate-100 px-3 py-2">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                        </div>

                        <div class="py-1">
                            <a
                                href="{{ route('profile.show') }}"
                                class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition-colors"
                                role="menuitem"
                            >
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                View Profile
                            </a>
                        </div>

                        <div class="border-t border-slate-100 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors"
                                    role="menuitem"
                                >
                                    <svg class="h-4 w-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu Dropdown -->
        @canany([
            'teachers.view',
            'students.view',
            'announcements.view',
            'roles.manage',
        ])
            <div id="mobile-nav" class="hidden md:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-1">
                <a href="{{ route('admin.dashboard.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.dashboard.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">Dashboard</a>
                @can('teachers.view')
                    <a href="{{ route('admin.teachers.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.teachers.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">Teachers</a>
                @endcan
                @can('students.view')
                    <a href="{{ route('admin.students.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.students.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">Students</a>
                @endcan
                @can('announcements.view')
                    <a href="{{ route('admin.announcements.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.announcements.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">Announcements</a>
                @endcan
                @can('roles.manage')
                    <a href="{{ route('admin.roles.index') }}" class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.roles.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">Roles & Permissions</a>
                @endcan
            </div>
        @endcanany
    </header>

    <main class="mx-auto flex max-w-7xl w-full flex-col gap-6 px-4 py-6 sm:px-6 sm:py-8">
        @yield('content')
    </main>

    <script>
        function toggleUserMenu() {
            const menu = document.getElementById('user-menu-dropdown');
            menu.classList.toggle('hidden');
        }

        function toggleMobileNav() {
            const nav = document.getElementById('mobile-nav');
            if (nav) {
                nav.classList.toggle('hidden');
            }
        }

        window.addEventListener('click', function(e) {
            const button = document.getElementById('user-menu-button');
            const menu = document.getElementById('user-menu-dropdown');
            if (button && menu && !button.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            @if (session('status'))
                if (window.toastr) {
                    window.toastr.success(@json(session('status')));
                }
            @endif
            @if (session('error'))
                if (window.toastr) {
                    window.toastr.error(@json(session('error')));
                }
            @endif
        });
    </script>
    @stack('scripts')
</body>
</html>
