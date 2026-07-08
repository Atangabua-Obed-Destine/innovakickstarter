@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Reports & Analytics</h1>
            <p class="text-dark-400">Comprehensive insights into platform performance</p>
        </div>
        <div class="flex gap-2">
            <select class="form-input py-2 text-sm w-auto">
                <option>Last 7 days</option>
                <option>Last 30 days</option>
                <option selected>Last 90 days</option>
                <option>Last year</option>
                <option>All time</option>
            </select>
            <button class="btn btn-outline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export PDF
            </button>
            <button class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Generate Report
            </button>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Total Fellows', 'value' => '847', 'change' => '+12%', 'trend' => 'up', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color' => 'primary'],
            ['label' => 'Avg Career Capital', 'value' => '68%', 'change' => '+5%', 'trend' => 'up', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'color' => 'teal'],
            ['label' => 'Activities Completed', 'value' => '12,456', 'change' => '+28%', 'trend' => 'up', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'blue'],
            ['label' => 'Active Engagement', 'value' => '89%', 'change' => '-2%', 'trend' => 'down', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'color' => 'amber'],
        ] as $metric)
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-{{ $metric['color'] }}-600/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-{{ $metric['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $metric['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="flex items-center gap-1 text-sm {{ $metric['trend'] === 'up' ? 'text-green-400' : 'text-red-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="{{ $metric['trend'] === 'up' ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}"/>
                        </svg>
                        {{ $metric['change'] }}
                    </span>
                </div>
                <p class="text-3xl font-bold text-white">{{ $metric['value'] }}</p>
                <p class="text-dark-400 text-sm">{{ $metric['label'] }}</p>
            </div>
        @endforeach
    </div>

    <!-- Report Sections -->
    <div x-data="{ activeTab: 'engagement' }" class="space-y-6">
        <!-- Tabs -->
        <div class="flex gap-2 overflow-x-auto pb-2 border-b border-dark-700">
            @foreach(['engagement' => 'Engagement', 'performance' => 'Performance', 'cohorts' => 'Cohorts', 'activities' => 'Activities', 'recruitment' => 'Recruitment'] as $key => $label)
                <button @click="activeTab = '{{ $key }}'" 
                        :class="activeTab === '{{ $key }}' ? 'text-primary-400 border-primary-400' : 'text-dark-400 border-transparent hover:text-dark-200'"
                        class="px-4 py-2 text-sm font-medium border-b-2 -mb-[2px] transition-colors whitespace-nowrap">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <!-- Engagement Tab -->
        <div x-show="activeTab === 'engagement'" x-transition class="space-y-6">
            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Daily Active Users -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-6">Daily Active Users</h3>
                    <div class="h-64 flex items-end justify-between gap-1">
                        @foreach([45, 52, 48, 61, 55, 67, 72, 68, 75, 82, 78, 85, 91, 88, 95, 89, 92, 86, 94, 98, 102, 97, 105, 108, 103, 112, 118, 115] as $index => $value)
                            <div class="flex-1 group relative">
                                <div class="w-full bg-gradient-to-t from-primary-600 to-primary-400 rounded-t transition-all group-hover:opacity-80" 
                                     style="height: {{ ($value / 120) * 100 }}%"></div>
                                <div class="absolute -top-8 left-1/2 -translate-x-1/2 hidden group-hover:block bg-dark-700 px-2 py-1 rounded text-xs text-white whitespace-nowrap z-10">
                                    {{ $value }} users
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between mt-4 text-dark-500 text-xs">
                        <span>Dec 1</span>
                        <span>Dec 15</span>
                        <span>Dec 28</span>
                    </div>
                </div>

                <!-- Engagement by Time -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-6">Peak Activity Hours</h3>
                    <div class="space-y-3">
                        @foreach([
                            ['hour' => '6-9 AM', 'percent' => 15],
                            ['hour' => '9-12 PM', 'percent' => 45],
                            ['hour' => '12-3 PM', 'percent' => 35],
                            ['hour' => '3-6 PM', 'percent' => 55],
                            ['hour' => '6-9 PM', 'percent' => 85],
                            ['hour' => '9-12 AM', 'percent' => 40],
                        ] as $time)
                            <div class="flex items-center gap-4">
                                <span class="text-dark-400 text-sm w-20">{{ $time['hour'] }}</span>
                                <div class="flex-1 h-6 bg-dark-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-primary-600 to-teal-500 rounded-full" style="width: {{ $time['percent'] }}%"></div>
                                </div>
                                <span class="text-dark-300 text-sm w-10 text-right">{{ $time['percent'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Engagement Metrics -->
            <div class="grid sm:grid-cols-3 gap-4">
                <div class="card p-6 text-center">
                    <p class="text-4xl font-bold text-primary-400">4.2</p>
                    <p class="text-dark-400 text-sm mt-1">Avg Sessions / Week</p>
                </div>
                <div class="card p-6 text-center">
                    <p class="text-4xl font-bold text-teal-400">32 min</p>
                    <p class="text-dark-400 text-sm mt-1">Avg Session Duration</p>
                </div>
                <div class="card p-6 text-center">
                    <p class="text-4xl font-bold text-blue-400">8</p>
                    <p class="text-dark-400 text-sm mt-1">Avg Activities / Fellow</p>
                </div>
            </div>
        </div>

        <!-- Performance Tab -->
        <div x-show="activeTab === 'performance'" x-transition class="space-y-6">
            <!-- Score Distribution -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-6">Career Capital Score Distribution</h3>
                <div class="grid grid-cols-10 gap-2 h-48 items-end">
                    @foreach([8, 12, 18, 25, 45, 78, 120, 156, 189, 196] as $index => $count)
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-full bg-gradient-to-t from-primary-600 to-teal-500 rounded-t transition-all hover:opacity-80" 
                                 style="height: {{ ($count / 200) * 100 }}%"></div>
                            <span class="text-dark-500 text-xs">{{ ($index + 1) * 10 }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Pillar Breakdown -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach([
                    ['name' => 'Technical Skills', 'avg' => 72, 'color' => 'primary'],
                    ['name' => 'Soft Skills', 'avg' => 68, 'color' => 'blue'],
                    ['name' => 'Domain Knowledge', 'avg' => 65, 'color' => 'teal'],
                    ['name' => 'Career Readiness', 'avg' => 70, 'color' => 'amber'],
                ] as $pillar)
                    <div class="card p-6 text-center">
                        <div class="w-24 h-24 mx-auto relative mb-4">
                            <svg class="w-24 h-24 -rotate-90">
                                <circle cx="48" cy="48" r="40" fill="none" stroke="currentColor" stroke-width="8" class="text-dark-700"/>
                                <circle cx="48" cy="48" r="40" fill="none" stroke="currentColor" stroke-width="8" 
                                        stroke-dasharray="{{ 2 * 3.14159 * 40 }}" 
                                        stroke-dashoffset="{{ 2 * 3.14159 * 40 * (1 - $pillar['avg'] / 100) }}"
                                        class="text-{{ $pillar['color'] }}-500"/>
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-xl font-bold text-white">
                                {{ $pillar['avg'] }}%
                            </span>
                        </div>
                        <p class="text-dark-200 font-medium">{{ $pillar['name'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Cohorts Tab -->
        <div x-show="activeTab === 'cohorts'" x-transition>
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-dark-800">
                            <tr>
                                <th class="text-left py-3 px-4 text-dark-400 font-medium">Cohort</th>
                                <th class="text-center py-3 px-4 text-dark-400 font-medium">Fellows</th>
                                <th class="text-center py-3 px-4 text-dark-400 font-medium">Avg Score</th>
                                <th class="text-center py-3 px-4 text-dark-400 font-medium">Completion</th>
                                <th class="text-center py-3 px-4 text-dark-400 font-medium">Engagement</th>
                                <th class="text-center py-3 px-4 text-dark-400 font-medium">Trend</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-700">
                            @foreach([
                                ['name' => 'Cohort 8', 'track' => 'Software Engineering', 'fellows' => 45, 'score' => 72, 'completion' => 35, 'engagement' => 92, 'trend' => 'up'],
                                ['name' => 'Cohort 7', 'track' => 'Data Science', 'fellows' => 38, 'score' => 78, 'completion' => 68, 'engagement' => 88, 'trend' => 'up'],
                                ['name' => 'Cohort 6', 'track' => 'Product Management', 'fellows' => 42, 'score' => 85, 'completion' => 100, 'engagement' => 45, 'trend' => 'down'],
                                ['name' => 'Cohort 5', 'track' => 'Digital Marketing', 'fellows' => 28, 'score' => 81, 'completion' => 100, 'engagement' => 38, 'trend' => 'down'],
                            ] as $cohort)
                                <tr class="hover:bg-dark-800/50">
                                    <td class="py-4 px-4">
                                        <p class="text-dark-200 font-medium">{{ $cohort['name'] }}</p>
                                        <p class="text-dark-500 text-sm">{{ $cohort['track'] }}</p>
                                    </td>
                                    <td class="py-4 px-4 text-center text-dark-300">{{ $cohort['fellows'] }}</td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="font-medium {{ $cohort['score'] >= 80 ? 'text-green-400' : ($cohort['score'] >= 60 ? 'text-amber-400' : 'text-red-400') }}">
                                            {{ $cohort['score'] }}%
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-16 h-2 bg-dark-700 rounded-full">
                                                <div class="h-full bg-teal-500 rounded-full" style="width: {{ $cohort['completion'] }}%"></div>
                                            </div>
                                            <span class="text-dark-400 text-sm">{{ $cohort['completion'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="text-dark-300">{{ $cohort['engagement'] }}%</span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="flex items-center justify-center {{ $cohort['trend'] === 'up' ? 'text-green-400' : 'text-red-400' }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="{{ $cohort['trend'] === 'up' ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}"/>
                                            </svg>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Activities Tab -->
        <div x-show="activeTab === 'activities'" x-transition class="space-y-6">
            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Most Popular Activities -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Most Popular Activities</h3>
                    <div class="space-y-3">
                        @foreach([
                            ['title' => 'JavaScript Fundamentals', 'completions' => 324, 'rating' => 4.8],
                            ['title' => 'Effective Communication', 'completions' => 412, 'rating' => 4.9],
                            ['title' => 'Resume Building Workshop', 'completions' => 567, 'rating' => 4.8],
                            ['title' => 'Data Visualization', 'completions' => 256, 'rating' => 4.6],
                            ['title' => 'Build a REST API', 'completions' => 189, 'rating' => 4.5],
                        ] as $activity)
                            <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                                <div class="flex-1">
                                    <p class="text-dark-200 font-medium">{{ $activity['title'] }}</p>
                                    <p class="text-dark-500 text-sm">{{ $activity['completions'] }} completions</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="text-dark-300">{{ $activity['rating'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Activity by Type -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Completions by Type</h3>
                    <div class="flex items-center justify-center gap-8 h-48">
                        <div class="text-center">
                            <div class="w-24 h-24 rounded-full bg-primary-600/30 flex items-center justify-center text-primary-400 mb-2">
                                <span class="text-2xl font-bold">65%</span>
                            </div>
                            <p class="text-dark-300">Lessons</p>
                        </div>
                        <div class="text-center">
                            <div class="w-20 h-20 rounded-full bg-blue-600/30 flex items-center justify-center text-blue-400 mb-2">
                                <span class="text-xl font-bold">25%</span>
                            </div>
                            <p class="text-dark-300">Challenges</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 rounded-full bg-teal-600/30 flex items-center justify-center text-teal-400 mb-2">
                                <span class="text-lg font-bold">10%</span>
                            </div>
                            <p class="text-dark-300">Projects</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recruitment Tab -->
        <div x-show="activeTab === 'recruitment'" x-transition class="space-y-6">
            <div class="grid sm:grid-cols-4 gap-4">
                <div class="card p-6 text-center">
                    <p class="text-3xl font-bold text-primary-400">24</p>
                    <p class="text-dark-400 text-sm">Active Recruiters</p>
                </div>
                <div class="card p-6 text-center">
                    <p class="text-3xl font-bold text-teal-400">156</p>
                    <p class="text-dark-400 text-sm">Profile Views</p>
                </div>
                <div class="card p-6 text-center">
                    <p class="text-3xl font-bold text-blue-400">42</p>
                    <p class="text-dark-400 text-sm">Shortlisted</p>
                </div>
                <div class="card p-6 text-center">
                    <p class="text-3xl font-bold text-green-400">12</p>
                    <p class="text-dark-400 text-sm">Hired</p>
                </div>
            </div>

            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Top Hiring Companies</h3>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach([
                        ['name' => 'TechCorp Inc.', 'views' => 45, 'shortlisted' => 12, 'hired' => 3],
                        ['name' => 'DataFlow Systems', 'views' => 38, 'shortlisted' => 8, 'hired' => 2],
                        ['name' => 'Innovation Labs', 'views' => 32, 'shortlisted' => 10, 'hired' => 4],
                        ['name' => 'Digital Solutions', 'views' => 28, 'shortlisted' => 6, 'hired' => 1],
                        ['name' => 'CloudBase Africa', 'views' => 25, 'shortlisted' => 5, 'hired' => 2],
                        ['name' => 'StartupXYZ', 'views' => 22, 'shortlisted' => 4, 'hired' => 0],
                    ] as $company)
                        <div class="p-4 bg-dark-800 rounded-lg">
                            <p class="text-dark-200 font-medium mb-3">{{ $company['name'] }}</p>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-dark-500">{{ $company['views'] }} views</span>
                                <span class="text-primary-400">{{ $company['shortlisted'] }} shortlisted</span>
                                <span class="text-green-400">{{ $company['hired'] }} hired</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
