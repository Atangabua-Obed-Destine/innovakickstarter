@extends('layouts.app')

@section('title', 'Fee Details · ' . $fee->reference)

@section('content')
<div class="space-y-6" x-data="{ showPaymentModal: false, showWaiveModal: false }">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <nav class="text-sm text-dark-400 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
                <span class="mx-2">›</span>
                <a href="{{ route('admin.fees.index') }}" class="hover:text-white">Fee Reports</a>
                <span class="mx-2">›</span>
                <span class="text-dark-300">{{ $fee->reference }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white">{{ $fee->title }}</h1>
                <span class="badge bg-{{ $fee->status_color }}-600/20 text-{{ $fee->status_color }}-400 border-{{ $fee->status_color }}-500/30">
                    {{ $fee->status_label }}
                </span>
            </div>
            <p class="text-dark-400 mt-1">Assigned on {{ $fee->assigned_at->format('M j, Y') }} by {{ $fee->assignedByAdmin?->name ?? 'System' }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if($fee->balance > 0 && $fee->status !== \App\Models\Fee::STATUS_WAIVED)
                <button @click="showPaymentModal = true" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Record Payment
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Fee Info --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Fellow Card --}}
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Fellow Details</h2>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-dark-700 flex items-center justify-center text-xl font-bold text-white overflow-hidden">
                        @if($fee->fellow->avatar)
                            <img src="{{ $fee->fellow->avatar }}" alt="{{ $fee->fellow->name }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($fee->fellow->name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <p class="text-white font-medium">{{ $fee->fellow->name }}</p>
                        <p class="text-dark-400 text-sm">{{ $fee->fellow->email }}</p>
                    </div>
                </div>
                <div class="space-y-3 pt-4 border-t border-dark-700">
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Username</span>
                        <span class="text-white text-sm">{{ $fee->fellow->username }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400 text-sm">Linked To</span>
                        <span class="text-white text-sm">{{ $fee->billable_label }}</span>
                    </div>
                </div>
            </div>

            {{-- Summary Card --}}
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
                            <span class="text-dark-400 text-sm">First Due Date</span>
                            <span class="text-white text-sm">{{ $fee->first_due_date->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-dark-400 text-sm">Final Due Date</span>
                            <span class="text-{{ $fee->is_overdue ? 'red' : 'white' }} text-sm">{{ $fee->final_due_date->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions Card --}}
            @if($fee->balance > 0 && $fee->status !== \App\Models\Fee::STATUS_WAIVED)
                <div class="card p-6 border-red-500/30">
                    <h2 class="text-lg font-semibold text-white mb-2">Administrative Actions</h2>
                    
                    @if($fee->amount_paid == 0 && $fee->payments()->count() === 0)
                        <div class="mb-4 pb-4 border-b border-dark-700">
                            <p class="text-dark-400 text-sm mb-4">You can permanently delete this fee because no payments have been recorded against it yet.</p>
                            <form action="{{ route('admin.fees.destroy', $fee) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this fee? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline border-red-500 text-red-400 hover:bg-red-500 hover:text-white w-full">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Permanently Delete Fee
                                </button>
                            </form>
                        </div>
                    @endif

                    <p class="text-dark-400 text-sm mb-4">Waiving a fee will mark it as fully settled and stop any overdue alerts.</p>
                    <button @click="showWaiveModal = true" class="btn btn-outline border-red-500/50 text-red-400 hover:bg-red-500/10 w-full">
                        Waive Remaining Fee
                    </button>
                </div>
            @endif

            @if($fee->status === \App\Models\Fee::STATUS_WAIVED)
                <div class="card p-6 bg-dark-800/80">
                    <h2 class="text-lg font-semibold text-white mb-2">Fee Waived</h2>
                    <p class="text-dark-400 text-sm mb-2">This fee was waived by {{ $fee->waivedByAdmin?->name }} on {{ $fee->waived_at->format('M j, Y') }}.</p>
                    <div class="p-3 bg-dark-900 rounded-lg text-sm text-dark-300 italic">
                        "{{ $fee->waived_reason }}"
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Column: Installments & Payments --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Installments Table (if applicable) --}}
            @if($fee->plan_type === 'installments' && $fee->installments->count() > 0)
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Payment Plan (Installments)</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-dark-700">
                                    <th class="py-3 text-dark-400 text-xs uppercase font-semibold">Installment</th>
                                    <th class="py-3 text-dark-400 text-xs uppercase font-semibold text-right">Amount Due</th>
                                    <th class="py-3 text-dark-400 text-xs uppercase font-semibold text-right">Amount Paid</th>
                                    <th class="py-3 text-dark-400 text-xs uppercase font-semibold text-right">Due Date</th>
                                    <th class="py-3 text-dark-400 text-xs uppercase font-semibold text-center">Status</th>
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
                            <div class="p-4 rounded-xl border {{ $payment->is_verified ? 'border-green-500/20 bg-green-500/5' : ($payment->is_rejected ? 'border-red-500/20 bg-red-500/5' : 'border-dark-700 bg-dark-800/50') }}">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-3 mb-1">
                                            <span class="text-lg font-bold {{ $payment->is_verified ? 'text-green-400' : 'text-white' }}">{{ $payment->formatted_amount }}</span>
                                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-{{ $payment->status_color }}-600/20 text-{{ $payment->status_color }}-400">
                                                {{ $payment->status_label }}
                                            </span>
                                            @if($payment->source === \App\Models\FeePayment::SOURCE_FELLOW)
                                                <span class="px-2 py-0.5 rounded text-xs bg-blue-600/20 text-blue-400 border border-blue-500/20 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                    Uploaded
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-dark-400 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1 mt-2">
                                            <p>Date: <span class="text-dark-200">{{ $payment->payment_date->format('M j, Y') }}</span></p>
                                            <p>Method: <span class="text-dark-200">{{ $payment->method_label }}</span></p>
                                            <p>Ref: <span class="text-dark-200 font-mono">{{ $payment->reference ?? 'N/A' }}</span></p>
                                            @if($payment->is_verified)
                                                <p>Receipt: <span class="text-dark-200 font-mono">{{ $payment->receipt_number }}</span></p>
                                            @endif
                                        </div>
                                        @if($payment->notes)
                                            <p class="text-sm text-dark-300 mt-2 italic">"{{ $payment->notes }}"</p>
                                        @endif
                                    </div>
                                    <div class="flex flex-col gap-2 min-w-[120px]">
                                        @if($payment->is_verified)
                                            <a href="{{ route('admin.fees.receipt', $payment) }}" target="_blank" class="btn btn-outline py-1.5 px-3 text-sm flex justify-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                Print Receipt
                                            </a>
                                        @endif
                                        @if($payment->source === \App\Models\FeePayment::SOURCE_FELLOW && $payment->is_pending)
                                            <a href="{{ route('admin.payment-verifications.show', $payment) }}" class="btn btn-primary py-1.5 px-3 text-sm flex justify-center">
                                                Review Upload
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($payment->is_rejected && $payment->rejection_reason)
                                    <div class="mt-3 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-sm text-red-300">
                                        <span class="font-semibold">Rejection Reason:</span> {{ $payment->rejection_reason }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-dark-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <p class="text-dark-400">No payments recorded yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Record Payment Modal --}}
    @include('admin.fees._payment-modal')

    {{-- Waive Fee Modal --}}
    @if($fee->balance > 0 && $fee->status !== \App\Models\Fee::STATUS_WAIVED)
    <div x-show="showWaiveModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showWaiveModal" x-transition.opacity class="fixed inset-0 transition-opacity bg-dark-900/80 backdrop-blur-sm" @click="showWaiveModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showWaiveModal" x-transition.scale class="relative z-10 inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-dark-800 rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-dark-700">
                <h3 class="text-lg font-bold text-white mb-4">Waive Fee</h3>
                <p class="text-dark-300 text-sm mb-4">Are you sure you want to waive the remaining balance of <span class="font-bold text-white">{{ $fee->formatted_balance }}</span>? This action cannot be undone.</p>
                
                <form action="{{ route('admin.fees.waive', $fee) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-dark-300 mb-1">Reason for waiving *</label>
                        <textarea name="reason" class="form-input w-full" rows="3" required placeholder="Authorized by management..."></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="showWaiveModal = false" class="btn btn-outline">Cancel</button>
                        <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white border-transparent">Confirm Waiver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
