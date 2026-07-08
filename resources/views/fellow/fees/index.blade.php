@extends('layouts.app')

@section('title', 'My Fees')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">My Fees & Payments</h1>
        <p class="text-dark-400">Track your assigned fees, upload payment receipts, and download official receipts.</p>
    </div>

    {{-- Summary Band --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="rounded-xl bg-gradient-to-br from-blue-600/20 to-blue-800/10 border border-blue-500/20 p-5">
            <p class="text-blue-400 text-sm font-medium">Total Fees Assigned</p>
            <p class="text-2xl font-bold text-white mt-1">{{ number_format($summary['total_assigned'], 2) }} CFA</p>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-green-600/20 to-green-800/10 border border-green-500/20 p-5">
            <p class="text-green-400 text-sm font-medium">Total Paid</p>
            <p class="text-2xl font-bold text-white mt-1">{{ number_format($summary['total_paid'], 2) }} CFA</p>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-amber-600/20 to-amber-800/10 border border-amber-500/20 p-5">
            <p class="text-amber-400 text-sm font-medium">Outstanding Balance</p>
            <p class="text-2xl font-bold text-white mt-1">{{ number_format($summary['outstanding'], 2) }} CFA</p>
        </div>
    </div>

    {{-- Fees List --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($fees as $fee)
            <div class="card p-6 flex flex-col relative overflow-hidden group hover:border-primary-500/50 transition-colors">
                
                {{-- Overdue visual marker --}}
                @if($fee->is_overdue)
                    <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
                @endif

                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-white">{{ $fee->title }}</h3>
                        <p class="text-dark-400 text-sm">{{ $fee->billable_label }}</p>
                    </div>
                    <span class="badge bg-{{ $fee->status_color }}-600/20 text-{{ $fee->status_color }}-400 border-{{ $fee->status_color }}-500/30 whitespace-nowrap">
                        {{ $fee->status_label }}
                    </span>
                </div>

                <div class="space-y-3 mb-6 flex-1">
                    <div class="flex justify-between items-center py-2 border-b border-dark-700">
                        <span class="text-dark-300 text-sm">Total Amount</span>
                        <span class="text-white font-medium">{{ $fee->formatted_total }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-dark-700">
                        <span class="text-dark-300 text-sm">Paid</span>
                        <span class="text-green-400 font-medium">{{ $fee->formatted_paid }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-dark-300 text-sm">Balance</span>
                        <span class="{{ $fee->balance > 0 ? 'text-amber-400 font-bold' : 'text-dark-400 font-medium' }}">{{ $fee->formatted_balance }}</span>
                    </div>
                </div>

                <div class="mt-auto space-y-3">
                    @if($fee->is_overdue)
                        <div class="p-2 bg-red-500/10 border border-red-500/20 rounded text-red-400 text-xs flex items-start gap-2">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            <p>Payment was due on {{ $fee->final_due_date->format('M j, Y') }}. Please settle your balance.</p>
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <a href="{{ route('fees.show', $fee) }}" class="btn btn-outline flex-1 justify-center text-sm py-2">View Details</a>
                        @if($fee->balance > 0 && $fee->status !== \App\Models\Fee::STATUS_WAIVED)
                            <a href="{{ route('fees.upload', $fee) }}" class="btn btn-primary flex-1 justify-center text-sm py-2">Upload Receipt</a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full card p-12 text-center text-dark-500">
                <div class="w-16 h-16 rounded-full bg-dark-800 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-white text-lg font-medium mb-1">No Fees Assigned</p>
                <p>You have no pending or paid fees assigned to your account at this time.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
