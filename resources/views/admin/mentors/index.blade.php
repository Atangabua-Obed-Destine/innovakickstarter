@extends('layouts.app')

@section('title', 'Mentor Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Mentor Management</h1>
            <p class="text-dark-400 mt-1">Manage mentor accounts and interview assignments</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                    <p class="text-dark-400 text-sm">Total Mentors</p>
                </div>
            </div>
        </div>
        
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['active'] }}</p>
                    <p class="text-dark-400 text-sm">Active</p>
                </div>
            </div>
        </div>
        
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['pending_approval'] }}</p>
                    <p class="text-dark-400 text-sm">Pending Approval</p>
                </div>
            </div>
        </div>
        
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['total_interviews'] }}</p>
                    <p class="text-dark-400 text-sm">Total Interviews</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-dark-400 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}"
                    class="w-full rounded-md bg-dark-800 border-dark-600 text-white placeholder-dark-500 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    placeholder="Name or email...">
            </div>
            
            <div class="w-40">
                <label for="status" class="block text-sm font-medium text-dark-400 mb-1">Status</label>
                <select name="status" id="status" class="w-full rounded-md bg-dark-800 border-dark-600 text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">All</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                Filter
            </button>
            
            @if(!empty($filters['search']) || !empty($filters['status']))
                <a href="{{ route('admin.mentors.index') }}" class="px-4 py-2 text-dark-400 hover:text-white transition">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Mentors Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800">
                    <tr>
                        <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Mentor</th>
                        <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Expertise</th>
                        <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Interviews</th>
                        <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Rating</th>
                        <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Status</th>
                        <th class="text-right py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($mentors as $mentor)
                        <tr class="hover:bg-dark-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        @if($mentor->avatar_url)
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ $mentor->avatar_url }}" alt="">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-primary-500/20 flex items-center justify-center">
                                                <span class="text-primary-400 font-medium">{{ substr($mentor->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-white">{{ $mentor->name }}</div>
                                        <div class="text-sm text-dark-400">{{ $mentor->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @if($mentor->expertise)
                                        @foreach(array_slice($mentor->expertise ?? [], 0, 3) as $skill)
                                            <span class="inline-flex px-2 py-0.5 text-xs bg-dark-700 text-dark-300 rounded">
                                                {{ $skill }}
                                            </span>
                                        @endforeach
                                        @if(count($mentor->expertise ?? []) > 3)
                                            <span class="text-xs text-dark-500">+{{ count($mentor->expertise) - 3 }}</span>
                                        @endif
                                    @else
                                        <span class="text-dark-500 text-sm">Not specified</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                {{ $mentor->conducted_interviews_count ?? 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                    <span class="ml-1 text-sm text-dark-300">{{ number_format($mentor->average_rating ?? 0, 1) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($mentor->suspended_at)
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-red-500/20 text-red-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 mr-1.5"></span>
                                        Suspended
                                    </span>
                                @elseif($mentor->is_active)
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-green-500/20 text-green-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1.5"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-amber-500/20 text-amber-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5"></span>
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.mentors.show', $mentor) }}" 
                                        class="text-primary-400 hover:text-primary-300 transition">View</a>
                                    
                                    @if(!$mentor->is_active && !$mentor->suspended_at)
                                        <form action="{{ route('admin.mentors.approve', $mentor) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-400 hover:text-green-300 transition">Approve</button>
                                        </form>
                                    @endif
                                    
                                    @if($mentor->is_active)
                                        <button type="button" onclick="openSuspendModal({{ $mentor->id }}, '{{ $mentor->name }}')"
                                            class="text-red-400 hover:text-red-300 transition">Suspend</button>
                                    @elseif($mentor->suspended_at)
                                        <form action="{{ route('admin.mentors.activate', $mentor) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-400 hover:text-green-300 transition">Activate</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 mx-auto rounded-full bg-dark-700 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-dark-400">No mentors found</p>
                                <p class="mt-1 text-sm text-dark-500">Mentors will appear here once they register.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mentors->hasPages())
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $mentors->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Suspend Modal -->
<div id="suspendModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50">
    <div class="card max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-medium text-white mb-4">Suspend Mentor</h3>
        <p class="text-dark-400 mb-4">Are you sure you want to suspend <span id="suspendName" class="font-medium text-white"></span>?</p>
        
        <form id="suspendForm" method="POST">
            @csrf
            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-dark-400 mb-1">Reason</label>
                <textarea name="reason" id="reason" rows="3" required
                    class="w-full rounded-md bg-dark-800 border-dark-600 text-white placeholder-dark-500 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    placeholder="Enter reason for suspension..."></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeSuspendModal()" class="px-4 py-2 text-dark-400 hover:text-white transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Suspend
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openSuspendModal(id, name) {
        document.getElementById('suspendName').textContent = name;
        document.getElementById('suspendForm').action = `/innovakickstarter/public/admin/mentors/${id}/suspend`;
        document.getElementById('suspendModal').classList.remove('hidden');
        document.getElementById('suspendModal').classList.add('flex');
    }
    
    function closeSuspendModal() {
        document.getElementById('suspendModal').classList.add('hidden');
        document.getElementById('suspendModal').classList.remove('flex');
    }
</script>
@endpush
@endsection
