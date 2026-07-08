@extends('layouts.app')

@section('title', 'Recruiter: ' . $recruiter->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <nav class="text-sm text-dark-400 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Dashboard</a>
                <span class="mx-2">›</span>
                <a href="{{ route('admin.recruiters.index') }}" class="hover:text-white">Recruiters</a>
                <span class="mx-2">›</span>
                <span class="text-dark-300">{{ $recruiter->name }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-white">{{ $recruiter->name }}</h1>
        </div>
        
        <div class="flex items-center space-x-3">
            @if($recruiter->suspended_at)
                <form action="{{ route('admin.recruiters.activate', $recruiter) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Activate Account
                    </button>
                </form>
            @elseif($recruiter->is_active)
                <button type="button" onclick="document.getElementById('suspendModal').classList.remove('hidden'); document.getElementById('suspendModal').classList.add('flex');"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Suspend Account
                </button>
            @else
                <form action="{{ route('admin.recruiters.approve', $recruiter) }}" method="POST">
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
                @if($recruiter->avatar_url)
                    <img class="h-24 w-24 rounded-full mx-auto object-cover" src="{{ $recruiter->avatar_url }}" alt="">
                @else
                    <div class="h-24 w-24 rounded-full bg-blue-500/20 mx-auto flex items-center justify-center">
                        <span class="text-3xl text-blue-400 font-medium">{{ substr($recruiter->name, 0, 1) }}</span>
                    </div>
                @endif
                
                <h2 class="mt-4 text-xl font-medium text-white">{{ $recruiter->name }}</h2>
                <p class="text-dark-400">{{ $recruiter->job_title ?? 'Recruiter' }}</p>
                
                @if($recruiter->company_name)
                    <p class="text-sm text-dark-400 mt-1">{{ $recruiter->company_name }}</p>
                @endif
                
                <div class="mt-4">
                    @if($recruiter->suspended_at)
                        <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full bg-red-500/20 text-red-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400 mr-1.5"></span>
                            Suspended
                        </span>
                    @elseif($recruiter->is_active)
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
                    <dd class="mt-1 text-sm text-white">{{ $recruiter->email }}</dd>
                </div>
                
                @if($recruiter->phone)
                    <div>
                        <dt class="text-sm font-medium text-dark-400">Phone</dt>
                        <dd class="mt-1 text-sm text-white">{{ $recruiter->phone }}</dd>
                    </div>
                @endif
                
                <div>
                    <dt class="text-sm font-medium text-dark-400">Joined</dt>
                    <dd class="mt-1 text-sm text-white">{{ $recruiter->created_at->format('F d, Y') }}</dd>
                </div>
                
                @if($recruiter->last_login_at)
                    <div>
                        <dt class="text-sm font-medium text-dark-400">Last Login</dt>
                        <dd class="mt-1 text-sm text-white">{{ $recruiter->last_login_at->diffForHumans() }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- Activity & Subscription -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Activity Stats -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-white mb-4">Activity</h3>
                
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-dark-800 rounded-lg">
                        <p class="text-3xl font-bold text-violet-400">{{ $activity['profile_views'] }}</p>
                        <p class="text-sm text-dark-400">Profile Views</p>
                    </div>
                    <div class="text-center p-4 bg-dark-800 rounded-lg">
                        <p class="text-3xl font-bold text-blue-400">{{ $activity['contacts_made'] }}</p>
                        <p class="text-sm text-dark-400">Contacts Made</p>
                    </div>
                    <div class="text-center p-4 bg-dark-800 rounded-lg">
                        <p class="text-3xl font-bold text-green-400">{{ $activity['shortlisted'] }}</p>
                        <p class="text-sm text-dark-400">Shortlisted</p>
                    </div>
                </div>
            </div>

            <!-- Subscription Info -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-white mb-4">Subscription</h3>
                
                @if($recruiter->subscription)
                    @php $sub = $recruiter->subscription; @endphp
                    
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-lg font-medium text-white">{{ ucfirst($sub->tier) }} Plan</span>
                            @if($sub->status === 'active')
                                <span class="ml-2 inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1"></span>Active
                                </span>
                            @elseif($sub->status === 'trial')
                                <span class="ml-2 inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-blue-500/20 text-blue-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-1"></span>Trial
                                </span>
                            @else
                                <span class="ml-2 inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-dark-600 text-dark-300">
                                    {{ ucfirst($sub->status) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-dark-400">Started</dt>
                            <dd class="mt-1 text-sm text-white">{{ $sub->created_at->format('M d, Y') }}</dd>
                        </div>
                        
                        @if($sub->expires_at)
                            <div>
                                <dt class="text-sm font-medium text-dark-400">Expires</dt>
                                <dd class="mt-1 text-sm text-white">{{ $sub->expires_at->format('M d, Y') }}</dd>
                            </div>
                        @endif
                        
                        <div>
                            <dt class="text-sm font-medium text-dark-400">Profile Views Used</dt>
                            <dd class="mt-1 text-sm text-white">{{ $sub->profile_views_used }} / {{ $sub->profile_views_limit ?? '∞' }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-dark-400">Contacts Used</dt>
                            <dd class="mt-1 text-sm text-white">{{ $sub->direct_contacts_used }} / {{ $sub->direct_contacts_limit ?? '∞' }}</dd>
                        </div>
                    </dl>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <p class="mt-2 text-dark-400">No active subscription</p>
                    </div>
                @endif
            </div>

            <!-- Suspension Info -->
            @if($recruiter->suspended_at)
                <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-red-400 mb-2">Account Suspended</h3>
                    <p class="text-sm text-red-400/80">Suspended on {{ $recruiter->suspended_at->format('F d, Y') }}</p>
                    @if($recruiter->suspension_reason)
                        <p class="mt-2 text-sm text-red-300"><strong>Reason:</strong> {{ $recruiter->suspension_reason }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Suspend Modal -->
<div id="suspendModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="card max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-medium text-white mb-4">Suspend Recruiter</h3>
        <p class="text-dark-400 mb-4">Are you sure you want to suspend {{ $recruiter->name }}?</p>
        
        <form action="{{ route('admin.recruiters.suspend', $recruiter) }}" method="POST">
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
