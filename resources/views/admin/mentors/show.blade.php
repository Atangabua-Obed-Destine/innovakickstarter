@extends('layouts.app')

@section('title', 'Mentor: ' . $mentor->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <nav class="text-sm text-dark-400 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Dashboard</a>
                <span class="mx-2">›</span>
                <a href="{{ route('admin.mentors.index') }}" class="hover:text-white">Mentors</a>
                <span class="mx-2">›</span>
                <span class="text-dark-300">{{ $mentor->name }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-white">{{ $mentor->name }}</h1>
        </div>
        
        <div class="flex items-center space-x-3">
            @if($mentor->suspended_at)
                <form action="{{ route('admin.mentors.activate', $mentor) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Activate Account
                    </button>
                </form>
            @elseif($mentor->is_active)
                <button type="button" onclick="document.getElementById('suspendModal').classList.remove('hidden'); document.getElementById('suspendModal').classList.add('flex');"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Suspend Account
                </button>
            @else
                <form action="{{ route('admin.mentors.approve', $mentor) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Approve Account
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="card p-6">
            <div class="text-center">
                @if($mentor->avatar_url)
                    <img class="h-24 w-24 rounded-full mx-auto object-cover" src="{{ $mentor->avatar_url }}" alt="">
                @else
                    <div class="h-24 w-24 rounded-full bg-violet-500/20 mx-auto flex items-center justify-center">
                        <span class="text-3xl text-violet-400 font-medium">{{ substr($mentor->name, 0, 1) }}</span>
                    </div>
                @endif
                
                <h2 class="mt-4 text-xl font-medium text-white">{{ $mentor->name }}</h2>
                <p class="text-dark-400">{{ $mentor->job_title ?? 'Industry Mentor' }}</p>
                
                @if($mentor->company_name)
                    <p class="text-sm text-dark-400 mt-1">{{ $mentor->company_name }}</p>
                @endif
                
                <div class="mt-4">
                    @if($mentor->suspended_at)
                        <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full bg-red-500/20 text-red-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400 mr-1.5"></span>
                            Suspended
                        </span>
                    @elseif($mentor->is_active)
                        <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full bg-green-500/20 text-green-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1.5"></span>
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full bg-yellow-500/20 text-yellow-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 mr-1.5"></span>
                            Pending Approval
                        </span>
                    @endif
                </div>
            </div>
            
            <hr class="my-6 border-dark-700">
            
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-dark-400">Email</dt>
                    <dd class="mt-1 text-sm text-white">{{ $mentor->email }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-dark-400">Joined</dt>
                    <dd class="mt-1 text-sm text-white">{{ $mentor->created_at->format('F d, Y') }}</dd>
                </div>
                
                @if($mentor->expertise && count($mentor->expertise) > 0)
                    <div>
                        <dt class="text-sm font-medium text-dark-400">Expertise</dt>
                        <dd class="mt-2 flex flex-wrap gap-1">
                            @foreach($mentor->expertise as $skill)
                                <span class="inline-flex px-2 py-0.5 text-xs bg-violet-500/20 text-violet-400 rounded">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- Stats & Interviews -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Interview Stats -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-white mb-4">Interview Statistics</h3>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-dark-800 rounded-lg">
                        <p class="text-3xl font-bold text-violet-400">{{ $stats['total_interviews'] }}</p>
                        <p class="text-sm text-dark-400">Total Interviews</p>
                    </div>
                    <div class="text-center p-4 bg-dark-800 rounded-lg">
                        <p class="text-3xl font-bold text-green-400">{{ $stats['completed_interviews'] }}</p>
                        <p class="text-sm text-dark-400">Completed</p>
                    </div>
                    <div class="text-center p-4 bg-dark-800 rounded-lg">
                        <p class="text-3xl font-bold text-blue-400">{{ $stats['upcoming_interviews'] }}</p>
                        <p class="text-sm text-dark-400">Upcoming</p>
                    </div>
                    <div class="text-center p-4 bg-dark-800 rounded-lg">
                        <div class="flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <span class="text-3xl font-bold text-white ml-1">{{ number_format($stats['average_rating'], 1) }}</span>
                        </div>
                        <p class="text-sm text-dark-400">Avg Rating</p>
                    </div>
                </div>
            </div>

            <!-- Recent Interviews -->
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h3 class="text-lg font-medium text-white">Recent Interviews</h3>
                </div>
                
                @if($recentInterviews->isNotEmpty())
                    <table class="min-w-full divide-y divide-dark-700">
                        <thead class="bg-dark-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">Fellow</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">Track</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">Score</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-700">
                            @foreach($recentInterviews as $interview)
                                <tr class="hover:bg-dark-800/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-dark-600 flex items-center justify-center">
                                                <span class="text-sm text-dark-300">{{ substr($interview->fellow->name ?? 'N', 0, 1) }}</span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-white">{{ $interview->fellow->name ?? 'Unknown' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-dark-400">
                                        {{ $interview->track->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-dark-400">
                                        {{ $interview->scheduled_at?->format('M d, Y') ?? 'Not scheduled' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'completed' => 'bg-green-500/20 text-green-400',
                                                'scheduled' => 'bg-blue-500/20 text-blue-400',
                                                'in_progress' => 'bg-yellow-500/20 text-yellow-400',
                                                'cancelled' => 'bg-red-500/20 text-red-400',
                                            ];
                                            $status = $interview->status->value ?? $interview->status;
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$status] ?? 'bg-dark-600 text-dark-300' }}">
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                        @if($interview->overall_score)
                                            {{ $interview->overall_score }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="mt-2 text-dark-400">No interviews yet</p>
                    </div>
                @endif
            </div>

            <!-- Suspension Info -->
            @if($mentor->suspended_at)
                <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-red-400 mb-2">Account Suspended</h3>
                    <p class="text-sm text-red-400/80">Suspended on {{ $mentor->suspended_at->format('F d, Y') }}</p>
                    @if($mentor->suspension_reason)
                        <p class="mt-2 text-sm text-red-300"><strong>Reason:</strong> {{ $mentor->suspension_reason }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Suspend Modal -->
<div id="suspendModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="card max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-medium text-white mb-4">Suspend Mentor</h3>
        <p class="text-dark-400 mb-4">Are you sure you want to suspend {{ $mentor->name }}?</p>
        
        <form action="{{ route('admin.mentors.suspend', $mentor) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="reason" class="form-label">Reason</label>
                <textarea name="reason" id="reason" rows="3" required
                    class="form-input"
                    placeholder="Enter reason for suspension..."></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('suspendModal').classList.add('hidden'); document.getElementById('suspendModal').classList.remove('flex');" class="px-4 py-2 text-dark-400 hover:text-white transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Suspend
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
