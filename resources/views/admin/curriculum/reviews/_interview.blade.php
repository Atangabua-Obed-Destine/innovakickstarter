{{-- Mock interview behind this submission: scorecard, transcript (spoken / typed / code), integrity. --}}
@php
    $interviewId = $progress->evidence['interview']['id'] ?? $progress->linked_interview_id;
    $reviewInterview = $interviewId ? \App\Models\InterviewSession::with(['track', 'fellow'])->find($interviewId) : null;
    $attempts = \App\Models\InterviewSession::where('fellow_id', $progress->fellow_id)->where('curriculum_activity_id', $progress->curriculum_activity_id)
        ->orderByDesc('created_at')->get(['id', 'status', 'score', 'hire_signal', 'integrity_level', 'cancellation_reason', 'created_at', 'scoring_status']);
@endphp
<div class="card p-6 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h3 class="text-white font-semibold">🎤 Mock interview</h3>
        @if($reviewInterview)
            <a href="{{ route('admin.interviews.show', $reviewInterview) }}" class="text-primary-400 hover:text-primary-300 text-sm">Open the interview page →</a>
        @endif
    </div>
    @if($attempts->count() > 1)
        <div class="text-sm text-dark-400">
            Attempts:
            @foreach($attempts as $a)
                <a href="{{ route('admin.interviews.show', $a->id) }}" class="text-dark-300 hover:text-white">{{ $a->created_at->format('j M') }}:
                    {{ $a->score !== null ? round((float) $a->score) . '/100' : ($a->cancellation_reason === 'abandoned' ? 'abandoned' : ($a->scoring_status === 'failed' ? 'not scored' : $a->status?->label())) }}{{ $a->integrity_level ? ' · ' . $a->integrity_level : '' }}</a>@if(!$loop->last), @endif
            @endforeach
        </div>
    @endif
    @if($reviewInterview)
        @include('partials.interview.report', ['interview' => $reviewInterview, 'audience' => 'reviewer'])
    @else
        <p class="text-dark-400 text-sm">No interview is linked to this submission.</p>
    @endif
</div>
