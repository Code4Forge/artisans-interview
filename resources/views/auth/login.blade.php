@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
    <div class="flex flex-col gap-8 rounded-2xl bg-white p-8 shadow-2xl">
        <div class="flex flex-col items-center gap-4 text-center">
            <img src="{{ asset('images/school-logo.webp') }}" alt="{{ config('app.name') }}" class="w-40">
            <h1 class="text-2xl font-semibold text-slate-900">Sign in</h1>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4">
            @csrf

            <div class="flex flex-col gap-1">
                <label for="email" class="text-sm font-medium text-slate-700">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    class="rounded-lg border px-3 py-2.5 text-slate-900 outline-none ring-slate-900/20 focus:ring-2 @error('email') border-red-500 @else border-slate-300 @enderror"
                    required
                    autofocus
                    autocomplete="username"
                >
                @error('email')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1">
                <label for="password" class="text-sm font-medium text-slate-700">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="rounded-lg border px-3 py-2.5 text-slate-900 outline-none ring-slate-900/20 focus:ring-2 @error('password') border-red-500 @else border-slate-300 @enderror"
                    required
                    autocomplete="current-password"
                >
                @error('password')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input class="size-4 rounded border-slate-300" type="checkbox" name="remember" value="1" @checked(old('remember'))>
                Remember me
            </label>

            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 font-medium text-white hover:bg-slate-800">Sign in</button>
        </form>
    </div>
@endsection
