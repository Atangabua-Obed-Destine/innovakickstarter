@extends('layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Attendance Management</h1>
            <p class="text-dark-400 mt-1">Track daily fellow attendance, manage sessions, and view reports.</p>
        </div>
        
        <div>
            @if($activeSession)
                <a href="{{ route('admin.attendance.show', $activeSession) }}" class="btn bg-primary-600 hover:bg-primary-700 text-white border-transparent">
                    View Active Session
                </a>
            @else
                <form action="{{ route('admin.attendance.store') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to start an attendance session for today?');">
                    @csrf
                    <button type="submit" class="btn bg-emerald-600 hover:bg-emerald-700 text-white border-transparent">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Start Today's Session
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Sessions List -->
    <div class="card border border-dark-700 bg-dark-800">
        <div class="px-6 py-5 border-b border-dark-700 flex justify-between items-center">
            <h2 class="font-semibold text-white">Recent Attendance Sessions</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-dark-900/50">
                        <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider">Opened By</th>
                        <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider">Closed By</th>
                        <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($sessions as $session)
                        <tr class="hover:bg-dark-700/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-white font-medium">{{ $session->date->format('M d, Y') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($session->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 mr-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-dark-500/10 text-dark-300 border border-dark-500/20">
                                        Closed
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-dark-300">
                                {{ optional($session->openedBy)->name ?? 'System' }} <br>
                                <span class="text-xs text-dark-500">{{ $session->opened_at->format('h:i A') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-dark-300">
                                @if($session->closed_at)
                                    {{ optional($session->closedBy)->name ?? 'System' }} <br>
                                    <span class="text-xs text-dark-500">{{ $session->closed_at->format('h:i A') }}</span>
                                @else
                                    <span class="text-dark-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.attendance.show', $session) }}" class="text-primary-400 hover:text-primary-300">
                                    {{ $session->status === 'active' ? 'View Live Board' : 'View Report' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-dark-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="text-lg font-medium text-white mb-1">No attendance sessions yet</p>
                                <p class="text-sm">Start a session to begin tracking daily attendance.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($sessions->hasPages())
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
