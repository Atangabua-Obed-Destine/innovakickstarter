@extends('layouts.app')

@section('title', 'My Attendance Records')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">My Attendance</h1>
        <p class="text-dark-400 mt-1">View your daily clock-in records and attendance status.</p>
    </div>

    <div class="card border border-dark-700 bg-dark-800">
        <div class="px-6 py-5 border-b border-dark-700">
            <h2 class="font-semibold text-white">Attendance History</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-dark-900/50">
                        <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider">Clock In</th>
                        <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider">Clock Out</th>
                        <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($records as $record)
                        <tr class="hover:bg-dark-700/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-white font-medium">{{ $record->session->date->format('M d, Y') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-dark-300">
                                {{ $record->clock_in_time ? $record->clock_in_time->format('h:i:s A') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-dark-300">
                                {{ $record->clock_out_time ? $record->clock_out_time->format('h:i:s A') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($record->status === 'present')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Present</span>
                                @elseif($record->status === 'absent')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">Absent</span>
                                @elseif($record->status === 'late')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">Late</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">On Leave</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-dark-400 max-w-[200px] truncate">
                                {{ $record->admin_notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-dark-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-lg font-medium text-white mb-1">No attendance records found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($records->hasPages())
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
