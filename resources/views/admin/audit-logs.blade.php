@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Audit Logs</h1>
            <p class="text-dark-400">Track system activity and administrative actions.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.audit-logs') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-dark-400 mb-1">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search actions, descriptions..."
                       class="w-full px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white placeholder-dark-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm text-dark-400 mb-1">Action</label>
                <select name="action" class="px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All Actions</option>
                    @foreach(['created', 'updated', 'deleted', 'approved', 'rejected', 'login', 'logout', 'enrolled', 'graduated'] as $action)
                        <option value="{{ $action }}" {{ ($filters['action'] ?? '') === $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-dark-400 mb-1">From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                       class="px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm text-dark-400 mb-1">To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                       class="px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.audit-logs') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <!-- Log Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dark-400 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dark-400 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dark-400 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dark-400 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dark-400 uppercase tracking-wider">Details</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-dark-400 uppercase tracking-wider">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-800">
                    @forelse($logs ?? collect() as $log)
                    <tr class="hover:bg-dark-800/50 transition-colors">
                        <td class="px-6 py-4 text-sm text-dark-300 whitespace-nowrap">{{ $log->created_at?->format('M j, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="text-white font-medium">{{ $log->admin?->name ?? 'System' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $actionColors = ['created' => 'green', 'updated' => 'blue', 'deleted' => 'red', 'approved' => 'green', 'rejected' => 'red', 'login' => 'teal', 'enrolled' => 'purple'];
                                $color = $actionColors[$log->action ?? ''] ?? 'gray';
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $color }}-600/20 text-{{ $color }}-400">{{ ucfirst($log->action ?? 'unknown') }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-dark-300">{{ $log->subject_type ?? '' }} {{ $log->subject_id ? '#'.Str::limit($log->subject_id, 8, '...') : '' }}</td>
                        <td class="px-6 py-4 text-sm text-dark-400 max-w-xs truncate">{{ $log->justification ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-dark-500 font-mono">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-dark-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-dark-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p>No audit logs found matching your filters.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($logs ?? collect(), 'links'))
        <div class="px-6 py-4 border-t border-dark-800">
            {{ $logs->appends($filters)->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
