<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sign in') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
</head>

<body class="min-h-screen bg-slate-950 bg-cover bg-center bg-[url('/images/login-background.webp')]">
    <div class="flex min-h-screen items-center justify-center bg-slate-950/65 px-4 py-12">
        <main class="w-full max-w-md">
            @yield('content')
        </main>
    </div>
</body>

</html>
