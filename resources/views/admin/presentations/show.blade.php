@extends('layouts.app')

@section('title', 'Review: ' . $presentation->title)

@section('content')
@php
    $categories = \App\Models\Presentation::CATEGORIES;
    $linkLabels = ['slides' => 'Slides', 'recording' => 'Recording', 'repository' => 'Repository', 'other' => 'Link'];
    $pending = $presentation->status === \App\Models\Presentation::STATUS_SUBMITTED;
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
    $oldCategories = old('categories', []);
    $oldBoosts = old('boosts', []);
@endphp
<div class="max-w-5xl mx-auto space-y-6">
    <a href="{{ route('admin.presentations.index') }}" class="inline-flex items-center gap-2 text-dark-400 hover:text-white transition-colors">← Presentations</a>

    @foreach(['success' => 'green', 'error' => 'red'] as $key => $color)
        @if(session($key))
            <div class="p-4 rounded-xl border bg-{{ $color }}-500/10 border-{{ $color }}-500/30 text-{{ $color }}-400">{{ session($key) }}</div>
        @endif
    @endforeach
    @if($errors->any())
        <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card p-6">
        <div class="flex flex-wrap items-center gap-2 mb-2">
            <span class="badge {{ $presentation->statusBadgeClass() }}">{{ $presentation->statusLabel() }}</span>
            <span class="badge badge-primary">{{ $presentation->kindLabel() }}</span>
            @if($presentation->submission_count > 1)
                <span class="text-dark-400 text-sm">Submission {{ $presentation->submission_count }}</span>
            @endif
        </div>
        <h1 class="text-2xl font-bold text-white">{{ $presentation->title }}</h1>
        <p class="text-dark-400 mt-1">
            {{ $presentation->fellow->name }} · {{ $presentation->track->name }} · {{ $presentation->event_name }} · given {{ $presentation->presented_at?->format('M j, Y') }}
            · submitted {{ $presentation->submitted_at?->format('M j, Y') }}
        </p>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- What the fellow submitted --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6 space-y-4">
                <div>
                    <h3 class="text-white font-semibold mb-1">What was presented</h3>
                    <p class="text-dark-200 whitespace-pre-line">{{ $presentation->summary }}</p>
                </div>
                @if($presentation->learnings)
                    <div>
                        <h3 class="text-white font-semibold mb-1">What they learned</h3>
                        <p class="text-dark-200 whitespace-pre-line">{{ $presentation->learnings }}</p>
                    </div>
                @endif
                <div class="grid sm:grid-cols-2 gap-3 text-sm">
                    <div class="p-3 bg-dark-800 rounded-lg"><span class="text-dark-400">Audience:</span> <span class="text-white">{{ $presentation->audience ?: 'Not given' }}</span></div>
                    <div class="p-3 bg-dark-800 rounded-lg"><span class="text-dark-400">Co-presenters:</span> <span class="text-white">{{ $presentation->co_presenters ?: 'None' }}</span></div>
                </div>
            </div>

            <div class="card p-6">
                <h3 class="text-white font-semibold mb-3">Proof</h3>
                <div class="space-y-2">
                    @foreach($presentation->links ?? [] as $key => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between bg-dark-800 rounded-lg px-4 py-2 text-sm hover:bg-dark-700 transition">
                            <span class="text-dark-300">{{ $linkLabels[$key] ?? 'Link' }}</span>
                            <span class="text-primary-400 truncate ml-3">{{ $url }}</span>
                        </a>
                    @endforeach
                    @foreach($presentation->files ?? [] as $i => $path)
                        <a href="{{ route('admin.presentations.file', [$presentation, $i]) }}" class="flex items-center justify-between bg-dark-800 rounded-lg px-4 py-2 text-sm hover:bg-dark-700 transition">
                            <span class="text-dark-300">File</span>
                            <span class="text-primary-400 truncate ml-3">{{ basename($path) }}</span>
                        </a>
                    @endforeach
                    @if(empty($presentation->links) && empty($presentation->files))
                        <p class="text-dark-500 text-sm italic">No links or files.</p>
                    @endif
                </div>
            </div>

            @if($otherPresentations->isNotEmpty())
                <div class="card p-6">
                    <h3 class="text-white font-semibold mb-3">Their other presentations</h3>
                    <div class="space-y-2">
                        @foreach($otherPresentations as $other)
                            <a href="{{ route('admin.presentations.show', $other) }}" class="flex items-center justify-between bg-dark-800 rounded-lg px-4 py-2 text-sm hover:bg-dark-700 transition">
                                <span class="text-dark-200 truncate">{{ $other->title }} <span class="text-dark-500">· {{ $other->presented_at?->format('M j, Y') }}</span></span>
                                <span class="badge {{ $other->statusBadgeClass() }} ml-3">{{ $other->statusLabel() }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Review --}}
        <div class="space-y-6">
            @if($pending)
                @if(!$basis)
                    <div class="card p-6 text-amber-400 text-sm">{{ $presentation->fellow->name }} is no longer enrolled in {{ $presentation->track->name }}, so this presentation can't add to that track's score. Send it back or mark it not accepted.</div>
                @endif
                <form method="POST" action="{{ route('admin.presentations.review', $presentation) }}" class="card p-6 space-y-5"
                      x-data="presentationReview(@js($basis), @js($oldCategories), @js((object) $oldBoosts))">
                    @csrf
                    <div>
                        <h3 class="text-white font-semibold">Career Capital</h3>
                        <p class="text-dark-400 text-sm mt-1">Tick each category this presentation showed, and choose how much it adds. Each category is out of 100.</p>
                    </div>

                    <div class="space-y-3">
                        @foreach($categories as $key => $label)
                            <div class="p-3 bg-dark-800 rounded-lg" :class="checked.includes('{{ $key }}') ? 'border border-primary-500/40' : ''">
                                <label class="flex items-center justify-between gap-3 cursor-pointer">
                                    <span class="flex items-center gap-2">
                                        <input type="checkbox" name="categories[]" value="{{ $key }}" x-model="checked" class="accent-primary-500" @disabled(!$basis)>
                                        <span class="text-white text-sm">{{ $label }}</span>
                                    </span>
                                    @if($basis)
                                        <span class="text-dark-400 text-xs">now {{ $fmt($basis['categories'][$key] ?? 0) }} / 100</span>
                                    @endif
                                </label>
                                <div x-show="checked.includes('{{ $key }}')" x-cloak class="mt-2 flex items-center gap-2">
                                    <span class="text-dark-400 text-sm">Add</span>
                                    <input type="number" name="boosts[{{ $key }}]" min="0" max="{{ \App\Services\PresentationService::MAX_BOOST_PER_CATEGORY }}" step="0.5"
                                           x-model.number="boosts['{{ $key }}']"
                                           class="w-24 bg-dark-900 border border-dark-600 rounded-lg px-3 py-1.5 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                    <span class="text-dark-400 text-sm">→ <span class="text-white" x-text="fmt(after('{{ $key }}'))"></span> / 100</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($basis)
                        <div class="p-4 rounded-lg border border-dark-600 bg-dark-900/40">
                            <p class="text-dark-400 text-xs uppercase tracking-wider mb-2">Effect on {{ $presentation->fellow->name }}'s score ({{ $presentation->track->name }})</p>
                            <div class="flex items-baseline gap-3">
                                <span class="text-2xl font-bold text-white" x-text="fmt(basis.total)"></span>
                                <span class="text-dark-400">→</span>
                                <span class="text-2xl font-bold" :class="newTotal() > basis.total ? 'text-green-400' : 'text-white'" x-text="fmt(newTotal())"></span>
                                <span class="text-green-400 text-sm" x-show="newTotal() > basis.total" x-text="'+' + fmt(newTotal() - basis.total)"></span>
                            </div>
                            <p class="text-sm mt-1">
                                <span class="text-dark-400">Tier:</span>
                                <span class="text-white" x-text="tier(basis.total)"></span>
                                <template x-if="tier(newTotal()) !== tier(basis.total)">
                                    <span>→ <span class="text-green-400 font-semibold" x-text="tier(newTotal())"></span></span>
                                </template>
                            </p>
                            <p class="text-dark-500 text-xs mt-2">
                                The total is out of 100: {{ collect($basis['tiers'])->map(fn ($t) => $t['label'] . ' from ' . $fmt($t['min']))->implode(', ') }}.
                                Weights: {{ collect($basis['weights'])->map(fn ($w, $k) => ($categories[$k] ?? $k) . ' ' . $fmt($w) . '%')->implode(', ') }}.
                                @if($basis['decay'] < 1) Includes the fellow's current inactivity decay (×{{ $basis['decay'] }}). @endif
                            </p>
                        </div>
                    @endif

                    <div>
                        <label class="block text-dark-300 text-sm mb-1">Feedback for the fellow</label>
                        <textarea name="feedback" rows="4" maxlength="3000" placeholder="What was strong, what to improve. Required when sending back or not accepting."
                                  class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">{{ old('feedback') }}</textarea>
                    </div>

                    <div class="flex flex-col gap-2">
                        <button type="submit" name="decision" value="approve" class="btn btn-primary w-full" @disabled(!$basis)
                                :disabled="!canApprove()" :class="!canApprove() ? 'opacity-50 cursor-not-allowed' : ''"
                                @click="if (!confirm('Approve and add ' + fmt(newTotal() - basis.total) + ' to the score?')) $event.preventDefault()">
                            Approve and update score
                        </button>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="submit" name="decision" value="changes" class="btn-secondary w-full">Request changes</button>
                            <button type="submit" name="decision" value="reject" class="btn-secondary w-full text-red-400"
                                    onclick="return confirm('Mark this presentation as not accepted?')">Not accepted</button>
                        </div>
                    </div>
                </form>
            @else
                <div class="card p-6 space-y-3">
                    <h3 class="text-white font-semibold">Review</h3>
                    @if($presentation->status === \App\Models\Presentation::STATUS_APPROVED)
                        @foreach($presentation->category_boosts ?? [] as $key => $amount)
                            <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg text-sm">
                                <span class="text-dark-300">{{ $categories[$key] ?? $key }}</span>
                                <span class="text-green-400 font-semibold">+{{ $fmt($amount) }}</span>
                            </div>
                        @endforeach
                    @endif
                    @if($presentation->review_feedback)
                        <p class="text-dark-200 text-sm whitespace-pre-line">{{ $presentation->review_feedback }}</p>
                    @endif
                    @if($presentation->reviewer)
                        <p class="text-dark-500 text-xs">By {{ $presentation->reviewer->name }}{{ $presentation->reviewed_at ? ' on ' . $presentation->reviewed_at->format('M j, Y') : '' }}</p>
                    @endif
                    @if($presentation->status === \App\Models\Presentation::STATUS_CHANGES_REQUESTED)
                        <p class="text-amber-400 text-sm">Waiting for the fellow to update and resubmit.</p>
                    @endif
                </div>

                @if(in_array($presentation->status, [\App\Models\Presentation::STATUS_APPROVED, \App\Models\Presentation::STATUS_REJECTED], true))
                    <form method="POST" action="{{ route('admin.presentations.reopen', $presentation) }}" class="card p-6 space-y-3"
                          onsubmit="return confirm('Reopen this review? Anything it added to the score is removed until it is reviewed again.')">
                        @csrf
                        <h3 class="text-white font-semibold">Reopen review</h3>
                        <p class="text-dark-400 text-sm">Puts it back in the queue{{ $presentation->status === \App\Models\Presentation::STATUS_APPROVED ? ' and removes what it added to the score' : '' }}.</p>
                        <textarea name="reason" rows="2" required maxlength="1000" placeholder="Reason (shown to the fellow)"
                                  class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"></textarea>
                        <button type="submit" class="btn-secondary w-full">Reopen review</button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function presentationReview(basis, checked, boosts) {
    return {
        basis: basis || { categories: {}, weights: {}, decay: 1, total: 0, tiers: [] },
        checked: checked || [],
        boosts: Object.assign({}, boosts || {}),
        added(key) {
            const v = parseFloat(this.boosts[key]);
            return this.checked.includes(key) && v > 0 ? Math.min(v, 100) : 0;
        },
        after(key) {
            return Math.min(100, (this.basis.categories[key] || 0) + this.added(key));
        },
        newTotal() {
            let weighted = 0;
            for (const [key, weight] of Object.entries(this.basis.weights)) {
                weighted += this.after(key) * weight / 100;
            }
            return Math.max(0, Math.min(100, weighted * this.basis.decay));
        },
        tier(score) {
            let label = this.basis.tiers.length ? this.basis.tiers[0].label : '';
            for (const t of this.basis.tiers) if (score >= t.min) label = t.label;
            return label;
        },
        canApprove() {
            return this.checked.some(key => this.added(key) > 0);
        },
        fmt(n) {
            return (Math.round(n * 100) / 100).toString();
        },
    };
}
</script>
@endpush
@endsection
