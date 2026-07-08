@extends('layouts.app')

@section('title', 'Recruiter Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Recruiter Management</h1>
            <p class="text-dark-400 mt-1">Manage recruiter accounts and subscriptions</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                    <p class="text-dark-400 text-sm">Total</p>
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
                    <p class="text-dark-400 text-sm">Pending</p>
                </div>
            </div>
        </div>
        
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['suspended'] }}</p>
                    <p class="text-dark-400 text-sm">Suspended</p>
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
                    placeholder="Name, email, or company...">
            </div>
            
            <div class="w-40">
                <label for="status" class="block text-sm font-medium text-dark-400 mb-1">Status</label>
                <select name="status" id="status" class="w-full rounded-md bg-dark-800 border-dark-600 text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">All</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <div class="w-40">
                <label for="subscription" class="block text-sm font-medium text-dark-400 mb-1">Subscription</label>
                <select name="subscription" id="subscription" class="w-full rounded-md bg-dark-800 border-dark-600 text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">All</option>
                    <option value="trial" {{ ($filters['subscription'] ?? '') === 'trial' ? 'selected' : '' }}>Trial</option>
                    <option value="free" {{ ($filters['subscription'] ?? '') === 'free' ? 'selected' : '' }}>Free</option>
                    <option value="partner" {{ ($filters['subscription'] ?? '') === 'partner' ? 'selected' : '' }}>Partner</option>
                    <option value="premium" {{ ($filters['subscription'] ?? '') === 'premium' ? 'selected' : '' }}>Premium</option>
                </select>
            </div>
            
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                Filter
            </button>
            
            @if(!empty($filters['search']) || !empty($filters['status']) || !empty($filters['subscription']))
                <a href="{{ route('admin.recruiters.index') }}" class="px-4 py-2 text-dark-400 hover:text-white transition">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Recruiters Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800">
                    <tr>
                        <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Recruiter</th>
                        <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Company</th>
                        <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Subscription</th>
                        <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Status</th>
                        <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Joined</th>
                        <th class="text-right py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($recruiters as $recruiter)
                        <tr class="hover:bg-dark-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        @if($recruiter->avatar_url)
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ $recruiter->avatar_url }}" alt="">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-primary-500/20 flex items-center justify-center">
                                                <span class="text-primary-400 font-medium">{{ substr($recruiter->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-white">{{ $recruiter->name }}</div>
                                        <div class="text-sm text-dark-400">{{ $recruiter->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-white">{{ $recruiter->company_name ?? '-' }}</div>
                                <div class="text-sm text-dark-400">{{ $recruiter->job_title ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($recruiter->subscription)
                                    @php
                                        $tier = $recruiter->subscription->tier;
                                        $colors = [
                                            'trial' => 'bg-dark-600 text-dark-300',
                                            'free' => 'bg-gray-500/20 text-gray-400',
                                            'partner' => 'bg-blue-500/20 text-blue-400',
                                            'premium' => 'bg-amber-500/20 text-amber-400',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full {{ $colors[$tier] ?? 'bg-dark-600 text-dark-300' }}">
                                        {{ ucfirst($tier) }}
                                    </span>
                                @else
                                    <span class="text-dark-500 text-sm">No subscription</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($recruiter->suspended_at)
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-red-500/20 text-red-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 mr-1.5"></span>
                                        Suspended
                                    </span>
                                @elseif($recruiter->is_active)
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-dark-400">
                                {{ $recruiter->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.recruiters.show', $recruiter) }}" 
                                        class="text-primary-400 hover:text-primary-300 transition">View</a>
                                    
                                    @if(!$recruiter->is_active && !$recruiter->suspended_at)
                                        <form action="{{ route('admin.recruiters.approve', $recruiter) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-400 hover:text-green-300 transition">Approve</button>
                                        </form>
                                    @endif
                                    
                                    @if($recruiter->is_active)
                                        <button type="button" onclick="openSuspendModal({{ $recruiter->id }}, '{{ $recruiter->name }}')"
                                            class="text-red-400 hover:text-red-300 transition">Suspend</button>
                                    @elseif($recruiter->suspended_at)
                                        <form action="{{ route('admin.recruiters.activate', $recruiter) }}" method="POST" class="inline">
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-dark-400">No recruiters found</p>
                                <p class="mt-1 text-sm text-dark-500">Recruiters will appear here once they register.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recruiters->hasPages())
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $recruiters->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Suspend Modal -->
<div id="suspendModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50">
    <div class="card max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-medium text-white mb-4">Suspend Recruiter</h3>
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
        document.getElementById('suspendForm').action = `/innovakickstarter/public/admin/recruiters/${id}/suspend`;
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
