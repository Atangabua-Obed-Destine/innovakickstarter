{{-- Payment method + reference for a paid plan request. Expects $paymentMethods and $instructions. --}}
@if($instructions !== '')
    <div class="rounded-lg bg-dark-800 border border-dark-700 p-3 text-sm text-dark-300 whitespace-pre-line">{{ $instructions }}</div>
@else
    <p class="text-sm text-dark-400">Pay I-NNOVA CMR by Mobile Money, bank transfer or cash, then enter the transaction reference below.</p>
@endif
<div>
    <label class="block text-sm font-medium text-dark-300 mb-1">Payment method</label>
    <select name="payment_method" required class="form-input w-full">
        @foreach($paymentMethods as $value => $label)
            <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-dark-300 mb-1">Transaction reference</label>
    <input type="text" name="payment_reference" required maxlength="120" value="{{ old('payment_reference') }}"
           class="form-input w-full font-mono" placeholder="e.g. MP240512.1234.A56789">
</div>
