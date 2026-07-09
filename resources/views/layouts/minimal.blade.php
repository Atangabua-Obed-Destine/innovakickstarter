<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'IKS Career Capital')) - Career Capital Platform</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚀</text></svg>">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-dark-950 min-h-screen">
    <!-- Impersonation Banner -->
    @if(session()->has('impersonate_original_id'))
        <div class="bg-primary-600 text-white px-4 py-2 flex items-center justify-between z-50 relative">
            <div class="flex items-center gap-2 text-sm font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                You are currently impersonating {{ auth()->user()->name }}
            </div>
            <form action="{{ route('leave-impersonation') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="text-sm font-bold bg-white text-primary-600 px-3 py-1 rounded-md hover:bg-primary-50 transition-colors">
                    Leave Impersonation
                </button>
            </form>
        </div>
    @endif
    <!-- Background Pattern -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-600/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-accent-600/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Main Content -->
    <main class="relative z-10">
        @yield('content')
    </main>

    <!-- Alpine.js for interactivity -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
