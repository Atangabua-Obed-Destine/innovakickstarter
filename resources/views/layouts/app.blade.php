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
    <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚀</text></svg>">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Additional Head Content -->
    @stack('head')
</head>
<body class="antialiased" x-data="{ sidebarOpen: false }">
    <!-- Sidebar Overlay (Mobile) -->
    <div 
        x-show="sidebarOpen" 
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-dark-950/80 backdrop-blur-sm z-30 lg:hidden"
    ></div>

    <!-- Sidebar -->
    <aside 
        class="sidebar transition-transform duration-300 lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-600 to-accent-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-dark-100">IKS</h1>
                    <p class="text-xs text-dark-400">Career Capital</p>
                </div>
            </a>
        </div>

        <!-- Sidebar Content -->
        <nav class="sidebar-content">
            @include('partials.navigation')
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="flex items-center gap-3">
                <div class="avatar avatar-md">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-dark-100 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-dark-400 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 text-dark-400 hover:text-dark-100 hover:bg-dark-800 rounded-lg transition-colors" title="Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Top Navbar -->
    <header class="navbar">
        <!-- Mobile Menu Button -->
        <button 
            @click="sidebarOpen = !sidebarOpen" 
            class="lg:hidden p-2 text-dark-400 hover:text-dark-100 hover:bg-dark-800 rounded-lg transition-colors"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Page Title -->
        <div class="flex items-center gap-4">
            <h2 class="text-lg font-semibold text-dark-100">{{ $header ?? 'Dashboard' }}</h2>
        </div>

        <!-- Right Side -->
        <div class="flex items-center gap-3">
            <!-- Global Track Switcher (Fellow only) -->
            @include('partials.track-switcher')

            <!-- Search -->
            <div class="hidden md:block relative">
                <input 
                    type="text" 
                    placeholder="Search..." 
                    class="w-64 pl-10 pr-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-sm text-dark-100 placeholder-dark-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                >
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Notifications -->
            <div class="relative" x-data="{ showNotifications: false }" @click.away="showNotifications = false">
                <button @click="showNotifications = !showNotifications" class="relative p-2 text-dark-400 hover:text-dark-100 hover:bg-dark-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if(auth()->user()->unreadNotifications()->count() > 0)
                        <span class="absolute top-1 right-1 w-2 h-2 bg-primary-500 rounded-full animate-pulse"></span>
                    @endif
                </button>

                <div x-show="showNotifications" style="display: none;" class="fixed left-4 right-4 top-16 sm:absolute sm:left-auto sm:right-0 sm:top-12 sm:w-80 max-w-sm mx-auto bg-dark-800 rounded-xl shadow-lg py-2 border border-dark-700 z-50 origin-top-right transition-all transform" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                    <div class="px-4 py-2 border-b border-dark-700 flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-white">Notifications</h3>
                        @if(auth()->user()->unreadNotifications()->count() > 0 && Route::has('notifications.mark-all-read'))
                            <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs text-primary-400 hover:text-primary-300">Mark all read</button>
                            </form>
                        @endif
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        @forelse(auth()->user()->notifications()->latest()->limit(5)->get() as $notification)
                            <div class="px-4 py-3 border-b border-dark-700/50 hover:bg-dark-700/30 transition-colors {{ $notification->read_at ? 'opacity-70' : 'bg-dark-700/10' }}">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-{{ $notification->color ?? 'primary' }}-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-{{ $notification->color ?? 'primary' }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-white truncate">{{ $notification->title }}</p>
                                        <p class="text-sm text-dark-100 leading-tight mt-0.5 line-clamp-2">
                                            @if($notification->action_url)
                                                <a href="{{ $notification->action_url }}" class="hover:text-primary-400">{{ $notification->message ?? 'New notification' }}</a>
                                            @else
                                                {{ $notification->message ?? 'New notification' }}
                                            @endif
                                        </p>
                                        <p class="text-xs text-dark-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-8 flex flex-col items-center justify-center">
                                <div class="w-12 h-12 rounded-full bg-dark-700/50 flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </div>
                                <p class="text-sm text-dark-400">You're all caught up!</p>
                            </div>
                        @endforelse
                    </div>
                    @if(auth()->user()->notifications()->count() > 5 && Route::has('notifications.index'))
                        <div class="px-4 py-2 border-t border-dark-700 text-center">
                            <a href="{{ route('notifications.index') }}" class="text-xs text-primary-400 hover:text-primary-300 font-medium">View all notifications</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            @if(auth()->user()->hasRole('fellow'))
                <a href="{{ route('activities.create') }}" class="btn btn-primary btn-sm hidden lg:inline-flex whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Log Activity
                </a>
            @endif
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Alerts -->
        @include('partials.alerts')

        <!-- Page Content -->
        <div class="p-4 sm:p-6 pb-36 lg:pb-6">
            @yield('content')
        </div>
    </main>

    <!-- Mobile Bottom Navigation -->
    @if(auth()->user()->hasRole('fellow'))
    <nav class="fixed bottom-0 left-0 right-0 bg-dark-900/90 backdrop-blur-xl border-t border-dark-700/50 lg:hidden z-40 pb-safe shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
        <div class="flex items-center justify-around py-1.5 px-1">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-0.5 p-1.5 text-dark-400 hover:text-primary-400 active:scale-95 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-[9px] font-medium tracking-wide">Home</span>
            </a>
            <a href="{{ route('activities.index') }}" class="flex flex-col items-center gap-0.5 p-1.5 text-dark-400 hover:text-primary-400 active:scale-95 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span class="text-[9px] font-medium tracking-wide">Activities</span>
            </a>
            <a href="{{ route('activities.create') }}" class="flex flex-col items-center gap-0.5 p-1 active:scale-90 transition-transform duration-300 relative z-50">
                <div class="w-12 h-12 -mt-6 bg-gradient-to-br from-primary-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg shadow-primary-500/40 border-4 border-dark-900">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <span class="text-[9px] font-semibold text-primary-400 tracking-wide mt-0.5">Log</span>
            </a>
            <a href="{{ route('interviews.index') }}" class="flex flex-col items-center gap-0.5 p-1.5 text-dark-400 hover:text-primary-400 active:scale-95 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <span class="text-[9px] font-medium tracking-wide">Interviews</span>
            </a>
            <a href="{{ route('profile.show') }}" class="flex flex-col items-center gap-0.5 p-1.5 text-dark-400 hover:text-primary-400 active:scale-95 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-[9px] font-medium tracking-wide">Profile</span>
            </a>
        </div>
    </nav>
    @endif

    <!-- Alpine.js CDN (for interactivity) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>
