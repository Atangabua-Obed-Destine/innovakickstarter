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
                    <select name="fellow_id" class="form-input w-full" x-model="fellowId" @change="fetchBillables" required>
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
                    <select name="billable_composite" class="form-input w-full" :disabled="loading">
                        <option value="">General Fee (No Link)</option>
                        <template x-for="billable in billables" :key="billable.type + '|' + billable.id">
                            <option :value="billable.type + '|' + billable.id" x-text="billable.label"></option>
                        </template>
                    </select>
                    <p x-show="loading" class="text-xs text-blue-400 mt-1">Loading linked options...</p>
                    <p x-show="!loading && fellowId && billables.length === 0" class="text-xs text-dark-400 mt-1">This fellow has no active internships or programs.</p>
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Due Date *</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}" class="form-input w-full" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Grace Period (Hours)</label>
                    <input type="number" name="grace_period_hours" value="{{ old('grace_period_hours', 48) }}"
                           class="form-input w-full" min="0" max="720">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-dark-300 mb-1">Description</label>
                <textarea name="description" class="form-input w-full" rows="3"
                          placeholder="Optional: description of the fee...">{{ old('description') }}</textarea>
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
        fellowId: '{{ old('fellow_id', '') }}',
        billables: [],
        loading: false,

        init() {
            if (this.fellowId) {
                this.fetchBillables();
            }
        },

        fetchBillables() {
            if (!this.fellowId) {
                this.billables = [];
                return;
            }
            
            this.loading = true;
            let url = '{{ route("admin.fees.fellow-billables", ["fellow" => ":id"]) }}';
            url = url.replace(':id', this.fellowId);
            
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    this.billables = data;
                })
                .catch(err => {
                    console.error('Failed to fetch billables', err);
                    this.billables = [];
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    };
}
</script>
@endsection
