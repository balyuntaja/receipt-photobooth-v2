<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Auth') – {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/auth.css'])
</head>
<body class="auth-page antialiased min-h-screen bg-stone-100 text-stone-900 flex flex-col">
    <header class="shrink-0 py-5 px-4">
        <div class="max-w-md mx-auto flex justify-between items-center auth-animate-fade-in delay-header">
            <a href="{{ url('/') }}" class="text-sm font-medium text-stone-500 hover:text-stone-800 transition">← Beranda</a>
            <a href="{{ route('gallery') }}" class="text-sm font-medium text-stone-500 hover:text-stone-800 transition">Gallery</a>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-8">
        @yield('content')
    </main>

    <footer class="shrink-0 py-4 text-center text-xs text-stone-400">
        &copy; {{ date('Y') }} {{ config('app.name') }}
    </footer>
</body>
</html>
