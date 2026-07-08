@extends('layouts.app')

@section('title', 'Upload Payment Receipt')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div>
        <nav class="text-sm text-dark-400 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-white">Dashboard</a>
            <span class="mx-2">›</span>
            <a href="{{ route('fees.index') }}" class="hover:text-white">My Fees</a>
            <span class="mx-2">›</span>
            <a href="{{ route('fees.show', $fee) }}" class="hover:text-white">Fee Details</a>
            <span class="mx-2">›</span>
            <span class="text-dark-300">Upload Receipt</span>
        </nav>
        <h1 class="text-2xl font-bold text-white">Upload Payment Receipt</h1>
        <p class="text-dark-400">Submit proof of your payment for verification.</p>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
            <ul class="text-red-400 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {{-- Left: Fee Context --}}
        <div class="md:col-span-1 space-y-4">
            <div class="card p-5 bg-blue-600/10 border-blue-500/30">
                <h3 class="text-blue-400 font-medium text-sm mb-1">Paying Towards</h3>
                <p class="text-white font-bold text-lg leading-tight">{{ $fee->title }}</p>
                <div class="mt-4 pt-4 border-t border-blue-500/20 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-blue-300/70">Total Fee:</span>
                        <span class="text-white font-medium">{{ $fee->formatted_total }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-blue-300/70">Amount Due:</span>
                        <span class="text-white font-bold">{{ $fee->formatted_balance }}</span>
                    </div>
                </div>
            </div>

            <div class="card p-5 border-dark-700">
                <h3 class="text-white font-medium text-sm mb-3">Accepted Methods</h3>
                <ul class="space-y-2 text-sm text-dark-300">
                    <li class="flex items-center gap-2"><div class="w-1.5 h-1.5 rounded-full bg-yellow-400"></div> MTN Mobile Money</li>
                    <li class="flex items-center gap-2"><div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div> Orange Money</li>
                    <li class="flex items-center gap-2"><div class="w-1.5 h-1.5 rounded-full bg-green-500"></div> Bank Transfer</li>
                    <li class="flex items-center gap-2"><div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div> Cash (At Office)</li>
                </ul>
            </div>
        </div>

        {{-- Right: Upload Form --}}
        <div class="md:col-span-2">
            <div class="card p-6">
                <form action="{{ route('fees.upload.store', $fee) }}" method="POST" enctype="multipart/form-data" class="space-y-5" x-data="{ fileName: '' }">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-1">Amount Paid (CFA) *</label>
                            <input type="number" name="amount" value="{{ old('amount', $fee->balance) }}" class="form-input w-full font-medium" min="1" step="1" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-1">Payment Date *</label>
                            <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" class="form-input w-full" max="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-1">Payment Method *</label>
                            <select name="method" class="form-input w-full" required>
                                <option value="">-- Select Method --</option>
                                @foreach(\App\Models\FeePayment::paymentMethods() as $val => $label)
                                    <option value="{{ $val }}" {{ old('method') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if($fee->plan_type === 'installments' && $fee->installments->count() > 0)
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-1">Applying To Installment</label>
                                <select name="installment_id" class="form-input w-full">
                                    <option value="">Auto-allocate</option>
                                    @foreach($fee->installments->where('status', '!=', \App\Models\FeeInstallment::STATUS_PAID) as $inst)
                                        <option value="{{ $inst->id }}" {{ (old('installment_id') == $inst->id || $preselectedInstallment == $inst->id) ? 'selected' : '' }}>
                                            {{ $inst->label }} ({{ number_format($inst->balance, 2) }} due)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-1">Transaction Ref (Optional)</label>
                                <input type="text" name="reference" value="{{ old('reference') }}" class="form-input w-full" placeholder="e.g. TXN-12345">
                            </div>
                        @endif
                    </div>

                    @if($fee->plan_type === 'installments')
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-1">Transaction Ref (Optional)</label>
                            <input type="text" name="reference" value="{{ old('reference') }}" class="form-input w-full" placeholder="e.g. TXN-12345">
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">Upload Receipt File *</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dark-600 border-dashed rounded-xl hover:border-primary-500/50 transition-colors bg-dark-800/50">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-dark-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-dark-300 justify-center">
                                    <label for="receipt_file" class="relative cursor-pointer bg-dark-900 rounded-md font-medium text-primary-400 hover:text-primary-300 focus-within:outline-none px-2 py-0.5">
                                        <span>Select a file</span>
                                        <input id="receipt_file" name="receipt_file" type="file" class="sr-only" required accept=".jpg,.jpeg,.png,.pdf" @change="fileName = $refs.fileInput.files[0].name" x-ref="fileInput">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-dark-500">PNG, JPG, PDF up to 5MB</p>
                                <p x-show="fileName" class="text-sm font-medium text-green-400 mt-2" x-text="'Selected: ' + fileName"></p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">Additional Notes</label>
                        <textarea name="notes" class="form-input w-full" rows="2" placeholder="Any details we should know?">{{ old('notes') }}</textarea>
                    </div>

                    <div class="pt-4 border-t border-dark-700 flex justify-end gap-3">
                        <a href="{{ route('fees.show', $fee) }}" class="btn btn-outline">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            Submit for Verification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
