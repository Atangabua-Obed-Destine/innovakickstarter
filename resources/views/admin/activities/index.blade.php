@extends('layouts.app')

@section('title', 'Manage Activities')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Manage Activities</h1>
            <p class="text-dark-400">Create and manage learning activities</p>
        </div>
        <a href="{{ route('admin.activities.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Create Activity
        </a>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <div class="flex flex-col lg:flex-row gap-4">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" placeholder="Search activities..." class="form-input pl-10">
            </div>
            <select class="form-input w-full lg:w-40">
                <option value="">All Tracks</option>
                <option>Software Engineering</option>
                <option>Data Science</option>
                <option>Product Management</option>
                <option>Digital Marketing</option>
            </select>
            <select class="form-input w-full lg:w-40">
                <option value="">All Types</option>
                <option value="lesson">Lesson</option>
                <option value="challenge">Challenge</option>
                <option value="project">Project</option>
            </select>
            <select class="form-input w-full lg:w-40">
                <option value="">All Pillars</option>
                <option value="technical">Technical Skills</option>
                <option value="soft">Soft Skills</option>
                <option value="domain">Domain Knowledge</option>
                <option value="career">Career Readiness</option>
            </select>
            <select class="form-input w-full lg:w-36">
                <option value="">All Status</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived</option>
            </select>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid sm:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-white">156</p>
            <p class="text-dark-400 text-sm">Total Activities</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-green-400">142</p>
            <p class="text-dark-400 text-sm">Published</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-amber-400">8</p>
            <p class="text-dark-400 text-sm">Drafts</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-dark-400">6</p>
            <p class="text-dark-400 text-sm">Archived</p>
        </div>
    </div>

    <!-- Activities Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800">
                    <tr>
                        <th class="py-3 px-4 w-10">
                            <input type="checkbox" class="w-4 h-4 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500">
                        </th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Activity</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Track / Pillar</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Type</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Points</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Completions</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Avg Rating</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Status</th>
                        <th class="text-right py-3 px-4 text-dark-400 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @foreach([
                        ['title' => 'JavaScript Fundamentals', 'desc' => 'Learn core JS concepts', 'track' => 'Software Engineering', 'pillar' => 'Technical Skills', 'type' => 'lesson', 'points' => 25, 'completions' => 324, 'rating' => 4.8, 'status' => 'published'],
                        ['title' => 'Build a REST API', 'desc' => 'Create a full REST API with Node.js', 'track' => 'Software Engineering', 'pillar' => 'Technical Skills', 'type' => 'project', 'points' => 100, 'completions' => 189, 'rating' => 4.6, 'status' => 'published'],
                        ['title' => 'Data Visualization Challenge', 'desc' => 'Create interactive charts', 'track' => 'Data Science', 'pillar' => 'Technical Skills', 'type' => 'challenge', 'points' => 50, 'completions' => 156, 'rating' => 4.5, 'status' => 'published'],
                        ['title' => 'Effective Communication', 'desc' => 'Master workplace communication', 'track' => 'General', 'pillar' => 'Soft Skills', 'type' => 'lesson', 'points' => 20, 'completions' => 412, 'rating' => 4.9, 'status' => 'published'],
                        ['title' => 'Machine Learning Intro', 'desc' => 'Introduction to ML concepts', 'track' => 'Data Science', 'pillar' => 'Domain Knowledge', 'type' => 'lesson', 'points' => 30, 'completions' => 98, 'rating' => 4.7, 'status' => 'published'],
                        ['title' => 'Resume Building Workshop', 'desc' => 'Craft an impactful resume', 'track' => 'General', 'pillar' => 'Career Readiness', 'type' => 'lesson', 'points' => 25, 'completions' => 567, 'rating' => 4.8, 'status' => 'published'],
                        ['title' => 'Advanced React Patterns', 'desc' => 'Deep dive into React', 'track' => 'Software Engineering', 'pillar' => 'Technical Skills', 'type' => 'lesson', 'points' => 35, 'completions' => 0, 'rating' => 0, 'status' => 'draft'],
                        ['title' => 'Product Strategy 101', 'desc' => 'Learn product strategy basics', 'track' => 'Product Management', 'pillar' => 'Domain Knowledge', 'type' => 'lesson', 'points' => 25, 'completions' => 234, 'rating' => 4.4, 'status' => 'archived'],
                    ] as $activity)
                        <tr class="hover:bg-dark-800/50 transition-colors">
                            <td class="py-4 px-4">
                                <input type="checkbox" class="w-4 h-4 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500">
                            </td>
                            <td class="py-4 px-4">
                                <div class="max-w-xs">
                                    <p class="text-dark-200 font-medium truncate">{{ $activity['title'] }}</p>
                                    <p class="text-dark-500 text-sm truncate">{{ $activity['desc'] }}</p>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <p class="text-dark-200">{{ $activity['track'] }}</p>
                                <p class="text-dark-500 text-sm">{{ $activity['pillar'] }}</p>
                            </td>
                            <td class="py-4 px-4 text-center">
                                @php
                                    $typeClasses = match($activity['type']) {
                                        'lesson' => 'bg-primary-600/20 text-primary-400 border-primary-500/30',
                                        'challenge' => 'bg-blue-600/20 text-blue-400 border-blue-500/30',
                                        'project' => 'bg-teal-600/20 text-teal-400 border-teal-500/30',
                                        default => ''
                                    };
                                    $typeIcons = match($activity['type']) {
                                        'lesson' => '📚',
                                        'challenge' => '⚡',
                                        'project' => '🚀',
                                        default => '📋'
                                    };
                                @endphp
                                <span class="badge {{ $typeClasses }}">
                                    {{ $typeIcons }} {{ ucfirst($activity['type']) }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="text-primary-400 font-medium">{{ $activity['points'] }} pts</span>
                            </td>
                            <td class="py-4 px-4 text-center text-dark-300">
                                {{ number_format($activity['completions']) }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($activity['rating'] > 0)
                                    <div class="flex items-center justify-center gap-1">
                                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <span class="text-dark-200">{{ $activity['rating'] }}</span>
                                    </div>
                                @else
                                    <span class="text-dark-500">—</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                @php
                                    $statusClasses = match($activity['status']) {
                                        'published' => 'bg-green-600/20 text-green-400 border-green-500/30',
                                        'draft' => 'bg-amber-600/20 text-amber-400 border-amber-500/30',
                                        'archived' => 'bg-dark-600/20 text-dark-400 border-dark-500/30',
                                        default => ''
                                    };
                                @endphp
                                <span class="badge {{ $statusClasses }}">{{ ucfirst($activity['status']) }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-end gap-1">
                                    <button class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors" title="Preview">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    <a href="{{ route('admin.activities.edit', 1) }}" 
                                       class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <button class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors" title="Duplicate">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                    <button class="p-2 text-dark-400 hover:text-red-400 hover:bg-dark-700 rounded-lg transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-dark-700 flex items-center justify-between">
            <p class="text-dark-500 text-sm">Showing 1-8 of 156 activities</p>
            <div class="flex items-center gap-2">
                <button class="px-3 py-1.5 rounded bg-dark-800 text-dark-500 cursor-not-allowed" disabled>Previous</button>
                <button class="px-3 py-1.5 rounded bg-primary-600 text-white">1</button>
                <button class="px-3 py-1.5 rounded bg-dark-800 text-dark-200 hover:bg-dark-700">2</button>
                <button class="px-3 py-1.5 rounded bg-dark-800 text-dark-200 hover:bg-dark-700">3</button>
                <span class="text-dark-500">...</span>
                <button class="px-3 py-1.5 rounded bg-dark-800 text-dark-200 hover:bg-dark-700">20</button>
                <button class="px-3 py-1.5 rounded bg-dark-800 text-dark-200 hover:bg-dark-700">Next</button>
            </div>
        </div>
    </div>
</div>
@endsection
