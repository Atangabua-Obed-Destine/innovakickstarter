@extends('layouts.app')

@section('title', 'My Interviews')

@section('content')
<div class="space-y-6" x-data="interviewList()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-sm text-gray-500 mb-2">
                <a href="{{ route('mentor.dashboard') }}" class="hover:text-gray-700">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Interviews</span>
            </nav>
            <h1 class="text-2xl font-semibold text-gray-900">My Interviews</h1>
            <p class="mt-1 text-gray-600">View and manage all your interview sessions</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('mentor.interviews') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="no_show" {{ request('status') === 'no_show' ? 'selected' : '' }}>No Show</option>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Interview Type</label>
                <select name="type" id="type" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Types</option>
                    @foreach($interviewTypes ?? [] as $type)
                    <option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $type->value)) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label for="from_date" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label for="to_date" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Apply Filters
                </button>
                <a href="{{ route('mentor.interviews') }}" 
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Interview Stats Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-blue-900">{{ $interviews->where('status', 'scheduled')->count() ?? 0 }}</p>
                <p class="text-sm text-blue-700">Scheduled</p>
            </div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-green-900">{{ $interviews->where('status', 'completed')->whereNotNull('feedback')->count() ?? 0 }}</p>
                <p class="text-sm text-green-700">Reviewed</p>
            </div>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-orange-900">{{ $interviews->where('status', 'completed')->whereNull('feedback')->count() ?? 0 }}</p>
                <p class="text-sm text-orange-700">Pending Review</p>
            </div>
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-center gap-4">
            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $interviews->whereIn('status', ['cancelled', 'no_show'])->count() ?? 0 }}</p>
                <p class="text-sm text-gray-700">Cancelled/No Show</p>
            </div>
        </div>
    </div>

    <!-- Interviews Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('mentor.interviews', array_merge(request()->query(), ['sort' => 'scheduled_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                               class="flex items-center gap-1 hover:text-gray-700">
                                Date/Time
                                @if(request('sort', 'scheduled_at') === 'scheduled_at')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="{{ request('direction') === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
                                </svg>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fellow</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($interviews ?? [] as $interview)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $interview->scheduled_at?->format('M d, Y') ?? 'TBD' }}</p>
                                <p class="text-sm text-gray-500">{{ $interview->scheduled_at?->format('g:i A') ?? '' }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($interview->fellow->name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $interview->fellow->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500">{{ $interview->fellow->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ ($interview->type ?? '') === 'technical' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ ($interview->type ?? '') === 'behavioral' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ ($interview->type ?? '') === 'system_design' ? 'bg-green-100 text-green-800' : '' }}
                                {{ ($interview->type ?? '') === 'mock_interview' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ !in_array($interview->type ?? '', ['technical', 'behavioral', 'system_design', 'mock_interview']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $interview->type ?? 'general')) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                @if(($interview->mode ?? 'video') === 'video')
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                @elseif(($interview->mode ?? '') === 'phone')
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                @else
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                @endif
                                <span class="text-sm text-gray-600">{{ ucfirst($interview->mode ?? 'Video') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($interview->score)
                            <div class="flex items-center gap-2">
                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $interview->score >= 7 ? 'bg-green-500' : ($interview->score >= 5 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                         style="width: {{ ($interview->score / 10) * 100 }}%"></div>
                                </div>
                                <span class="text-sm font-medium {{ $interview->score >= 7 ? 'text-green-600' : ($interview->score >= 5 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ number_format($interview->score, 1) }}
                                </span>
                            </div>
                            @else
                            <span class="text-sm text-gray-400">--</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ ($interview->status ?? '') === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ ($interview->status ?? '') === 'completed' ? ($interview->feedback ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') : '' }}
                                {{ ($interview->status ?? '') === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ ($interview->status ?? '') === 'no_show' ? 'bg-red-100 text-red-800' : '' }}">
                                @if($interview->status === 'completed' && !$interview->feedback)
                                    Needs Review
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $interview->status ?? 'pending')) }}
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($interview->status === 'scheduled')
                                    @if($interview->meeting_link)
                                    <a href="{{ $interview->meeting_link }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                        Join
                                    </a>
                                    @endif
                                    <a href="{{ route('mentor.interviews.review', $interview->id) }}"
                                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                        Start
                                    </a>
                                @elseif($interview->status === 'completed' && !$interview->feedback)
                                    <a href="{{ route('mentor.interviews.review', $interview->id) }}"
                                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                                        Review
                                    </a>
                                @else
                                    <a href="{{ route('mentor.interviews.review', $interview->id) }}"
                                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                        View
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-gray-900 font-medium mb-1">No interviews found</h3>
                                <p class="text-gray-500 text-sm">Try adjusting your filters or set your availability to receive interview requests.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(($interviews ?? collect())->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $interviews->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function interviewList() {
    return {
        // Add any interactive state here
    }
}
</script>
@endsection
