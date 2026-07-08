@extends('layouts.app')

@section('title', 'Fee Details')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <nav class="text-sm text-dark-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-white">Dashboard</a>
            <span class="mx-2">›</span>
            <a href="{{ route('fees.index') }}" class="hover:text-white">My Fees</a>
            <span class="mx-2">›</span>
            <span class="text-dark-300">Fee Details</span>
        </nav>
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white">{{ $fee->title }}</h1>
                <span class="badge bg-{{ $fee->status_color }}-600/20 text-{{ $fee->status_color }}-400 border-{{ $fee->status_color }}-500/30">
                    {{ $fee->status_label }}
                </span>
            </div>
            
            @if($fee->balance > 0 && $fee->status !== \App\Models\Fee::STATUS_WAIVED)
                <a href="{{ route('fees.upload', $fee) }}" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Upload Receipt
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4 text-amber-400">
            {{ session('warning') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left Column: Summary --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Financial Summary</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 rounded-lg bg-dark-800/50">
                        <span class="text-dark-400 text-sm">Total Amount</span>
                        <span class="text-white font-medium">{{ $fee->formatted_total }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 rounded-lg bg-green-500/10 border border-green-500/20">
                        <span class="text-green-400 text-sm">Total Paid</span>
                        <span class="text-green-400 font-medium">{{ $fee->formatted_paid }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 rounded-lg {{ $fee->balance > 0 ? 'bg-amber-500/10 border border-amber-500/20' : 'bg-dark-800/50' }}">
                        <span class="{{ $fee->balance > 0 ? 'text-amber-400' : 'text-dark-400' }} text-sm font-medium">Remaining Balance</span>
                        <span class="{{ $fee->balance > 0 ? 'text-amber-400 font-bold' : 'text-dark-400 font-medium' }}">{{ $fee->formatted_balance }}</span>
                    </div>

                    <div class="pt-4 border-t border-dark-700 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-dark-400 text-sm">Linked To</span>
                            <span class="text-white text-sm">{{ $fee->billable_label }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-dark-400 text-sm">First Due Date</span>
                            <span class="text-white text-sm">{{ $fee->first_due_date->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-dark-400 text-sm">Final Due Date</span>
                            <span class="text-{{ $fee->is_overdue ? 'red' : 'white' }} text-sm">{{ $fee->final_due_date->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>

                @if($fee->description)
                    <div class="mt-4 pt-4 border-t border-dark-700">
                        <h3 class="text-sm font-medium text-dark-300 mb-2">Description</h3>
                        <p class="text-dark-400 text-sm">{{ $fee->description }}</p>
                    </div>
                @endif
            </div>

            @if($fee->status === \App\Models\Fee::STATUS_WAIVED)
                <div class="card p-6 border-l-4 border-l-dark-500">
                    <h2 class="text-lg font-semibold text-white mb-2">Fee Waived</h2>
                    <p class="text-dark-300 text-sm mb-3">This fee has been waived by the administration. You do not owe any further balance.</p>
                </div>
            @endif
        </div>

        {{-- Right Column: Installments & Payments --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Installments Table --}}
            @if($fee->plan_type === 'installments' && $fee->installments->count() > 0)
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Payment Plan</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-dark-700">
                                    <th class="py-3 text-dark-400 text-xs uppercase font-semibold">Installment</th>
                                    <th class="py-3 text-dark-400 text-xs uppercase font-semibold text-right">Amount Due</th>
                                    <th class="py-3 text-dark-400 text-xs uppercase font-semibold text-right">Paid</th>
                                    <th class="py-3 text-dark-400 text-xs uppercase font-semibold text-right">Due Date</th>
                                    <th class="py-3 text-dark-400 text-xs uppercase font-semibold text-center">Status</th>
                                    <th class="py-3 text-dark-400 text-xs uppercase font-semibold text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fee->installments as $installment)
                                    <tr class="border-b border-dark-700/50 hover:bg-dark-800/50 transition-colors">
                                        <td class="py-3 text-white text-sm font-medium">{{ $installment->label }}</td>
                                        <td class="py-3 text-dark-300 text-sm text-right">{{ number_format($installment->amount_due, 2) }}</td>
                                        <td class="py-3 text-green-400 text-sm text-right font-medium">{{ number_format($installment->amount_paid, 2) }}</td>
                                        <td class="py-3 text-{{ $installment->is_overdue ? 'red-400 font-medium' : 'dark-300' }} text-sm text-right">{{ $installment->due_date->format('M j, Y') }}</td>
                                        <td class="py-3 text-center">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $installment->status_color }}-600/20 text-{{ $installment->status_color }}-400">
                                                {{ $installment->status_label }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-center">
                                            @if($installment->balance > 0 && $fee->status !== \App\Models\Fee::STATUS_WAIVED)
                                                <a href="{{ route('fees.upload', ['fee' => $fee, 'installment' => $installment->id]) }}" class="text-primary-400 hover:text-primary-300 text-xs font-medium">Pay</a>
                                            @else
                                                <span class="text-dark-500 text-xs">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Payments History --}}
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Payment History</h2>
                @if($fee->payments->count() > 0)
                    <div class="space-y-4">
                        @foreach($fee->payments as $payment)
                            <div class="p-4 rounded-xl border {{ $payment->is_verified ? 'border-green-500/20 bg-green-500/5' : ($payment->is_rejected ? 'border-red-500/20 bg-red-500/5' : 'border-amber-500/20 bg-amber-500/5') }}">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="text-lg font-bold {{ $payment->is_verified ? 'text-green-400' : 'text-white' }}">{{ $payment->formatted_amount }}</span>
                                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-{{ $payment->status_color }}-600/20 text-{{ $payment->status_color }}-400">
                                            {{ $payment->status_label }}
                                        </span>
                                    </div>
                                    @if($payment->is_verified)
                                        <a href="{{ route('fees.receipt', $payment) }}" target="_blank" class="btn btn-outline py-1.5 px-3 text-sm flex justify-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            Download Receipt
                                        </a>
                                    @endif
                                </div>
                                
                                <div class="text-sm text-dark-400 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1">
                                    <p>Date: <span class="text-dark-200">{{ $payment->payment_date->format('M j, Y') }}</span></p>
                                    <p>Method: <span class="text-dark-200">{{ $payment->method_label }}</span></p>
                                    @if($payment->reference)
                                        <p>Ref: <span class="text-dark-200 font-mono">{{ $payment->reference }}</span></p>
                                    @endif
                                    @if($payment->is_verified)
                                        <p>Receipt #: <span class="text-dark-200 font-mono">{{ $payment->receipt_number }}</span></p>
                                    @endif
                                </div>

                                @if($payment->is_rejected && $payment->rejection_reason)
                                    <div class="mt-3 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-sm text-red-300">
                                        <span class="font-semibold">Rejection Reason:</span> {{ $payment->rejection_reason }}
                                    </div>
                                @endif
                                
                                @if($payment->is_pending)
                                    <p class="text-xs text-amber-400/80 mt-2">This payment is currently being reviewed by administration. Your balance will update once approved.</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 bg-dark-800/50 rounded-xl border border-dark-700">
                        <svg class="w-12 h-12 text-dark-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <p class="text-dark-400">You haven't made any payments for this fee yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
