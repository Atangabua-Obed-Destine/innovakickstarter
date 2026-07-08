@extends('layouts.app')

@section('title', 'Assign Fee')

@section('content')
<div class="space-y-6" x-data="feeForm()">
    <div>
        <nav class="text-sm text-dark-400 mb-2">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.fees.index') }}" class="hover:text-white">Fee Reports</a>
            <span class="mx-2">›</span>
            <span class="text-dark-300">Assign New Fee</span>
        </nav>
        <h1 class="text-2xl font-bold text-white">Assign New Fee</h1>
        <p class="text-dark-400">Assign a fee to a fellow for an internship, program, cohort, or track.</p>
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

    <form method="POST" action="{{ route('admin.fees.store') }}" class="space-y-6">
        @csrf

        {{-- Fellow Selection --}}
        <div class="card p-6 space-y-4">
            <h2 class="text-lg font-semibold text-white">Fellow Information</h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Select Fellow *</label>
                    <select name="fellow_id" class="form-input w-full" required>
                        <option value="">-- Select Fellow --</option>
                        @foreach($fellows as $fellow)
                            <option value="{{ $fellow->id }}" {{ old('fellow_id') == $fellow->id ? 'selected' : '' }}>
                                {{ $fellow->name }} ({{ $fellow->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Linked To (Optional)</label>
                    <select name="billable_type" class="form-input w-full">
                        <option value="">General Fee (No Link)</option>
                        <option value="App\Models\InternshipProfile" {{ old('billable_type') === 'App\Models\InternshipProfile' ? 'selected' : '' }}>Internship</option>
                        <option value="App\Models\Cohort" {{ old('billable_type') === 'App\Models\Cohort' ? 'selected' : '' }}>Cohort</option>
                        <option value="App\Models\Program" {{ old('billable_type') === 'App\Models\Program' ? 'selected' : '' }}>Program</option>
                        <option value="App\Models\Track" {{ old('billable_type') === 'App\Models\Track' ? 'selected' : '' }}>Track</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Fee Details --}}
        <div class="card p-6 space-y-4">
            <h2 class="text-lg font-semibold text-white">Fee Details</h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Fee Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-input w-full"
                           placeholder="e.g. Internship Fee, Program Tuition" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Amount (CFA) *</label>
                    <input type="number" name="amount_total" value="{{ old('amount_total') }}" class="form-input w-full"
                           placeholder="150000" min="1" step="1" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-dark-300 mb-1">Description</label>
                <textarea name="description" class="form-input w-full" rows="3"
                          placeholder="Optional: description of the fee...">{{ old('description') }}</textarea>
            </div>
        </div>

        {{-- Payment Plan --}}
        <div class="card p-6 space-y-4">
            <h2 class="text-lg font-semibold text-white">Payment Plan</h2>

            <div>
                <label class="block text-sm font-medium text-dark-300 mb-1">Plan Type *</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="plan_type" value="one_time" x-model="planType"
                               {{ old('plan_type', 'one_time') === 'one_time' ? 'checked' : '' }}
                               class="text-primary-500 focus:ring-primary-500">
                        <span class="text-dark-200">One-Time Payment</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="plan_type" value="installments" x-model="planType"
                               {{ old('plan_type') === 'installments' ? 'checked' : '' }}
                               class="text-primary-500 focus:ring-primary-500">
                        <span class="text-dark-200">Installments</span>
                    </label>
                </div>
            </div>

            <div x-show="planType === 'installments'" x-transition class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Number of Installments *</label>
                    <input type="number" name="installments_count" value="{{ old('installments_count', 2) }}"
                           class="form-input w-full" min="2" max="12">
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Cadence *</label>
                    <select name="installment_cadence" class="form-input w-full">
                        <option value="monthly" {{ old('installment_cadence') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="biweekly" {{ old('installment_cadence') === 'biweekly' ? 'selected' : '' }}>Bi-Weekly</option>
                        <option value="weekly" {{ old('installment_cadence') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">First Due Date *</label>
                    <input type="date" name="first_due_date" value="{{ old('first_due_date') }}" class="form-input w-full" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Final Due Date *</label>
                    <input type="date" name="final_due_date" value="{{ old('final_due_date') }}" class="form-input w-full" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Grace Period (Hours)</label>
                    <input type="number" name="grace_period_hours" value="{{ old('grace_period_hours', 48) }}"
                           class="form-input w-full" min="0" max="720">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.fees.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Assign Fee</button>
        </div>
    </form>
</div>

<script>
function feeForm() {
    return {
        planType: '{{ old('plan_type', 'one_time') }}',
    };
}
</script>
@endsection
