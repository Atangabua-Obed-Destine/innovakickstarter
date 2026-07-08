<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $user->name }} — Career Capital Profile</title>
    <meta name="description" content="{{ Str::limit($user->bio ?? $user->name . ' is a tech professional on I-NNOVA Career Capital Platform.', 160) }}">
    <meta property="og:title" content="{{ $user->name }} — Career Capital Profile">
    <meta property="og:description" content="{{ Str::limit($user->bio ?? '', 160) }}">
    <meta property="og:type" content="profile">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-dark-950 text-dark-100">
    <!-- Navigation Bar -->
    <nav class="border-b border-dark-800 bg-dark-900/80 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-white font-bold">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-600 to-accent-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                IKS Career Capital
            </a>
            <a href="{{ route('public.talent.directory') }}" class="text-dark-400 hover:text-white text-sm transition-colors">← Back to Directory</a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-8 space-y-8">
        <!-- Profile Header -->
        <div class="card p-8">
            <div class="flex flex-col md:flex-row gap-6 items-start">
                <!-- Avatar -->
                <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-primary-600 to-accent-600 flex items-center justify-center text-3xl font-bold text-white flex-shrink-0">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-2xl">
                    @else
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    @endif
                </div>
                
                <!-- Info -->
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3 mb-2">
                        <h1 class="text-3xl font-bold text-white">{{ $user->name }}</h1>
                        @if($user->open_to_opportunities)
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-600/20 text-green-400">Open to Opportunities</span>
                        @endif
                    </div>
                    
                    @if($primaryTrack?->track)
                        <p class="text-primary-400 font-medium">{{ $primaryTrack->track->name }}</p>
                    @endif
                    
                    <div class="flex flex-wrap items-center gap-4 mt-3 text-dark-400 text-sm">
                        @if($user->location)
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $user->location }}
                        </span>
                        @endif
                        @if($user->availability)
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ ucfirst(str_replace('-', ' ', $user->availability)) }}
                        </span>
                        @endif
                    </div>
                    
                    @if($user->bio)
                        <p class="mt-4 text-dark-300 leading-relaxed">{{ $user->bio }}</p>
                    @endif

                    <!-- Social Links -->
                    <div class="flex items-center gap-3 mt-4">
                        @if($user->linkedin_url)
                        <a href="{{ $user->linkedin_url }}" target="_blank" rel="noopener" class="p-2 bg-dark-800 hover:bg-dark-700 rounded-lg text-dark-400 hover:text-blue-400 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        @endif
                        @if($user->github_url)
                        <a href="{{ $user->github_url }}" target="_blank" rel="noopener" class="p-2 bg-dark-800 hover:bg-dark-700 rounded-lg text-dark-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                        </a>
                        @endif
                        @if($user->portfolio_url)
                        <a href="{{ $user->portfolio_url }}" target="_blank" rel="noopener" class="p-2 bg-dark-800 hover:bg-dark-700 rounded-lg text-dark-400 hover:text-primary-400 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Career Capital Score Badge -->
                @if($primaryTrack)
                <div class="text-center p-6 bg-dark-800 rounded-2xl flex-shrink-0">
                    <p class="text-dark-500 text-xs uppercase tracking-wider mb-1">Career Capital</p>
                    <p class="text-4xl font-bold text-primary-400">{{ number_format($primaryTrack->score ?? 0, 1) }}</p>
                    <p class="text-dark-500 text-sm mt-1">{{ ucfirst($primaryTrack->tier ?? 'rookie') }} tier</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Activities -->
        @if($user->activities->count() > 0)
        <div class="card p-6">
            <h2 class="text-xl font-semibold text-white mb-4">Recent Work</h2>
            <div class="grid md:grid-cols-2 gap-4">
                @foreach($user->activities as $activity)
                <div class="p-4 bg-dark-800 rounded-xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-white font-medium">{{ $activity->title }}</h3>
                            <p class="text-dark-400 text-sm mt-1 line-clamp-2">{{ $activity->description }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full bg-primary-600/20 text-primary-400 whitespace-nowrap">{{ ucfirst($activity->category) }}</span>
                    </div>
                    <p class="text-dark-500 text-xs mt-3">{{ $activity->created_at->diffForHumans() }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="border-t border-dark-800 mt-16 py-8 text-center text-dark-500 text-sm">
        <p>&copy; {{ date('Y') }} I-NNOVA Career Capital Platform. All rights reserved.</p>
    </footer>
</body>
</html>
