@extends('layouts.guest')

@section('title', 'Create Account')

@section('content')
<div class="w-full max-w-6xl mx-auto">
    <div class="grid lg:grid-cols-2 rounded-3xl overflow-hidden shadow-2xl shadow-accent-900/30 border border-dark-700/60 backdrop-blur-sm">

        {{-- ============================================================ --}}
        {{-- LEFT SIDE — Value Proposition / Journey                      --}}
        {{-- ============================================================ --}}
        <div class="relative hidden lg:flex flex-col justify-between p-10 xl:p-12 bg-gradient-to-br from-accent-700 via-primary-800 to-teal-900 overflow-hidden">
            {{-- Decorative orbs --}}
            <div class="absolute -top-24 -left-24 w-80 h-80 bg-primary-500/30 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-teal-500/25 rounded-full blur-3xl"></div>
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20viewBox%3D%220%200%2060%2060%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg%20fill%3D%22none%22%20fill-rule%3D%22evenodd%22%3E%3Cg%20fill%3D%22%23ffffff%22%20fill-opacity%3D%220.06%22%3E%3Cpath%20d%3D%22M36%2034v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6%2034v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6%204V0H4v4H0v2h4v4h2V6h4V4H6z%22%2F%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E')]"></div>

            <div class="relative">
                {{-- Brand mark --}}
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                    <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-white text-xl font-bold leading-tight">IKS Career Capital</h2>
                        <p class="text-white/60 text-xs">Powered by I-NNOVA CMR</p>
                    </div>
                </a>

                {{-- Headline --}}
                <div class="mt-14">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white/80 text-xs font-medium mb-6">
                        <svg class="w-3.5 h-3.5 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Start free. No credit card
                    </span>
                    <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight">
                        Turn your work into <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-teal-300">a hiring signal</span> recruiters trust.
                    </h1>
                    <p class="mt-5 text-white/70 text-lg leading-relaxed max-w-md">
                        Join a growing community of fellows building measurable Career Capital, verified by projects, interviews and peer collaboration.
                    </p>
                </div>

                {{-- Journey timeline --}}
                <div class="mt-10 max-w-md">
                    <p class="text-white/60 uppercase text-xs tracking-widest font-semibold mb-5">Your journey</p>
                    <ol class="relative border-l-2 border-white/15 space-y-6 pl-6">
                        <li class="relative">
                            <span class="absolute -left-[33px] flex items-center justify-center w-6 h-6 rounded-full bg-primary-500 text-white text-xs font-bold ring-4 ring-primary-900/50">1</span>
                            <p class="text-white font-semibold text-sm">Create your account</p>
                            <p class="text-white/60 text-xs mt-0.5">Pick a track: Full-Stack, Product, Design and more.</p>
                        </li>
                        <li class="relative">
                            <span class="absolute -left-[33px] flex items-center justify-center w-6 h-6 rounded-full bg-accent-500 text-white text-xs font-bold ring-4 ring-accent-900/50">2</span>
                            <p class="text-white font-semibold text-sm">Build Career Capital</p>
                            <p class="text-white/60 text-xs mt-0.5">Ship projects, complete AI & human mock interviews.</p>
                        </li>
                        <li class="relative">
                            <span class="absolute -left-[33px] flex items-center justify-center w-6 h-6 rounded-full bg-teal-500 text-white text-xs font-bold ring-4 ring-teal-900/50">3</span>
                            <p class="text-white font-semibold text-sm">Climb the tiers</p>
                            <p class="text-white/60 text-xs mt-0.5">Rookie → Intern → Professional → Elite.</p>
                        </li>
                        <li class="relative">
                            <span class="absolute -left-[33px] flex items-center justify-center w-6 h-6 rounded-full bg-emerald-500 text-white text-xs font-bold ring-4 ring-emerald-900/50">4</span>
                            <p class="text-white font-semibold text-sm">Get hired</p>
                            <p class="text-white/60 text-xs mt-0.5">Featured on the Talent Marketplace to top recruiters.</p>
                        </li>
                    </ol>
                </div>
            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- RIGHT SIDE — Registration Form                               --}}
        {{-- ============================================================ --}}
        <div class="bg-dark-800/95 backdrop-blur p-8 sm:p-10 xl:p-12 flex flex-col justify-center">
            {{-- Mobile logo --}}
            <div class="text-center mb-8 lg:hidden">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-600 to-accent-600 flex items-center justify-center">
                        <span class="text-white font-bold text-xl">IKS</span>
                    </div>
                    <span class="text-2xl font-bold text-white">Career Capital</span>
                </a>
            </div>

            <div class="max-w-md w-full mx-auto">
                <div class="mb-8">
                    <h2 class="text-2xl sm:text-3xl font-bold text-white">Create your account</h2>
                    <p class="text-dark-400 mt-2 text-sm">Takes less than a minute. No credit card required.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-dark-300 mb-2">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Full Name
                            </span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                               class="form-input w-full @error('name') border-red-500 @enderror"
                               placeholder="John Doe">
                        @error('name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-dark-300 mb-2">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Email Address
                            </span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="form-input w-full @error('email') border-red-500 @enderror"
                               placeholder="you@example.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Role Selection --}}
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-2">I want to join as</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="role" value="fellow" class="sr-only peer" {{ old('role', 'fellow') === 'fellow' ? 'checked' : '' }}>
                                <div class="p-4 rounded-xl border-2 border-dark-600 bg-dark-700/50 peer-checked:border-primary-500 peer-checked:bg-primary-600/10 transition-all">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg bg-primary-600/20">
                                            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-dark-200 font-medium">Fellow</p>
                                            <p class="text-dark-500 text-xs">Build Career Capital</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="role" value="recruiter" class="sr-only peer" {{ old('role') === 'recruiter' ? 'checked' : '' }}>
                                <div class="p-4 rounded-xl border-2 border-dark-600 bg-dark-700/50 peer-checked:border-teal-500 peer-checked:bg-teal-600/10 transition-all">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg bg-teal-600/20">
                                            <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-dark-200 font-medium">Recruiter</p>
                                            <p class="text-dark-500 text-xs">Hire top talent</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @error('role')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-dark-300 mb-2">
                            <span class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Password
                            </span>
                        </label>
                        <input type="password" id="password" name="password" required
                               class="form-input w-full @error('password') border-red-500 @enderror"
                               placeholder="••••••••">
                        @error('password')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-dark-300 mb-2">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               class="form-input w-full"
                               placeholder="••••••••">
                    </div>

                    {{-- Terms --}}
                    <div class="flex items-start gap-2">
                        <input type="checkbox" name="terms" id="terms" required
                               class="mt-1 w-4 h-4 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500">
                        <label for="terms" class="text-dark-400 text-sm">
                            I agree to the <a href="#" class="text-primary-400 hover:underline">Terms of Service</a> and
                            <a href="#" class="text-primary-400 hover:underline">Privacy Policy</a>
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-primary w-full justify-center py-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Create Account
                    </button>
                </form>

                {{-- Divider --}}
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-dark-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-dark-800 text-dark-500">Or continue with</span>
                    </div>
                </div>

                {{-- Social Login --}}
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" class="btn btn-outline justify-center py-2.5">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Google
                    </button>
                    <button type="button" class="btn btn-outline justify-center py-2.5">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        GitHub
                    </button>
                </div>

                {{-- Login Link --}}
                <p class="text-center text-dark-400 mt-6 text-sm">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-primary-400 hover:text-primary-300 font-medium">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
