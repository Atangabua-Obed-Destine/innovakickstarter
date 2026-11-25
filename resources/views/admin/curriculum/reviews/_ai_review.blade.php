{{-- AI review panel. Expects $progress, $aiReview (nullable), $aiEnabled, $aiVisible, $pending. --}}
@php
    $r = $aiReview;
    $modeLabel = $r ? (\App\Models\AiReview::MODES[$r->mode] ?? $r->mode) : null;
    $scoreClass = fn ($score) => $score >= 75 ? 'bg-green-500' : ($score >= 50 ? 'bg-amber-500' : 'bg-red-500');
@endphp

<div class="card p-6 border border-primary-500/20" id="ai-review">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h3 class="text-white font-semibold flex items-center gap-2">🤖 AI Review
            @if($r)
                <span class="px-2 py-0.5 rounded text-[10px] uppercase tracking-wider bg-dark-700 text-dark-300">{{ $r->mode }}</span>
            @endif
        </h3>
        @if($aiEnabled && $pending)
            <form method="POST" action="{{ route('admin.curriculum.reviews.ai-run', $progress->id) }}"
                  onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Reviewing… (up to a minute)';">
                @csrf
                <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-dark-700 hover:bg-dark-600 text-dark-200 border border-dark-600">
                    {{ $r ? 'Re-run AI review' : 'Run AI review now' }}
                </button>
            </form>
        @endif
    </div>

    @if(!$r)
        <p class="text-dark-400 text-sm">
            {{ $aiEnabled ? 'This submission has not been reviewed by the AI yet. Reviews run in the background after submission.' : 'AI review is off for this activity.' }}
        </p>
    @elseif(in_array($r->status, ['queued', 'running'], true))
        <p class="text-dark-400 text-sm">The AI review is {{ $r->status === 'queued' ? 'waiting in the queue' : 'running' }}. Refresh in a minute, or run it now.</p>
    @elseif(!$aiVisible)
        <div class="rounded-lg bg-dark-800 p-4 text-sm">
            <p class="text-dark-200">The AI has reviewed this submission. Its verdict stays hidden until you decide, so we can measure honestly how often it agrees with human reviewers.</p>
            <form method="POST" action="{{ route('admin.curriculum.reviews.ai-reveal', $progress->id) }}" class="mt-3"
                  onsubmit="return confirm('Show the AI verdict now? This review will then be left out of the agreement statistics.')">
                @csrf
                <button type="submit" class="text-xs text-primary-400 hover:text-primary-300">Show the AI verdict anyway</button>
            </form>
        </div>
    @else
        {{-- Verdict --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <span class="px-3 py-1 rounded-full text-sm font-semibold border {{ $r->decision_badge_class }}">AI recommends: {{ $r->decision_label }}</span>
            @if($r->weighted_score !== null)
                <span class="text-white font-semibold">{{ number_format($r->weighted_score, 0) }}/100</span>
            @endif
            @php $activityPoints = (int) ($progress->curriculumActivity->points ?? 0); $suggested = $r->suggestedPoints($activityPoints); @endphp
            @if($suggested !== null)
                <span class="px-2 py-0.5 rounded bg-dark-700 text-dark-200 text-sm" title="{{ $r->recommended_decision === \App\Models\AiReview::DECISION_REVISE ? 'Sent back to redo: no points this attempt.' : 'In proportion to the AI score; you decide.' }}">
                    Suggested points: <strong class="text-white">{{ $suggested }}</strong> / {{ $activityPoints }}{{ $r->recommended_decision === \App\Models\AiReview::DECISION_ESCALATE ? ' (your call)' : '' }}
                </span>
            @endif
            @if($r->confidence)
                <span class="text-dark-400 text-xs">confidence: {{ $r->confidence }}</span>
            @endif
            @if($r->human_decision)
                <span class="text-xs px-2 py-0.5 rounded {{ $r->agrees === false ? 'bg-red-500/20 text-red-300' : 'bg-dark-700 text-dark-300' }}">
                    Human: {{ $r->human_decision }}{{ $r->agrees === true ? ' · agreed' : ($r->agrees === false ? ' · disagreed' : '') }}
                </span>
            @endif
        </div>

        @if($r->status === 'failed')
            <div class="mb-4 rounded-lg bg-red-500/10 border border-red-500/30 p-3 text-sm text-red-300">
                The AI review did not complete, so this submission needs a human. {{ \Illuminate\Support\Str::limit($r->error, 300) }}
            </div>
        @endif

        @if(!empty($r->decision_reasons))
            <ul class="mb-4 space-y-1 text-sm text-dark-300 list-disc list-inside">
                @foreach($r->decision_reasons as $reason)
                    <li>{{ $reason }}</li>
                @endforeach
            </ul>
        @endif

        @if($r->summary)
            <p class="text-dark-200 text-sm mb-5">{{ $r->summary }}</p>
        @endif

        {{-- Rubric --}}
        @if(!empty($r->criteria))
            <div class="space-y-4 mb-6">
                @foreach($r->criteria as $c)
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-dark-200 font-medium">{{ $c['rubric_criterion'] ?? $c['criterion'] }} <span class="text-dark-500 font-normal">({{ $c['weight'] ?? '—' }}%)</span></span>
                            <span class="text-white font-semibold">{{ $c['score'] }}</span>
                        </div>
                        <div class="h-1.5 rounded bg-dark-700 overflow-hidden"><div class="h-full {{ $scoreClass($c['score']) }}" style="width: {{ (int) $c['score'] }}%"></div></div>
                        <p class="text-dark-300 text-xs mt-1.5">{{ $c['reasoning'] }}</p>
                        @if(!empty($c['evidence']))
                            <p class="text-dark-500 text-xs mt-1 border-l-2 border-dark-600 pl-2 italic">{{ $c['evidence'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if(!empty($r->missing_requirements))
            <div class="mb-5">
                <p class="text-dark-500 text-xs uppercase tracking-wider mb-1">Missing</p>
                <ul class="text-sm text-dark-300 list-disc list-inside">
                    @foreach($r->missing_requirements as $item)<li>{{ $item }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Integrity --}}
        @php
            $matches = $r->integrity['similarity']['matches'] ?? [];
            $provenance = $r->integrity['provenance'] ?? [];
            $disclosure = $r->integrity['ai_assistance'] ?? null;
        @endphp
        <div class="mb-5 rounded-lg bg-dark-800 p-4 space-y-2 text-sm">
            <p class="text-dark-500 text-xs uppercase tracking-wider">Integrity</p>
            @forelse($matches as $m)
                <p class="text-amber-300">
                    @if($m['type'] === 'same_repository')
                        Same GitHub repository ({{ $m['repo'] ?? '' }}) as
                    @else
                        {{ $m['overlap'] }}% of the text matches
                    @endif
                    <a class="underline" href="{{ route('admin.curriculum.reviews.show', $m['progress_id']) }}" target="_blank">{{ $m['fellow_name'] ?? 'another fellow' }}'s submission</a>.
                    <span class="text-dark-500">Who copied whom is for you to judge.</span>
                </p>
            @empty
                <p class="text-dark-300">No significant overlap with other fellows' submissions{{ ($r->integrity['similarity']['compared'] ?? true) ? '' : ' (too little text to compare)' }}.</p>
            @endforelse
            @foreach($provenance as $finding)
                <p class="{{ ($finding['severity'] ?? '') === 'flag' ? 'text-amber-300' : 'text-dark-400' }}">{{ $finding['message'] }}</p>
            @endforeach
            @if($r->authenticity_concern && $r->authenticity_concern !== 'none')
                <p class="{{ in_array($r->authenticity_concern, ['medium', 'high'], true) ? 'text-amber-300' : 'text-dark-400' }}">Authenticity concern: {{ $r->authenticity_concern }}.</p>
            @endif
            <p class="text-dark-400">AI tools disclosed:
                @if($disclosure === null) not answered
                @elseif($disclosure['used'] ?? false) yes · <span class="text-dark-300">{{ $disclosure['how'] }}</span>
                @else no
                @endif
            </p>
        </div>

        {{-- What was read --}}
        @php $ev = $r->evidence ?? []; @endphp
        <details class="mb-5 text-sm">
            <summary class="cursor-pointer text-dark-400 hover:text-white">What the AI read</summary>
            <div class="mt-3 space-y-2 text-dark-300">
                @foreach($ev['github'] ?? [] as $repo)
                    @if($repo['ok'] ?? false)
                        <p>GitHub <a class="text-primary-400" href="{{ $repo['url'] }}" target="_blank" rel="noopener">{{ $repo['repo'] ?? $repo['url'] }}</a>:
                            {{ count($repo['files'] ?? []) }} files read of {{ $repo['file_count'] ?? count($repo['files'] ?? []) }},
                            {{ $repo['history']['commits_total'] ?? '?' }} commits ({{ $repo['history']['commits_during_activity'] ?? '?' }} during the activity).</p>
                    @endif
                @endforeach
                @foreach($ev['links'] ?? [] as $link)
                    @if($link['ok'] ?? false)
                        <p>Page <a class="text-primary-400" href="{{ $link['url'] }}" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::limit($link['title'] ?: $link['url'], 70) }}</a> (HTTP {{ $link['status'] }}).</p>
                    @endif
                @endforeach
                @foreach($ev['files'] ?? [] as $file)
                    <p>File {{ $file['name'] }} ({{ number_format($file['chars']) }} characters).</p>
                @endforeach
                @foreach($ev['unread'] ?? [] as $item)
                    <p class="text-amber-300">Not read: {{ \Illuminate\Support\Str::limit($item['item'], 80) }} · {{ $item['reason'] }}</p>
                @endforeach
                @foreach($r->checks['failures'] ?? [] as $failure)
                    <p class="text-red-300">Check failed: {{ $failure }}</p>
                @endforeach
            </div>
        </details>

        @if($r->feedback_for_fellow)
            <div class="mb-4">
                <p class="text-dark-500 text-xs uppercase tracking-wider mb-1">Draft feedback for the fellow</p>
                <div class="bg-dark-900 rounded-lg p-3 text-dark-200 text-sm border border-dark-700 whitespace-pre-line">{{ $r->feedback_for_fellow }}</div>
            </div>
        @endif

        @if($r->notes_for_reviewer)
            <div class="mb-4">
                <p class="text-dark-500 text-xs uppercase tracking-wider mb-1">Notes for you</p>
                <p class="text-dark-300 text-sm">{{ $r->notes_for_reviewer }}</p>
            </div>
        @endif

        <p class="text-dark-500 text-xs">
            {{ $r->model }} · prompt {{ $r->prompt_version }} · {{ $r->updated_at?->diffForHumans() }}
            @if($r->duration_ms) · {{ round($r->duration_ms / 1000) }}s @endif
            @if($r->input_tokens) · {{ number_format($r->input_tokens + $r->output_tokens) }} tokens @endif
        </p>
    @endif
</div>
