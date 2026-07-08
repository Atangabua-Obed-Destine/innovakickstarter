@extends('layouts.app')

@section('title', 'Payment Verifications')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Payment Verifications</h1>
        <p class="text-dark-400">Review and approve fee payment receipts uploaded by fellows.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stat Tiles --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        @php
            $tiles = [
                ['label' => 'Pending Receipts',    'count' => $stats['pending'],              'color' => 'amber',   'desc' => 'Awaiting Verification'],
                ['label' => 'Approved Receipts',   'count' => $stats['approved'],             'color' => 'green',   'desc' => 'Verified & Approved'],
                ['label' => 'Rejected Receipts',   'count' => $stats['rejected'],             'color' => 'red',     'desc' => 'Verification Rejected'],
                ['label' => 'Pending Fees',        'count' => $stats['pending_fees'],         'color' => 'blue',    'desc' => 'Fee Payments'],
                ['label' => 'Pending Installments','count' => $stats['pending_installments'], 'color' => 'dark',    'desc' => 'Installment Payments'],
                ['label' => 'Total Receipts',      'count' => $stats['total_receipts'],       'color' => 'primary', 'desc' => 'All Submissions'],
            ];
        @endphp
        @foreach($tiles as $tile)
            <div class="card p-4 rounded-xl border-t-4 border-{{ $tile['color'] }}-500 bg-{{ $tile['color'] }}-900/10">
                <p class="text-dark-200 text-sm font-medium">{{ $tile['label'] }}</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $tile['count'] }}</p>
                <p class="text-{{ $tile['color'] }}-400/80 text-xs mt-2">{{ $tile['desc'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card p-4 mt-6">
        <form method="GET" action="{{ route('admin.payment-verifications.index') }}" class="flex flex-col lg:flex-row gap-3">
            <div class="flex-1">
                <label class="block text-xs text-dark-400 mb-1">Payment Type</label>
                <select name="payment_type" class="form-input w-full">
                    <option value="">All Types</option>
                    <option value="fee" {{ ($filters['payment_type'] ?? '') === 'fee' ? 'selected' : '' }}>Full Fee</option>
                    <option value="installment" {{ ($filters['payment_type'] ?? '') === 'installment' ? 'selected' : '' }}>Installment</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs text-dark-400 mb-1">Status</label>
                <select name="status" class="form-input w-full">
                    <option value="">Action Needed (Pending First)</option>
                    <option value="submitted" {{ ($filters['status'] ?? '') === 'submitted' ? 'selected' : '' }}>Pending</option>
                    <option value="verified" {{ ($filters['status'] ?? '') === 'verified' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs text-dark-400 mb-1">From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-input w-full">
            </div>
            <div class="flex-1">
                <label class="block text-xs text-dark-400 mb-1">To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-input w-full">
            </div>
            <div class="flex-1">
                <label class="block text-xs text-dark-400 mb-1">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or ref..." class="form-input w-full">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary h-10 px-6">Filter</button>
                @if(!empty(array_filter($filters ?? [])))
                    <a href="{{ route('admin.payment-verifications.index') }}" class="btn btn-outline h-10 px-4">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden mt-6">
        <h2 class="text-lg font-semibold text-white p-4 border-b border-dark-700">Payment Receipts List</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-blue-600/20 text-blue-300 border-b border-blue-500/30">
                        <th class="text-left p-3 text-xs uppercase font-semibold">#</th>
                        <th class="text-left p-3 text-xs uppercase font-semibold">Type</th>
                        <th class="text-left p-3 text-xs uppercase font-semibold">Student</th>
                        <th class="text-left p-3 text-xs uppercase font-semibold">Fees Type</th>
                        <th class="text-right p-3 text-xs uppercase font-semibold">Amount</th>
                        <th class="text-left p-3 text-xs uppercase font-semibold">Payment Reference</th>
                        <th class="text-left p-3 text-xs uppercase font-semibold">Payment Date</th>
                        <th class="text-center p-3 text-xs uppercase font-semibold">Status</th>
                        <th class="text-center p-3 text-xs uppercase font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $i => $payment)
                        <tr class="border-b border-dark-700/50 hover:bg-dark-800/50 transition-colors">
                            <td class="p-3 text-dark-400 text-sm">{{ $payments->firstItem() + $i }}</td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs font-medium bg-blue-600 text-white">
                                    {{ $payment->installment_id ? 'Installment' : 'Fee Payment' }}
                                </span>
                            </td>
                            <td class="p-3">
                                <p class="text-white text-sm font-medium">{{ $payment->fellow->name }}</p>
                                <p class="text-dark-400 text-xs">{{ $payment->fellow->username }}</p>
                            </td>
                            <td class="p-3 text-dark-300 text-sm">{{ $payment->fee->title }}</td>
                            <td class="p-3 text-right text-dark-200 text-sm font-medium">{{ number_format($payment->amount, 2) }} CFA</td>
                            <td class="p-3 text-dark-400 text-sm font-mono">{{ $payment->reference ?? '-' }}</td>
                            <td class="p-3 text-dark-300 text-sm">{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-1 rounded text-xs font-medium bg-{{ $payment->status_color }}-600/20 text-{{ $payment->status_color }}-400 border border-{{ $payment->status_color }}-500/30">
                                    {{ $payment->status_label }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <a href="{{ route('admin.payment-verifications.show', $payment) }}" class="btn btn-primary py-1 px-3 text-xs">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-dark-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-dark-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p>No payment receipts found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-dark-700">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
