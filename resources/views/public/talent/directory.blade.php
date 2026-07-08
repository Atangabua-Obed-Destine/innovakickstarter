<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Discover top talent from I-NNOVA's Career Capital Platform">

    <title>Talent Directory | IKS Career Capital Platform</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-dark-900 text-dark-300 antialiased" x-data="{ filterOpen: false }">
    <!-- Navigation -->
    <nav class="bg-dark-800 border-b border-dark-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-600 to-blue-600 flex items-center justify-center">
                    <span class="text-white font-bold text-lg">IKS</span>
                </div>
                <span class="text-xl font-bold text-white hidden sm:block">Career Capital</span>
            </a>
            <div class="flex items-center gap-4">
                <a href="{{ route('login') }}" class="text-dark-400 hover:text-white transition-colors text-sm">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary py-2 px-4 text-sm">Join Platform</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-b from-dark-800 to-dark-900 py-16">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h1 class="text-4xl lg:text-5xl font-bold text-white mb-4">
                Discover <span class="bg-gradient-to-r from-primary-400 to-teal-400 bg-clip-text text-transparent">Exceptional Talent</span>
            </h1>
            <p class="text-dark-400 text-lg max-w-2xl mx-auto mb-8">
                Browse verified profiles of I-NNOVA's top fellows. Each candidate has earned their Career Capital score through real-world activities and rigorous assessments.
            </p>
            
            <!-- Search Bar -->
            <form action="{{ route('public.talent.directory') }}" method="GET" class="max-w-3xl mx-auto">
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by skills, track, or name..." 
                               class="form-input pl-12 py-4 text-lg w-full">
                    </div>
                    <button type="submit" class="btn btn-primary py-4 px-8">
                        Search
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-12">
        <!-- Stats Bar -->
        <div class="grid sm:grid-cols-4 gap-4 mb-8">
            <div class="card p-4 text-center">
                <p class="text-3xl font-bold text-primary-400">{{ $totalTalents ?? '2,847' }}</p>
                <p class="text-dark-500 text-sm">Total Talents</p>
            </div>
            <div class="card p-4 text-center">
                <p class="text-3xl font-bold text-teal-400">{{ $availableNow ?? 156 }}</p>
                <p class="text-dark-500 text-sm">Available Now</p>
            </div>
            <div class="card p-4 text-center">
                <p class="text-3xl font-bold text-blue-400">{{ $avgScore ?? 78 }}%</p>
                <p class="text-dark-500 text-sm">Avg Career Capital</p>
            </div>
            <div class="card p-4 text-center">
                <p class="text-3xl font-bold text-amber-400">{{ $tracks ?? 12 }}</p>
                <p class="text-dark-500 text-sm">Career Tracks</p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Filters Sidebar -->
            <aside class="lg:w-72 shrink-0">
                <div class="card p-6 sticky top-24">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-white">Filters</h2>
                        <a href="{{ route('public.talent.directory') }}" class="text-primary-400 text-sm hover:underline">Clear All</a>
                    </div>

                    <form action="{{ route('public.talent.directory') }}" method="GET" class="space-y-6">
                        <!-- Track Filter -->
                        <div>
                            <label class="block text-dark-400 text-sm mb-2">Career Track</label>
                            <select name="track" class="form-input w-full">
                                <option value="">All Tracks</option>
                                @foreach([
                                    'Software Engineering', 'Data Science', 'Product Management', 
                                    'UI/UX Design', 'Digital Marketing', 'Business Development',
                                    'Finance', 'Project Management', 'Entrepreneurship',
                                    'Cloud Engineering', 'Cybersecurity', 'AI/ML Engineering'
                                ] as $track)
                                    <option {{ request('track') === $track ? 'selected' : '' }}>{{ $track }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Score Range -->
                        <div>
                            <label class="block text-dark-400 text-sm mb-2">Minimum Score</label>
                            <div class="flex gap-2">
                                @foreach(['70' => '70%+', '80' => '80%+', '90' => '90%+'] as $value => $label)
                                    <label class="flex-1">
                                        <input type="radio" name="min_score" value="{{ $value }}" 
                                               {{ request('min_score') == $value ? 'checked' : '' }}
                                               class="sr-only peer">
                                        <div class="text-center py-2 px-3 bg-dark-700 rounded-lg cursor-pointer text-sm text-dark-400 peer-checked:bg-primary-600/20 peer-checked:text-primary-400 peer-checked:border-primary-500 border border-transparent hover:bg-dark-600 transition-colors">
                                            {{ $label }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Availability -->
                        <div>
                            <label class="block text-dark-400 text-sm mb-2">Availability</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="available_now" value="1" {{ request('available_now') ? 'checked' : '' }}
                                           class="w-4 h-4 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500">
                                    <span class="text-dark-300 text-sm">Available Now</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="open_to_remote" value="1" {{ request('open_to_remote') ? 'checked' : '' }}
                                           class="w-4 h-4 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500">
                                    <span class="text-dark-300 text-sm">Open to Remote</span>
                                </label>
                            </div>
                        </div>

                        <!-- Cohort -->
                        <div>
                            <label class="block text-dark-400 text-sm mb-2">Cohort</label>
                            <select name="cohort" class="form-input w-full">
                                <option value="">All Cohorts</option>
                                <option {{ request('cohort') === '2024-A' ? 'selected' : '' }}>2024-A</option>
                                <option {{ request('cohort') === '2024-B' ? 'selected' : '' }}>2024-B</option>
                                <option {{ request('cohort') === '2023-A' ? 'selected' : '' }}>2023-A</option>
                                <option {{ request('cohort') === '2023-B' ? 'selected' : '' }}>2023-B</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-full justify-center">
                            Apply Filters
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Talent Grid -->
            <div class="flex-1">
                <!-- Sort Bar -->
                <div class="flex items-center justify-between mb-6">
                    <p class="text-dark-400">Showing <span class="text-white font-medium">{{ $showing ?? 24 }}</span> of <span class="text-white">{{ $totalTalents ?? '2,847' }}</span> talents</p>
                    <select class="form-input w-40" onchange="this.form.submit()">
                        <option>Score: High to Low</option>
                        <option>Most Recent</option>
                        <option>Name (A-Z)</option>
                    </select>
                </div>

                <!-- Talent Cards Grid -->
                <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach([
                        ['name' => 'Alex Johnson', 'title' => 'Full Stack Developer', 'track' => 'Software Engineering', 'score' => 92, 'skills' => ['React', 'Node.js', 'Python', 'AWS'], 'available' => true, 'location' => 'Yaoundé'],
                        ['name' => 'Sarah Chen', 'title' => 'Data Scientist', 'track' => 'Data Science', 'score' => 89, 'skills' => ['Python', 'TensorFlow', 'SQL'], 'available' => true, 'location' => 'Douala'],
                        ['name' => 'Michael Brown', 'title' => 'DevOps Engineer', 'track' => 'Cloud Engineering', 'score' => 87, 'skills' => ['Docker', 'Kubernetes', 'AWS'], 'available' => false, 'location' => 'Yaoundé'],
                        ['name' => 'Emily Davis', 'title' => 'Product Manager', 'track' => 'Product Management', 'score' => 85, 'skills' => ['Agile', 'Analytics', 'UX'], 'available' => true, 'location' => 'Buea'],
                        ['name' => 'David Wilson', 'title' => 'UX Designer', 'track' => 'UI/UX Design', 'score' => 84, 'skills' => ['Figma', 'User Research', 'Prototyping'], 'available' => true, 'location' => 'Yaoundé'],
                        ['name' => 'Lisa Taylor', 'title' => 'ML Engineer', 'track' => 'AI/ML Engineering', 'score' => 83, 'skills' => ['Python', 'PyTorch', 'MLOps'], 'available' => false, 'location' => 'Douala'],
                        ['name' => 'James Anderson', 'title' => 'Frontend Developer', 'track' => 'Software Engineering', 'score' => 81, 'skills' => ['React', 'TypeScript', 'CSS'], 'available' => true, 'location' => 'Bamenda'],
                        ['name' => 'Amanda Martinez', 'title' => 'Digital Marketer', 'track' => 'Digital Marketing', 'score' => 80, 'skills' => ['SEO', 'Analytics', 'Content'], 'available' => true, 'location' => 'Yaoundé'],
                        ['name' => 'Robert Kim', 'title' => 'Backend Developer', 'track' => 'Software Engineering', 'score' => 79, 'skills' => ['Python', 'Django', 'PostgreSQL'], 'available' => false, 'location' => 'Douala'],
                    ] as $talent)
                        <a href="{{ route('public.profile.show', 1) }}" class="card p-6 hover:border-primary-500/30 transition-all group">
                            <div class="flex items-start gap-4 mb-4">
                                <div class="relative">
                                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg">
                                        {{ strtoupper(substr($talent['name'], 0, 2)) }}
                                    </div>
                                    @if($talent['available'])
                                        <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full bg-green-500 border-2 border-dark-800"></div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-dark-200 font-semibold group-hover:text-primary-400 transition-colors truncate">
                                        {{ $talent['name'] }}
                                    </h3>
                                    <p class="text-dark-500 text-sm truncate">{{ $talent['title'] }}</p>
                                    <p class="text-dark-600 text-xs">{{ $talent['location'] }}</p>
                                </div>
                                <div class="text-center">
                                    <div class="relative w-12 h-12">
                                        <svg class="w-12 h-12 -rotate-90">
                                            <circle cx="24" cy="24" r="20" fill="none" stroke="currentColor" stroke-width="4" class="text-dark-700"/>
                                            <circle cx="24" cy="24" r="20" fill="none" stroke="currentColor" stroke-width="4" 
                                                    stroke-dasharray="{{ 2 * 3.14159 * 20 }}" 
                                                    stroke-dashoffset="{{ 2 * 3.14159 * 20 * (1 - $talent['score'] / 100) }}"
                                                    class="{{ $talent['score'] >= 85 ? 'text-green-500' : ($talent['score'] >= 75 ? 'text-teal-500' : 'text-amber-500') }}"/>
                                        </svg>
                                        <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-white">
                                            {{ $talent['score'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <span class="px-2 py-1 bg-primary-600/20 text-primary-400 rounded text-xs">{{ $talent['track'] }}</span>
                            </div>

                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($talent['skills'], 0, 3) as $skill)
                                    <span class="px-2 py-0.5 bg-dark-700 text-dark-400 rounded text-xs">{{ $skill }}</span>
                                @endforeach
                                @if(count($talent['skills']) > 3)
                                    <span class="px-2 py-0.5 bg-dark-700 text-dark-500 rounded text-xs">+{{ count($talent['skills']) - 3 }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8 flex justify-center">
                    <nav class="flex items-center gap-1">
                        <button class="p-2 rounded-lg text-dark-500 hover:bg-dark-800 transition-colors" disabled>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button class="w-10 h-10 rounded-lg bg-primary-600 text-white font-medium">1</button>
                        <button class="w-10 h-10 rounded-lg text-dark-400 hover:bg-dark-800 transition-colors">2</button>
                        <button class="w-10 h-10 rounded-lg text-dark-400 hover:bg-dark-800 transition-colors">3</button>
                        <span class="px-2 text-dark-500">...</span>
                        <button class="w-10 h-10 rounded-lg text-dark-400 hover:bg-dark-800 transition-colors">12</button>
                        <button class="p-2 rounded-lg text-dark-400 hover:bg-dark-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </main>

    <!-- CTA Section -->
    <section class="bg-gradient-to-r from-primary-900/50 to-blue-900/50 border-y border-primary-800/30 py-16">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-2xl lg:text-3xl font-bold text-white mb-4">
                Ready to connect with top talent?
            </h2>
            <p class="text-dark-400 mb-8 max-w-xl mx-auto">
                Sign up as a recruiter to access full profiles, contact information, and advanced search features.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="btn btn-primary py-3 px-8 justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Register as Recruiter
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline py-3 px-8 justify-center">
                    Already have an account? Sign In
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark-800 border-t border-dark-700 py-8">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-dark-500 text-sm">
                Powered by <a href="{{ route('home') }}" class="text-primary-400 hover:underline">IKS Career Capital Platform</a> • 
                © {{ date('Y') }} I-NNOVA CMR
            </p>
        </div>
    </footer>
</body>
</html>
