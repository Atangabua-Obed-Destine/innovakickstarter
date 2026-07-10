@extends('layouts.app')

@section('title', 'Create Mentorship Pod')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.mentorship-pods.index') }}" class="p-2 text-dark-400 hover:text-white hover:bg-dark-800 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white">Create Mentorship Pod</h1>
            <p class="text-dark-400 mt-1">Group fellows into squads and assign a lead.</p>
        </div>
    </div>

    <form action="{{ route('admin.mentorship-pods.store') }}" method="POST" class="card border border-dark-700 bg-dark-800" x-data="podCreator('{{ $selectedTrackId ?? '' }}')">
        @csrf
        
        <div class="p-6 space-y-8">
            <!-- Step 1: Track Selection -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 border-b border-dark-700 pb-2">
                    <div class="w-8 h-8 rounded-full bg-primary-500/20 text-primary-400 flex items-center justify-center font-bold text-sm">1</div>
                    <h2 class="text-lg font-semibold text-white">Select Track</h2>
                </div>
                
                <div>
                    <label for="track_id" class="form-label">Track <span class="text-red-500">*</span></label>
                    <select name="track_id" id="track_id" class="form-input w-full" x-model="trackId" @change="fetchEligibleFellows" required>
                        <option value="">-- Choose a Track --</option>
                        @foreach($tracks as $track)
                            <option value="{{ $track->id }}">{{ $track->name }}</option>
                        @endforeach
                    </select>
                    <p class="form-help">Only fellows with an active internship who are not already in a pod will be eligible.</p>
                    @error('track_id')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="isLoading" class="py-8 flex flex-col items-center justify-center text-dark-400">
                <svg class="w-8 h-8 animate-spin-slow text-primary-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <p>Loading eligible fellows...</p>
            </div>

            <div x-show="!isLoading && trackId && eligibleFellows.length === 0" class="p-4 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 text-sm">
                There are no eligible fellows for this track. Fellows must have an active internship and an approved track enrollment to be placed in a pod.
            </div>

            <!-- Step 2: Pod Composition -->
            <div x-show="!isLoading && eligibleFellows.length > 0" class="space-y-6" style="display: none;">
                <div class="flex items-center gap-2 border-b border-dark-700 pb-2">
                    <div class="w-8 h-8 rounded-full bg-primary-500/20 text-primary-400 flex items-center justify-center font-bold text-sm">2</div>
                    <h2 class="text-lg font-semibold text-white">Assemble Pod</h2>
                </div>

                <div class="bg-dark-900/50 rounded-xl p-4 border border-dark-700/50">
                    <p class="text-sm text-dark-300 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Select between 2 and 4 members. The Pod Lead is automatically included as a member.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Pod Lead -->
                        <div>
                            <label for="lead_id" class="form-label text-primary-400 font-bold">👑 Pod Lead <span class="text-red-500">*</span></label>
                            <select name="lead_id" id="lead_id" class="form-input w-full border-primary-500/30 focus:border-primary-500" x-model="leadId" required>
                                <option value="">-- Select Pod Lead --</option>
                                <template x-for="fellow in eligibleFellows" :key="fellow.id">
                                    <option :value="fellow.id" x-text="`${fellow.name} (${fellow.score}%, ${fellow.tier})`"></option>
                                </template>
                            </select>
                            <p class="text-xs text-dark-400 mt-1">Usually the highest scoring fellow. They will name the pod.</p>
                            @error('lead_id')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Optional Initial Name -->
                        <div>
                            <label for="name" class="form-label">Pod Name (Optional)</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-input w-full" placeholder="e.g. The Innovators">
                            <p class="text-xs text-dark-400 mt-1">Leave blank to let the Pod Lead choose the name.</p>
                        </div>
                    </div>

                    <!-- Members Selection -->
                    <div class="mt-6">
                        <label class="form-label">Pod Members <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-2">
                            <template x-for="fellow in eligibleFellows" :key="fellow.id">
                                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                                    :class="{
                                        'bg-primary-500/10 border-primary-500/50': memberIds.includes(fellow.id) || fellow.id == leadId,
                                        'bg-dark-800 border-dark-700 hover:border-dark-500': !memberIds.includes(fellow.id) && fellow.id != leadId,
                                        'opacity-50 cursor-not-allowed': fellow.id == leadId
                                    }">
                                    <div class="pt-0.5">
                                        <input type="checkbox" name="member_ids[]" :value="fellow.id" x-model="memberIds" 
                                            class="w-4 h-4 rounded border-dark-600 text-primary-500 focus:ring-primary-500 focus:ring-offset-dark-800 bg-dark-900"
                                            :disabled="fellow.id == leadId"
                                            :checked="fellow.id == leadId">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-white truncate">
                                            <span x-text="fellow.name"></span>
                                            <span x-show="fellow.id == leadId" class="ml-1 text-xs px-1.5 py-0.5 rounded bg-primary-500/20 text-primary-400 uppercase tracking-wider font-bold">Lead</span>
                                        </p>
                                        <p class="text-xs text-dark-400 mt-0.5 truncate" x-text="`Score: ${fellow.score}% • ${fellow.tier}`"></p>
                                    </div>
                                </label>
                            </template>
                        </div>
                        <p class="text-xs mt-2" :class="isValidSize ? 'text-emerald-400' : 'text-amber-400'">
                            Selected: <span x-text="totalMembersSelected"></span>/4 members
                        </p>
                        @error('member_ids')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-dark-700 bg-dark-900/50 flex justify-end gap-3 rounded-b-xl">
            <a href="{{ route('admin.mentorship-pods.index') }}" class="btn btn-outline border-dark-600 hover:border-dark-500 text-dark-200">Cancel</a>
            <button type="submit" class="btn btn-primary" :disabled="!canSubmit" :class="{ 'opacity-50 cursor-not-allowed': !canSubmit }">
                Create Mentorship Pod
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('podCreator', (initialTrackId) => ({
            trackId: initialTrackId,
            eligibleFellows: [],
            isLoading: false,
            leadId: '{{ old('lead_id', '') }}',
            memberIds: {!! json_encode(old('member_ids', [])) !!},

            init() {
                if (this.trackId) {
                    this.fetchEligibleFellows();
                }
            },

            async fetchEligibleFellows() {
                if (!this.trackId) {
                    this.eligibleFellows = [];
                    this.leadId = '';
                    this.memberIds = [];
                    return;
                }

                this.isLoading = true;
                try {
                    const response = await fetch(`{{ route('admin.mentorship-pods.eligible-fellows') }}?track_id=${this.trackId}`);
                    this.eligibleFellows = await response.json();
                    
                    // Reset selections if they are no longer in the list
                    if (this.leadId && !this.eligibleFellows.find(f => f.id == this.leadId)) {
                        this.leadId = '';
                    }
                    this.memberIds = this.memberIds.filter(id => this.eligibleFellows.find(f => f.id == id));
                } catch (error) {
                    console.error('Error fetching eligible fellows:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            get totalMembersSelected() {
                let count = this.memberIds.length;
                // If a lead is selected but they aren't explicitly checked in memberIds yet, count them
                if (this.leadId && !this.memberIds.includes(this.leadId) && !this.memberIds.includes(Number(this.leadId))) {
                    count++;
                }
                return count;
            },

            get isValidSize() {
                const total = this.totalMembersSelected;
                return total >= 2 && total <= 4;
            },

            get canSubmit() {
                return this.trackId && this.leadId && this.isValidSize;
            }
        }));
    });
</script>
<style>
    .animate-spin-slow {
        animation: spin 3s linear infinite;
    }
</style>
@endpush
@endsection
