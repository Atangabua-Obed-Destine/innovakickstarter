@extends('layouts.app')

@section('title', 'Payment Record Details')

@section('content')
<div class="space-y-6" x-data="{ showRejectModal: false }">
    {{-- Header --}}
    <div>
        <nav class="text-sm text-dark-400 mb-2">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.payment-verifications.index') }}" class="hover:text-white">Payment Records</a>
            <span class="mx-2">›</span>
            <span class="text-dark-300">Record Details</span>
        </nav>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-white">Payment Record Details</h1>
            <span class="badge bg-{{ $payment->status_color }}-600/20 text-{{ $payment->status_color }}-400 border-{{ $payment->status_color }}-500/30">
                {{ $payment->status_label }}
            </span>
        </div>
        <p class="text-dark-400 mt-1">
            @if($payment->source === \App\Models\FeePayment::SOURCE_FELLOW)
                Uploaded by {{ $payment->fellow->name }} on {{ $payment->created_at->format('M j, Y, g:i a') }}
            @else
                Recorded by Admin on {{ $payment->created_at->format('M j, Y, g:i a') }}
            @endif
        </p>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- Left Column: Details --}}
        <div class="space-y-6">
            
            {{-- Action Banner --}}
            @if($payment->status === \App\Models\FeePayment::STATUS_SUBMITTED)
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        <div>
                            <p class="text-white font-medium">Pending Verification</p>
                            <p class="text-amber-400/80 text-sm">Please verify the receipt and approve or reject it.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="showRejectModal = true" class="btn btn-outline border-red-500/50 text-red-400 hover:bg-red-500/10">Reject</button>
                        <form action="{{ route('admin.payment-verifications.approve', $payment) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn bg-green-600 hover:bg-green-500 text-white border-transparent">
                                Approve Payment
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if($payment->status === \App\Models\FeePayment::STATUS_REJECTED)
                <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-5">
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-white font-medium">Payment Rejected</p>
                    </div>
                    <p class="text-dark-300 text-sm mb-3">Rejected by {{ $payment->rejector?->name }} on {{ $payment->rejected_at->format('M j, Y') }}</p>
                    <div class="p-3 bg-dark-900 rounded-lg text-sm text-red-300 italic border border-red-500/20">
                        "{{ $payment->rejection_reason }}"
                    </div>
                </div>
            @endif

            @if($payment->status === \App\Models\FeePayment::STATUS_VERIFIED)
                <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-5">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>
                                <p class="text-white font-medium">Payment Approved</p>
                                <p class="text-dark-300 text-sm">
                                    @if($payment->verifier)
                                        Verified by {{ $payment->verifier->name }} on {{ $payment->verified_at?->format('M j, Y') ?? $payment->created_at->format('M j, Y') }}
                                    @else
                                        Auto-verified admin entry on {{ $payment->created_at->format('M j, Y') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('admin.fees.receipt', $payment) }}" target="_blank" class="btn btn-primary py-1.5 px-3 text-sm">
                            Print Receipt
                        </a>
                    </div>
                </div>
            @endif

            {{-- Danger Zone --}}
            <div class="card p-6 border-red-500/30">
                <h2 class="text-lg font-semibold text-white mb-2">Danger Zone</h2>
                <p class="text-dark-400 text-sm mb-4">Deleting this payment record will permanently remove it from the system and automatically reverse any financial totals and status updates on the fellow's fee.</p>
                <form action="{{ route('admin.payment-verifications.destroy', $payment) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this payment record? This action cannot be undone and will alter financial records.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline border-red-500 text-red-400 hover:bg-red-500 hover:text-white w-full">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Permanently Delete Record
                    </button>
                </form>
            </div>

            {{-- Fellow Card --}}
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Fellow Information</h2>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-dark-700 flex items-center justify-center text-xl font-bold text-white overflow-hidden">
                        @if($payment->fellow->avatar)
                            <img src="{{ $payment->fellow->avatar }}" alt="{{ $payment->fellow->name }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($payment->fellow->name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <p class="text-white font-medium">{{ $payment->fellow->name }}</p>
                        <p class="text-dark-400 text-sm">{{ $payment->fellow->email }}</p>
                        <p class="text-dark-500 text-xs">{{ $payment->fellow->username }}</p>
                    </div>
                </div>
            </div>

            {{-- Payment Details --}}
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Payment Details</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-dark-800/50 rounded-lg">
                            <span class="block text-dark-400 text-xs mb-1">Amount Declared</span>
                            <span class="text-white font-bold text-lg">{{ $payment->formatted_amount }}</span>
                        </div>
                        <div class="p-3 bg-dark-800/50 rounded-lg">
                            <span class="block text-dark-400 text-xs mb-1">Payment Method</span>
                            <span class="text-white font-medium">{{ $payment->method_label }}</span>
                        </div>
                    </div>

                    <div class="space-y-2 pt-2">
                        <div class="flex justify-between border-b border-dark-700 pb-2">
                            <span class="text-dark-400 text-sm">Payment Date</span>
                            <span class="text-white text-sm">{{ $payment->payment_date->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between border-b border-dark-700 pb-2">
                            <span class="text-dark-400 text-sm">Transaction Reference</span>
                            <span class="text-white text-sm font-mono">{{ $payment->reference ?? 'None provided' }}</span>
                        </div>
                        @if($payment->notes)
                            <div class="pt-2">
                                <span class="text-dark-400 text-sm block mb-1">Fellow Notes</span>
                                <p class="text-dark-200 text-sm italic bg-dark-900/50 p-3 rounded-lg">"{{ $payment->notes }}"</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Target Fee --}}
            <div class="card p-6 border-l-4 border-l-blue-500">
                <h2 class="text-lg font-semibold text-white mb-4">Target Fee</h2>
                
                <div class="mb-4">
                    <a href="{{ route('admin.fees.show', $payment->fee) }}" class="text-blue-400 hover:text-blue-300 font-medium text-lg flex items-center gap-2">
                        {{ $payment->fee->title }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    @if($payment->installment)
                        <p class="text-dark-300 text-sm mt-1">Applying to: <span class="text-white font-medium">{{ $payment->installment->label }}</span></p>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm border-t border-dark-700 pt-4">
                    <div>
                        <p class="text-dark-400 mb-1">Total Fee Amount</p>
                        <p class="text-white">{{ $payment->fee->formatted_total }}</p>
                    </div>
                    <div>
                        <p class="text-dark-400 mb-1">Remaining Balance</p>
                        <p class="text-amber-400">{{ $payment->fee->formatted_balance }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Receipt Image --}}
        <div class="space-y-6">
            <div class="card p-6 h-full min-h-[500px] flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">Uploaded Receipt Evidence</h2>
                    @if($payment->receipt_path)
                        <a href="{{ Storage::url($payment->receipt_path) }}" target="_blank" class="text-primary-400 hover:text-primary-300 text-sm flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download
                        </a>
                    @endif
                </div>

                <div class="flex-1 bg-dark-900 rounded-lg border border-dark-700 flex items-center justify-center p-4 overflow-hidden relative">
                    @if($payment->receipt_path)
                        @php
                            $ext = strtolower(pathinfo($payment->receipt_path, PATHINFO_EXTENSION));
                        @endphp
                        
                        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                            <img src="{{ Storage::url($payment->receipt_path) }}" alt="Payment Receipt" class="max-w-full max-h-[800px] object-contain rounded">
                        @elseif($ext === 'pdf')
                            <iframe src="{{ Storage::url($payment->receipt_path) }}" class="w-full h-full min-h-[600px] rounded" border="0"></iframe>
                        @else
                            <div class="text-center">
                                <svg class="w-16 h-16 text-dark-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-white mb-2">File type not supported for preview</p>
                                <a href="{{ Storage::url($payment->receipt_path) }}" target="_blank" class="btn btn-primary">Download File</a>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-dark-500">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p>No receipt file uploaded.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    @if($payment->status === \App\Models\FeePayment::STATUS_SUBMITTED)
    <div x-show="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showRejectModal" x-transition.opacity class="fixed inset-0 transition-opacity bg-dark-900/80 backdrop-blur-sm" @click="showRejectModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showRejectModal" x-transition.scale class="relative z-10 inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-dark-800 rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-dark-700">
                <h3 class="text-lg font-bold text-white mb-4">Reject Payment Verification</h3>
                <p class="text-dark-300 text-sm mb-4">The fellow will be notified that their payment verification was rejected.</p>
                
                <form action="{{ route('admin.payment-verifications.reject', $payment) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-dark-300 mb-1">Reason for Rejection *</label>
                        <textarea name="rejection_reason" class="form-input w-full" rows="3" required placeholder="e.g. Receipt is unreadable, amount does not match..."></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="showRejectModal = false" class="btn btn-outline">Cancel</button>
                        <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white border-transparent">Reject Submission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
