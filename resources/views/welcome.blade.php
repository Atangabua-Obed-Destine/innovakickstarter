<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="IKS Career Capital Platform - Transform your learning into measurable career readiness. Join I-NNOVA CMR's flagship program.">
    
    <title>IKS Career Capital Platform | I-NNOVA CMR</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        
        .hero-gradient {
            background: radial-gradient(ellipse at top, rgba(124, 58, 237, 0.15) 0%, transparent 50%),
                        radial-gradient(ellipse at bottom right, rgba(20, 184, 166, 0.1) 0%, transparent 50%),
                        radial-gradient(ellipse at bottom left, rgba(30, 64, 175, 0.1) 0%, transparent 50%);
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #7C3AED 0%, #1E40AF 50%, #14B8A6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .card-shine {
            position: relative;
            overflow: hidden;
        }
        
        .card-shine::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .card-shine:hover::before {
            left: 100%;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }
        
        @keyframes float-delayed {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(-2deg); }
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(124, 58, 237, 0.3); }
            50% { box-shadow: 0 0 40px rgba(124, 58, 237, 0.6); }
        }
        
        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @keyframes counter {
            from { --num: 0; }
            to { --num: var(--target); }
        }
        
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float-delayed 5s ease-in-out infinite 0.5s; }
        .animate-pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }
        
        .gradient-border {
            position: relative;
            background: #1E293B;
            border-radius: 1rem;
        }
        
        .gradient-border::before {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 1rem;
            background: linear-gradient(135deg, #7C3AED, #1E40AF, #14B8A6);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .gradient-border:hover::before {
            opacity: 1;
        }
        
        .score-ring {
            background: conic-gradient(
                #7C3AED 0deg,
                #1E40AF 120deg,
                #14B8A6 240deg,
                #7C3AED 360deg
            );
            animation: gradient-shift 4s linear infinite;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: rgba(124, 58, 237, 0.5);
            animation: float 8s ease-in-out infinite;
        }
    </style>
</head>
<body class="antialiased bg-[#0F172A] text-gray-100 font-['Inter']">
    <!-- Navigation -->
    <nav x-data="{ mobileMenu: false, scrolled: false }" 
         x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 50 })"
         :class="scrolled ? 'bg-[#0F172A]/95 backdrop-blur-lg shadow-xl' : 'bg-transparent'"
         class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="/" class="flex items-center space-x-3 group">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-600 via-blue-600 to-teal-500 flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white font-bold text-xl">IKS</span>
                    </div>
                    <div class="hidden sm:block">
                        <span class="text-xl font-bold text-white">Career Capital</span>
                        <span class="block text-xs text-gray-400">by I-NNOVA CMR</span>
                    </div>
                </a>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-300 hover:text-white transition-colors duration-200">Features</a>
                    <a href="#tracks" class="text-gray-300 hover:text-white transition-colors duration-200">Career Tracks</a>
                    <a href="#how-it-works" class="text-gray-300 hover:text-white transition-colors duration-200">How It Works</a>
                </div>
                
                <!-- Auth Buttons -->
                <div class="hidden md:flex items-center space-x-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-6 py-2.5 text-white bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg font-medium hover:opacity-90 transition-opacity">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition-colors duration-200 font-medium">
                            Sign In
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-6 py-2.5 text-white bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg font-medium hover:opacity-90 transition-opacity shadow-lg shadow-purple-500/25">
                                Get Started
                            </a>
                        @endif
                    @endauth
                </div>
                
                <!-- Mobile Menu Button -->
                <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors">
                    <svg x-show="!mobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileMenu" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div x-show="mobileMenu" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4"
                 class="md:hidden py-4 border-t border-gray-800">
                <div class="flex flex-col space-y-4">
                    <a href="#features" class="text-gray-300 hover:text-white transition-colors px-4 py-2">Features</a>
                    <a href="#tracks" class="text-gray-300 hover:text-white transition-colors px-4 py-2">Career Tracks</a>
                    <a href="#how-it-works" class="text-gray-300 hover:text-white transition-colors px-4 py-2">How It Works</a>
                    <div class="pt-4 border-t border-gray-800 px-4 space-y-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="block w-full px-6 py-2.5 text-center text-white bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg font-medium">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="block text-center text-gray-300 hover:text-white py-2">Sign In</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="block w-full px-6 py-2.5 text-center text-white bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg font-medium">
                                    Get Started
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center hero-gradient overflow-hidden pt-20">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <!-- Floating Orbs -->
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl animate-float"></div>
            <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-blue-600/20 rounded-full blur-3xl animate-float-delayed"></div>
            <div class="absolute top-1/2 right-1/3 w-64 h-64 bg-teal-500/15 rounded-full blur-3xl animate-float"></div>
            
            <!-- Grid Pattern -->
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0wIDBoNjB2NjBIMHoiLz48cGF0aCBkPSJNMzAgMzBtLTEgMGExIDEgMCAxIDAgMiAwYTEgMSAwIDEgMCAtMiAwIiBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMDUpIi8+PC9nPjwvc3ZnPg==')] opacity-40"></div>
            
            <!-- Particles -->
            <div class="particle" style="top: 20%; left: 10%; animation-delay: 0s;"></div>
            <div class="particle" style="top: 60%; left: 80%; animation-delay: 1s;"></div>
            <div class="particle" style="top: 80%; left: 30%; animation-delay: 2s;"></div>
            <div class="particle" style="top: 40%; left: 60%; animation-delay: 3s;"></div>
            <div class="particle" style="top: 10%; left: 70%; animation-delay: 4s;"></div>
        </div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-purple-600/20 border border-purple-500/30 text-purple-300 text-sm font-medium mb-6">
                        <span class="w-2 h-2 bg-purple-400 rounded-full mr-2 animate-pulse"></span>
                        {{ $content['hero_badge'] ?? 'I-NNOVA CMR Kickstarter Program' }}
                    </div>
                    
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                        {{ $content['hero_title_line1'] ?? 'Transform Your Learning Into' }}
                        <span class="text-gradient block mt-2">{{ $content['hero_title_line2'] ?? 'Career Capital' }}</span>
                    </h1>
                    
                    <p class="text-lg sm:text-xl text-gray-400 mb-8 max-w-xl mx-auto lg:mx-0">
                        {{ $content['hero_subtitle'] ?? 'Build measurable career readiness through structured learning, real-world challenges, and mock interviews. Track your progress with our revolutionary Career Capital Score.' }}
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mb-12">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="group px-8 py-4 bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl font-semibold text-white shadow-2xl shadow-purple-500/30 hover:shadow-purple-500/50 transition-all duration-300 flex items-center justify-center space-x-2">
                                <span>{{ $content['hero_cta_primary'] ?? 'Start Your Journey' }}</span>
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                        @endif
                        <a href="#how-it-works" class="px-8 py-4 border border-gray-700 rounded-xl font-semibold text-white hover:bg-gray-800/50 transition-all duration-300 flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $content['hero_cta_secondary'] ?? 'See How It Works' }}</span>
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-8">
                        <div class="text-center lg:text-left">
                            <div class="text-3xl sm:text-4xl font-bold text-white mb-1" x-data="{ count: 0 }" x-init="setTimeout(() => { let i = setInterval(() => { count++; if(count >= {{ intval($stats['fellows_count'] ?? 50) }}) clearInterval(i) }, 30) }, 500)">
                                <span x-text="count"></span>+
                            </div>
                            <div class="text-sm text-gray-500">{{ $content['stat_fellows_label'] ?? 'Fellows Trained' }}</div>
                        </div>
                        <div class="text-center lg:text-left">
                            <div class="text-3xl sm:text-4xl font-bold text-white mb-1" x-data="{ count: 0 }" x-init="setTimeout(() => { let i = setInterval(() => { count++; if(count >= {{ intval($stats['placement_rate'] ?? 65) }}) clearInterval(i) }, 30) }, 800)">
                                <span x-text="count"></span>%
                            </div>
                            <div class="text-sm text-gray-500">{{ $content['stat_placement_label'] ?? 'Placement Rate' }}</div>
                        </div>
                        <div class="text-center lg:text-left">
                            <div class="text-3xl sm:text-4xl font-bold text-white mb-1" x-data="{ count: 0 }" x-init="setTimeout(() => { let i = setInterval(() => { count++; if(count >= {{ intval($stats['tracks_count'] ?? 6) }}) clearInterval(i) }, 100) }, 1100)">
                                <span x-text="count"></span>
                            </div>
                            <div class="text-sm text-gray-500">{{ $content['stat_tracks_label'] ?? 'Career Tracks' }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Content - Score Circle Visualization -->
                <div class="relative flex justify-center lg:justify-end">
                    <div class="relative" x-data="{ score: 0, showDetails: false }" x-init="setTimeout(() => { let i = setInterval(() => { score += 2; if(score >= 78) { score = 78; clearInterval(i); showDetails = true; } }, 40) }, 1000)">
                        <!-- Main Score Circle -->
                        <div class="relative w-80 h-80 sm:w-96 sm:h-96">
                            <!-- Outer Glow Ring -->
                            <div class="absolute inset-0 rounded-full score-ring opacity-20 blur-xl"></div>
                            
                            <!-- Main Circle Background -->
                            <div class="absolute inset-4 rounded-full bg-[#1E293B]/80 backdrop-blur-xl border border-gray-700/50"></div>
                            
                            <!-- Progress Ring -->
                            <svg class="absolute inset-0 w-full h-full -rotate-90">
                                <circle 
                                    cx="50%" 
                                    cy="50%" 
                                    r="45%" 
                                    fill="none" 
                                    stroke="#334155" 
                                    stroke-width="8"
                                />
                                <circle 
                                    cx="50%" 
                                    cy="50%" 
                                    r="45%" 
                                    fill="none" 
                                    stroke="url(#gradient)" 
                                    stroke-width="8"
                                    stroke-linecap="round"
                                    :stroke-dasharray="`${score * 2.83} 283`"
                                    class="transition-all duration-300"
                                />
                                <defs>
                                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#7C3AED"/>
                                        <stop offset="50%" stop-color="#1E40AF"/>
                                        <stop offset="100%" stop-color="#14B8A6"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                            
                            <!-- Center Content -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <div class="text-sm text-gray-400 mb-2">Career Capital Score</div>
                                <div class="text-6xl sm:text-7xl font-bold text-white mb-2">
                                    <span x-text="score"></span><span class="text-3xl text-gray-400">%</span>
                                </div>
                                <div class="px-4 py-1.5 rounded-full bg-purple-600/20 border border-purple-500/30">
                                    <span class="text-purple-300 text-sm font-medium">Advanced Level</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating Stat Cards -->
                        <div x-show="showDetails" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                             class="absolute -top-4 -left-8 px-4 py-3 bg-[#1E293B]/90 backdrop-blur-lg rounded-xl border border-gray-700/50 shadow-xl animate-float">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-purple-600/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400">Mastery Level</div>
                                    <div class="text-white font-semibold">Expert</div>
                                </div>
                            </div>
                        </div>
                        
                        <div x-show="showDetails" x-transition:enter="transition ease-out duration-500 delay-150" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                             class="absolute -bottom-4 -right-8 px-4 py-3 bg-[#1E293B]/90 backdrop-blur-lg rounded-xl border border-gray-700/50 shadow-xl animate-float-delayed">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-teal-600/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400">This Week</div>
                                    <div class="text-teal-400 font-semibold">+12%</div>
                                </div>
                            </div>
                        </div>
                        
                        <div x-show="showDetails" x-transition:enter="transition ease-out duration-500 delay-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                             class="absolute top-1/2 -right-12 transform -translate-y-1/2 px-4 py-3 bg-[#1E293B]/90 backdrop-blur-lg rounded-xl border border-gray-700/50 shadow-xl animate-float">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-600/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400">Activities</div>
                                    <div class="text-white font-semibold">24 Done</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
            <a href="#features" class="flex flex-col items-center text-gray-400 hover:text-white transition-colors">
                <span class="text-sm mb-2">Scroll to explore</span>
                <div class="w-6 h-10 border-2 border-gray-600 rounded-full flex justify-center pt-2">
                    <div class="w-1.5 h-3 bg-gray-400 rounded-full animate-bounce"></div>
                </div>
            </a>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 relative">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-purple-900/5 to-transparent"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                    The Four Pillars of
                    <span class="text-gradient">Career Capital</span>
                </h2>
                <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                    Our comprehensive approach measures your career readiness across four essential dimensions, 
                    ensuring you're fully prepared for professional success.
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Pillar 1: Knowledge -->
                <div class="group gradient-border p-6 rounded-2xl card-shine hover:scale-105 transition-transform duration-300">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-purple-600 to-purple-800 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">Knowledge</h3>
                    <p class="text-gray-400 text-sm mb-4">
                        Master technical concepts and industry knowledge through structured learning modules and assessments.
                    </p>
                    <div class="flex items-center text-purple-400 text-sm font-medium">
                        <span>25% of your score</span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Pillar 2: Experience -->
                <div class="group gradient-border p-6 rounded-2xl card-shine hover:scale-105 transition-transform duration-300">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">Experience</h3>
                    <p class="text-gray-400 text-sm mb-4">
                        Build practical skills through real-world challenges, projects, and hands-on activities.
                    </p>
                    <div class="flex items-center text-blue-400 text-sm font-medium">
                        <span>25% of your score</span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Pillar 3: Network -->
                <div class="group gradient-border p-6 rounded-2xl card-shine hover:scale-105 transition-transform duration-300">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-teal-600 to-teal-800 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">Network</h3>
                    <p class="text-gray-400 text-sm mb-4">
                        Connect with industry professionals, mentors, and fellow learners to expand your opportunities.
                    </p>
                    <div class="flex items-center text-teal-400 text-sm font-medium">
                        <span>25% of your score</span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Pillar 4: Recognition -->
                <div class="group gradient-border p-6 rounded-2xl card-shine hover:scale-105 transition-transform duration-300">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-3">Recognition</h3>
                    <p class="text-gray-400 text-sm mb-4">
                        Earn certifications, badges, and achievements that validate your skills to employers.
                    </p>
                    <div class="flex items-center text-amber-400 text-sm font-medium">
                        <span>25% of your score</span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Career Tracks Section -->
    <section id="tracks" class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-[#0B1120]"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                    Choose Your
                    <span class="text-gradient">Career Track</span>
                </h2>
                <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                    Specialized learning paths designed to prepare you for in-demand careers. 
                    Each track includes tailored activities, assessments, and interview preparation.
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6" x-data="{ activeTrack: null }">
                <!-- Track 1: Software Development -->
                <div @mouseenter="activeTrack = 'software'" @mouseleave="activeTrack = null"
                     class="relative group bg-[#1E293B]/60 rounded-2xl p-6 border border-gray-700/50 hover:border-purple-500/50 transition-all duration-300 cursor-pointer overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-purple-600/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                            </div>
                            <span class="text-xs text-purple-400 font-medium px-3 py-1 bg-purple-500/10 rounded-full">Popular</span>
                        </div>
                        
                        <h3 class="text-xl font-semibold text-white mb-2">Software Development</h3>
                        <p class="text-gray-400 text-sm mb-4">Full-stack development, algorithms, system design, and modern frameworks.</p>
                        
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">12 Modules • 48 Activities</span>
                            <svg class="w-5 h-5 text-purple-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Track 2: Data Science -->
                <div @mouseenter="activeTrack = 'data'" @mouseleave="activeTrack = null"
                     class="relative group bg-[#1E293B]/60 rounded-2xl p-6 border border-gray-700/50 hover:border-blue-500/50 transition-all duration-300 cursor-pointer overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-600/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <span class="text-xs text-blue-400 font-medium px-3 py-1 bg-blue-500/10 rounded-full">High Demand</span>
                        </div>
                        
                        <h3 class="text-xl font-semibold text-white mb-2">Data Science & AI</h3>
                        <p class="text-gray-400 text-sm mb-4">Machine learning, analytics, Python, and data visualization techniques.</p>
                        
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">10 Modules • 42 Activities</span>
                            <svg class="w-5 h-5 text-blue-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Track 3: Product Management -->
                <div @mouseenter="activeTrack = 'product'" @mouseleave="activeTrack = null"
                     class="relative group bg-[#1E293B]/60 rounded-2xl p-6 border border-gray-700/50 hover:border-teal-500/50 transition-all duration-300 cursor-pointer overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-teal-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-teal-600/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </div>
                        </div>
                        
                        <h3 class="text-xl font-semibold text-white mb-2">Product Management</h3>
                        <p class="text-gray-400 text-sm mb-4">Strategy, roadmapping, user research, and agile methodologies.</p>
                        
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">8 Modules • 36 Activities</span>
                            <svg class="w-5 h-5 text-teal-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Track 4: UX/UI Design -->
                <div @mouseenter="activeTrack = 'design'" @mouseleave="activeTrack = null"
                     class="relative group bg-[#1E293B]/60 rounded-2xl p-6 border border-gray-700/50 hover:border-pink-500/50 transition-all duration-300 cursor-pointer overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-pink-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-pink-600/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                </svg>
                            </div>
                        </div>
                        
                        <h3 class="text-xl font-semibold text-white mb-2">UX/UI Design</h3>
                        <p class="text-gray-400 text-sm mb-4">User experience, interface design, prototyping, and design systems.</p>
                        
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">9 Modules • 38 Activities</span>
                            <svg class="w-5 h-5 text-pink-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Track 5: Business Analysis -->
                <div @mouseenter="activeTrack = 'business'" @mouseleave="activeTrack = null"
                     class="relative group bg-[#1E293B]/60 rounded-2xl p-6 border border-gray-700/50 hover:border-amber-500/50 transition-all duration-300 cursor-pointer overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-600/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                        </div>
                        
                        <h3 class="text-xl font-semibold text-white mb-2">Business Analysis</h3>
                        <p class="text-gray-400 text-sm mb-4">Requirements gathering, process improvement, and stakeholder management.</p>
                        
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">7 Modules • 32 Activities</span>
                            <svg class="w-5 h-5 text-amber-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Track 6: Digital Marketing -->
                <div @mouseenter="activeTrack = 'marketing'" @mouseleave="activeTrack = null"
                     class="relative group bg-[#1E293B]/60 rounded-2xl p-6 border border-gray-700/50 hover:border-green-500/50 transition-all duration-300 cursor-pointer overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-green-600/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                </svg>
                            </div>
                        </div>
                        
                        <h3 class="text-xl font-semibold text-white mb-2">Digital Marketing</h3>
                        <p class="text-gray-400 text-sm mb-4">SEO, social media, content strategy, and growth marketing techniques.</p>
                        
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">8 Modules • 35 Activities</span>
                            <svg class="w-5 h-5 text-green-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl font-semibold text-white shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 transition-all duration-300">
                    <span>Explore All 12 Tracks</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-24 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                    How
                    <span class="text-gradient">It Works</span>
                </h2>
                <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                    Your journey from learner to career-ready professional in four simple steps.
                </p>
            </div>
            
            <div class="relative">
                <!-- Connection Line -->
                <div class="hidden lg:block absolute top-1/2 left-0 right-0 h-0.5 bg-gradient-to-r from-purple-600 via-blue-600 to-teal-500 transform -translate-y-1/2"></div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Step 1 -->
                    <div class="relative text-center group">
                        <div class="relative z-10 mx-auto w-20 h-20 rounded-full bg-gradient-to-br from-purple-600 to-purple-800 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-xl shadow-purple-500/30">
                            <span class="text-2xl font-bold text-white">1</span>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-3">Choose Your Track</h3>
                        <p class="text-gray-400 text-sm">Select from 12 specialized career paths based on your interests and goals.</p>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="relative text-center group">
                        <div class="relative z-10 mx-auto w-20 h-20 rounded-full bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-xl shadow-blue-500/30">
                            <span class="text-2xl font-bold text-white">2</span>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-3">Complete Activities</h3>
                        <p class="text-gray-400 text-sm">Engage with learning modules, challenges, and real-world projects.</p>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="relative text-center group">
                        <div class="relative z-10 mx-auto w-20 h-20 rounded-full bg-gradient-to-br from-teal-600 to-teal-800 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-xl shadow-teal-500/30">
                            <span class="text-2xl font-bold text-white">3</span>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-3">Practice Interviews</h3>
                        <p class="text-gray-400 text-sm">Build confidence with mock interviews and personalized feedback.</p>
                    </div>
                    
                    <!-- Step 4 -->
                    <div class="relative text-center group">
                        <div class="relative z-10 mx-auto w-20 h-20 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-xl shadow-amber-500/30">
                            <span class="text-2xl font-bold text-white">4</span>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-3">Get Discovered</h3>
                        <p class="text-gray-400 text-sm">Showcase your Career Capital to recruiters in our talent marketplace.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mock Interview Feature Section -->
    <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-purple-900/20 to-blue-900/20"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-teal-600/20 border border-teal-500/30 text-teal-300 text-sm font-medium mb-6">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Mock Interview System
                    </div>
                    
                    <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">
                        Practice Makes
                        <span class="text-gradient">Perfect</span>
                    </h2>
                    
                    <p class="text-gray-400 text-lg mb-8">
                        Our comprehensive mock interview system helps you prepare for real-world interviews with 
                        structured practice sessions, expert feedback, and performance tracking.
                    </p>
                    
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start space-x-3">
                            <div class="w-6 h-6 rounded-full bg-green-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-gray-300">Technical, behavioral, and case study interviews</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <div class="w-6 h-6 rounded-full bg-green-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-gray-300">Real-time feedback from industry experts</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <div class="w-6 h-6 rounded-full bg-green-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-gray-300">Track progress across multiple interview rounds</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <div class="w-6 h-6 rounded-full bg-green-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-gray-300">Score contributes directly to Career Capital</span>
                        </li>
                    </ul>
                    
                    <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 bg-teal-600 hover:bg-teal-700 rounded-xl font-semibold text-white transition-colors">
                        <span>Start Practicing</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
                
                <!-- Interview Visualization -->
                <div class="relative">
                    <div class="bg-[#1E293B]/80 backdrop-blur-xl rounded-2xl border border-gray-700/50 p-6 shadow-2xl">
                        <!-- Interview Header -->
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-500 to-teal-700 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-white font-medium">Technical Interview</div>
                                    <div class="text-sm text-gray-400">Software Development Track</div>
                                </div>
                            </div>
                            <div class="px-3 py-1 bg-green-500/20 rounded-full">
                                <span class="text-green-400 text-sm font-medium">Live</span>
                            </div>
                        </div>
                        
                        <!-- Question Display -->
                        <div class="bg-[#0F172A] rounded-xl p-4 mb-6">
                            <div class="text-sm text-gray-400 mb-2">Current Question</div>
                            <p class="text-white">"Explain the difference between SQL and NoSQL databases. When would you choose one over the other?"</p>
                        </div>
                        
                        <!-- Scoring Preview -->
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="bg-[#0F172A] rounded-xl p-3 text-center">
                                <div class="text-2xl font-bold text-purple-400">85</div>
                                <div class="text-xs text-gray-400">Technical</div>
                            </div>
                            <div class="bg-[#0F172A] rounded-xl p-3 text-center">
                                <div class="text-2xl font-bold text-blue-400">78</div>
                                <div class="text-xs text-gray-400">Communication</div>
                            </div>
                            <div class="bg-[#0F172A] rounded-xl p-3 text-center">
                                <div class="text-2xl font-bold text-teal-400">92</div>
                                <div class="text-xs text-gray-400">Confidence</div>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div>
                            <div class="flex justify-between text-sm text-gray-400 mb-2">
                                <span>Interview Progress</span>
                                <span>Question 3 of 5</span>
                            </div>
                            <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full w-3/5 bg-gradient-to-r from-purple-600 to-teal-500 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Floating Badge -->
                    <div class="absolute -top-4 -right-4 px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg shadow-lg animate-float">
                        <span class="text-white font-medium text-sm">+15 Career Capital</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-purple-900/30 via-blue-900/30 to-teal-900/30"></div>
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0wIDBoNjB2NjBIMHoiLz48cGF0aCBkPSJNMzAgMzBtLTEgMGExIDEgMCAxIDAgMiAwYTEgMSAwIDEgMCAtMiAwIiBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMDUpIi8+PC9nPjwvc3ZnPg==')] opacity-40"></div>
        
        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-6">
                Ready to Build Your
                <span class="text-gradient">Career Capital?</span>
            </h2>
            
            <p class="text-xl text-gray-400 mb-10 max-w-2xl mx-auto">
                Join hundreds of ambitious professionals transforming their learning into measurable career readiness. 
                Start your journey today.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="group px-10 py-4 bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl font-semibold text-white shadow-2xl shadow-purple-500/30 hover:shadow-purple-500/50 transition-all duration-300 flex items-center justify-center space-x-2">
                        <span>Start Free Today</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                @endif
                <a href="#features" class="px-10 py-4 border-2 border-gray-600 hover:border-gray-500 rounded-xl font-semibold text-white hover:bg-gray-800/50 transition-all duration-300">
                    Learn More
                </a>
            </div>
            
            <p class="mt-6 text-sm text-gray-500">
                No credit card required • Free 14-day trial • Cancel anytime
            </p>
        </div>
    </section>

    <!-- Recruiter CTA Section -->
    <section class="py-16 relative bg-[#0B1120]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-teal-900/50 to-blue-900/50 rounded-3xl p-8 sm:p-12 border border-teal-500/20">
                <div class="grid lg:grid-cols-2 gap-8 items-center">
                    <div>
                        <div class="inline-flex items-center px-4 py-2 rounded-full bg-teal-600/20 border border-teal-500/30 text-teal-300 text-sm font-medium mb-4">
                            For Recruiters
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-bold text-white mb-4">
                            Find Pre-Qualified Talent with Verified Skills
                        </h3>
                        <p class="text-gray-400 mb-6">
                            Access our marketplace of career-ready fellows with verified Career Capital scores. 
                            Filter by track, skills, and interview performance to find your perfect candidates.
                        </p>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 bg-teal-600 hover:bg-teal-700 rounded-xl font-semibold text-white transition-colors">
                            <span>Access Talent Marketplace</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    </div>
                    
                    <div class="flex justify-center lg:justify-end">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-[#1E293B]/80 rounded-xl p-4 text-center">
                                <div class="text-3xl font-bold text-teal-400 mb-1">60%</div>
                                <div class="text-sm text-gray-400">Faster Hiring</div>
                            </div>
                            <div class="bg-[#1E293B]/80 rounded-xl p-4 text-center">
                                <div class="text-3xl font-bold text-blue-400 mb-1">50+</div>
                                <div class="text-sm text-gray-400">Active Fellows</div>
                            </div>
                            <div class="bg-[#1E293B]/80 rounded-xl p-4 text-center">
                                <div class="text-3xl font-bold text-purple-400 mb-1">6</div>
                                <div class="text-sm text-gray-400">Career Tracks</div>
                            </div>
                            <div class="bg-[#1E293B]/80 rounded-xl p-4 text-center">
                                <div class="text-3xl font-bold text-amber-400 mb-1">4.5</div>
                                <div class="text-sm text-gray-400">Satisfaction</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-[#0B1120] border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <!-- Brand -->
                <div class="md:col-span-1">
                    <a href="/" class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-600 via-blue-600 to-teal-500 flex items-center justify-center">
                            <span class="text-white font-bold">IKS</span>
                        </div>
                        <span class="text-lg font-bold text-white">Career Capital</span>
                    </a>
                    <p class="text-gray-400 text-sm mb-4">
                        I-NNOVA CMR's flagship career readiness platform. Transform your learning into measurable career success.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg>
                        </a>
                    </div>
                </div>
                
                <!-- Product -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Product</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-gray-400 hover:text-white text-sm transition-colors">Features</a></li>
                        <li><a href="#tracks" class="text-gray-400 hover:text-white text-sm transition-colors">Career Tracks</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Mock Interviews</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Talent Marketplace</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Pricing</a></li>
                    </ul>
                </div>
                
                <!-- Company -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">About I-NNOVA</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Careers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Blog</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Press</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Contact</a></li>
                    </ul>
                </div>
                
                <!-- Legal -->
                <div>
                    <h4 class="text-white font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Cookie Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Security</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="pt-8 border-t border-gray-800 flex flex-col sm:flex-row justify-between items-center">
                <p class="text-gray-500 text-sm">
                    © {{ date('Y') }} I-NNOVA CMR. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
