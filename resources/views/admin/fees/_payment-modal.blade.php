<div x-show="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showPaymentModal" x-transition.opacity class="fixed inset-0 transition-opacity bg-dark-900/80 backdrop-blur-sm" @click="showPaymentModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-show="showPaymentModal" x-transition.scale class="relative z-10 inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-dark-800 rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6 border border-dark-700">
            <div class="flex justify-between items-start mb-5">
                <h3 class="text-xl font-bold text-white">Record Payment</h3>
                <button @click="showPaymentModal = false" class="text-dark-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Fellow Context --}}
            <div class="bg-dark-900/50 rounded-xl p-4 mb-6 border border-dark-700">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <div>
                        <p class="text-white text-sm font-medium">{{ $fee->fellow->name }} ({{ $fee->fellow->username }})</p>
                        <p class="text-dark-400 text-xs">{{ $fee->title }}</p>
                    </div>
                </div>
            </div>

            {{-- Financial Context --}}
            <div class="grid grid-cols-3 gap-4 mb-6 text-center">
                <div class="p-3 bg-dark-900/50 rounded-lg">
                    <p class="text-dark-400 text-xs mb-1">Fee Amount</p>
                    <p class="text-white font-medium">{{ number_format($fee->amount_total, 2) }}</p>
                </div>
                <div class="p-3 bg-green-500/10 rounded-lg">
                    <p class="text-green-400/80 text-xs mb-1">Already Paid</p>
                    <p class="text-green-400 font-medium">{{ number_format($fee->amount_paid, 2) }}</p>
                </div>
                <div class="p-3 bg-amber-500/10 rounded-lg border border-amber-500/20">
                    <p class="text-amber-400/80 text-xs mb-1">Remaining Balance</p>
                    <p class="text-amber-400 font-bold">{{ number_format($fee->balance, 2) }}</p>
                </div>
            </div>

            <form action="{{ route('admin.fees.record-payment', $fee) }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">Payment Date *</label>
                        <input type="date" name="payment_date" class="form-input w-full" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">Amount Received (CFA) *</label>
                        <input type="number" name="amount" class="form-input w-full font-medium" value="{{ $fee->balance }}" min="1" max="{{ $fee->balance }}" step="1" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">Payment Method *</label>
                        <select name="method" class="form-input w-full" required>
                            @foreach(\App\Models\FeePayment::paymentMethods() as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($fee->plan_type === 'installments' && $fee->installments->count() > 0)
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-1">Allocate To Installment</label>
                            <select name="installment_id" class="form-input w-full">
                                <option value="">Auto-allocate (Oldest First)</option>
                                @foreach($fee->installments->where('status', '!=', \App\Models\FeeInstallment::STATUS_PAID) as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->label }} ({{ number_format($inst->balance, 2) }} due)</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div></div>
                    @endif
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-dark-300 mb-1">Notes (Optional)</label>
                    <textarea name="notes" class="form-input w-full" rows="2" placeholder="Transaction ID or notes..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-dark-700">
                    <button type="button" @click="showPaymentModal = false" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn bg-green-600 hover:bg-green-500 text-white border-transparent">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Record as Received
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
