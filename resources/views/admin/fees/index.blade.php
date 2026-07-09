@extends('layouts.app')

@section('title', 'Fee Reports')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Fee Reports</h1>
            <p class="text-dark-400">Manage and track all fellow fees, payments, and balances.</p>
        </div>
        <a href="{{ route('admin.fees.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Assign New Fee
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stat Tiles --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @php
            $tiles = [
                ['label' => 'Total Fees',      'count' => $stats['total_fees'],     'color' => 'primary', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['label' => 'Fully Paid',      'count' => $stats['fully_paid'],     'color' => 'green',   'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'Partially Paid',  'count' => $stats['partially_paid'], 'color' => 'amber',   'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'Overdue',         'count' => $stats['overdue'],        'color' => 'red',     'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z'],
                ['label' => 'Waived',          'count' => $stats['waived'],         'color' => 'dark',    'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
            ];
        @endphp
        @foreach($tiles as $tile)
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-{{ $tile['color'] }}-600/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-{{ $tile['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tile['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-dark-400 text-xs">{{ $tile['label'] }}</p>
                        <p class="text-xl font-bold text-white">{{ $tile['count'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Summary Band --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="rounded-xl bg-gradient-to-br from-blue-600/30 to-blue-800/20 border border-blue-500/30 p-5">
            <p class="text-blue-300 text-sm font-medium">Total Assigned</p>
            <p class="text-2xl font-bold text-white mt-1">{{ number_format($stats['total_assigned'], 2) }} CFA</p>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-green-600/30 to-green-800/20 border border-green-500/30 p-5">
            <p class="text-green-300 text-sm font-medium">Total Collected</p>
            <p class="text-2xl font-bold text-white mt-1">{{ number_format($stats['total_collected'], 2) }} CFA</p>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-amber-600/30 to-amber-800/20 border border-amber-500/30 p-5">
            <p class="text-amber-300 text-sm font-medium">Outstanding Balance</p>
            <p class="text-2xl font-bold text-white mt-1">{{ number_format($stats['outstanding'], 2) }} CFA</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.fees.index') }}" class="flex flex-col lg:flex-row gap-3">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="Search fellow, reference or title..."
                   class="form-input flex-1">
            <select name="plan_type" class="form-input w-full lg:w-40">
                <option value="">All Types</option>
                <option value="one_time" {{ ($filters['plan_type'] ?? '') === 'one_time' ? 'selected' : '' }}>One-Time</option>
                <option value="installments" {{ ($filters['plan_type'] ?? '') === 'installments' ? 'selected' : '' }}>Installments</option>
            </select>
            <select name="status" class="form-input w-full lg:w-40">
                <option value="">All Statuses</option>
                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="partially_paid" {{ ($filters['status'] ?? '') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>Fully Paid</option>
                <option value="overdue" {{ ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                <option value="waived" {{ ($filters['status'] ?? '') === 'waived' ? 'selected' : '' }}>Waived</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(!empty(array_filter($filters ?? [])))
                <a href="{{ route('admin.fees.index') }}" class="btn btn-outline">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-dark-700">
                        <th class="text-left p-4 text-dark-400 text-xs uppercase font-semibold">#</th>
                        <th class="text-left p-4 text-dark-400 text-xs uppercase font-semibold">Reference</th>
                        <th class="text-left p-4 text-dark-400 text-xs uppercase font-semibold">Fellow</th>
                        <th class="text-left p-4 text-dark-400 text-xs uppercase font-semibold">Fee Title</th>
                        <th class="text-left p-4 text-dark-400 text-xs uppercase font-semibold">Due Date</th>
                        <th class="text-right p-4 text-dark-400 text-xs uppercase font-semibold">Amount</th>
                        <th class="text-right p-4 text-dark-400 text-xs uppercase font-semibold">Paid</th>
                        <th class="text-right p-4 text-dark-400 text-xs uppercase font-semibold">Balance</th>
                        <th class="text-center p-4 text-dark-400 text-xs uppercase font-semibold">Status</th>
                        <th class="text-center p-4 text-dark-400 text-xs uppercase font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fees as $i => $fee)
                        <tr class="border-b border-dark-700/50 hover:bg-dark-800/50 transition-colors">
                            <td class="p-4 text-dark-400 text-sm">{{ $fees->firstItem() + $i }}</td>
                            <td class="p-4 text-dark-300 text-sm font-mono">{{ $fee->reference }}</td>
                            <td class="p-4">
                                <div>
                                    <p class="text-white text-sm font-medium">{{ $fee->fellow?->name ?? 'N/A' }}</p>
                                    <p class="text-dark-500 text-xs">{{ $fee->fellow?->email }}</p>
                                </div>
                            </td>
                            <td class="p-4">
                                <p class="text-dark-200 text-sm">{{ $fee->title }}</p>
                                <p class="text-dark-500 text-xs">{{ ucfirst(str_replace('_', ' ', $fee->plan_type)) }}</p>
                            </td>
                            <td class="p-4">
                                <p class="text-dark-200 text-sm whitespace-nowrap">{{ $fee->first_due_date?->format('M j, Y') ?? 'N/A' }}</p>
                                @if($fee->is_overdue)
                                    <span class="text-red-400 text-[10px] uppercase font-bold tracking-wider">Overdue</span>
                                @endif
                            </td>
                            <td class="p-4 text-right text-dark-200 text-sm">{{ $fee->formatted_total }}</td>
                            <td class="p-4 text-right text-green-400 text-sm font-medium">{{ $fee->formatted_paid }}</td>
                            <td class="p-4 text-right text-sm {{ $fee->balance > 0 ? 'text-amber-400' : 'text-dark-500' }}">{{ $fee->formatted_balance }}</td>
                            <td class="p-4 text-center">
                                <span class="badge bg-{{ $fee->status_color }}-600/20 text-{{ $fee->status_color }}-400 border-{{ $fee->status_color }}-500/30">
                                    {{ $fee->status_label }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center gap-2" x-data="{ showPaymentModal: false }">
                                    <a href="{{ route('admin.fees.show', $fee) }}" class="p-1.5 rounded-lg bg-primary-600/20 text-primary-400 hover:bg-primary-600/40 transition-colors" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    
                                    @if($fee->balance > 0 && $fee->status !== \App\Models\Fee::STATUS_WAIVED)
                                        <button @click="showPaymentModal = true" type="button" class="p-1.5 rounded-lg bg-green-600/20 text-green-400 hover:bg-green-600/40 transition-colors" title="Record Payment">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </button>
                                        
                                        {{-- We include the modal right here. Because it uses fixed positioning, it overlays the whole screen correctly. --}}
                                        <div class="text-left">
                                            @include('admin.fees._payment-modal', ['fee' => $fee])
                                        </div>
                                    @endif
                                    
                                    @if($fee->amount_paid == 0 && $fee->payments()->count() === 0)
                                        <form action="{{ route('admin.fees.destroy', $fee) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this fee? This action cannot be undone.');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg bg-red-600/20 text-red-400 hover:bg-red-600/40 transition-colors" title="Delete Fee">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-dark-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-dark-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p>No fees found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($fees->hasPages())
            <div class="p-4 border-t border-dark-700">
                {{ $fees->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
