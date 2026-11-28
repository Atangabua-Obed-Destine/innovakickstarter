{{--
    Mock interview activity, in progress: launch / continue / wait for the mentor / retake rules.
    Needs: $activity, $activeSession, $interviewStanding, $minScore, $requiredCount, $bestScore, $completedSessions
--}}
@php
    $standing = $interviewStanding ?? [];
    $retakeAt = $standing['retake_at'] ?? null;
    $isHumanActivity = in_array($activity->interview_config['mode'] ?? 'ai', ['human', 'peer'], true);
@endphp
<div class="space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 bg-purple-500 rounded-full animate-pulse"></span>
                <h3 class="text-lg font-semibold text-purple-400">🎤 {{ $isHumanActivity ? 'Interview with a mentor' : 'Mock interview' }}</h3>
            </div>
            <p class="text-dark-400 text-sm">
                Pass mark: {{ $minScore }}/100{{ $requiredCount > 1 ? ' in ' . $requiredCount . ' sessions' : '' }}.
                @unless($isHumanActivity) The interviewer writes; you answer out loud (code and SQL in an editor). Full screen, no camera. @endunless
            </p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            @if($activeSession)
                @if($activeSession->mode === \App\Enums\InterviewMode::AI)
                    <a href="{{ route('interviews.room', $activeSession) }}" class="btn-primary text-lg px-6 py-3 flex-shrink-0">🎤 {{ empty($activeSession->plan['begun_at']) ? 'Open the briefing' : 'Continue interview' }}</a>
                    @if(empty($activeSession->plan['begun_at']))
                        <form action="{{ route('interviews.cancel', $activeSession) }}" method="POST" onsubmit="return confirm('Cancel this interview before it starts?');">
                            @csrf
                            <button type="submit" class="btn-secondary px-4 py-3">Cancel</button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('interviews.show', $activeSession) }}" class="btn-primary text-lg px-6 py-3 flex-shrink-0">📋 View interview</a>
                @endif
            @elseif($retakeAt)
                <span class="btn-secondary px-4 py-3" style="cursor: default; opacity: .8;">⏳ Retake from {{ $retakeAt->format('D j M, g:i A') }}</span>
            @else
                <form action="{{ route('curriculum.activity.interview', $activity) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary text-lg px-6 py-3">
                        🎤 {{ $isHumanActivity ? 'Request the interview' : ($completedSessions->count() > 0 ? 'Retake the interview' : 'Start the interview') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($activeSession && $activeSession->mode !== \App\Enums\InterviewMode::AI)
        <div class="p-4 bg-dark-800/50 rounded-xl border border-dark-700 text-sm text-dark-300">
            @if($activeSession->status === \App\Enums\InterviewStatus::SCHEDULED && $activeSession->scheduled_at)
                📅 <span class="text-white font-medium">{{ $activeSession->scheduled_at->format('D j M Y, g:i A') }}</span>
                with {{ $activeSession->interviewer?->name ?? 'a mentor' }}.
                @if($activeSession->meeting_link)
                    <a href="{{ $activeSession->meeting_link }}" target="_blank" rel="noopener" class="text-primary-400 hover:text-primary-300">Meeting link</a>
                @endif
            @else
                ⏳ Waiting for the team to assign a mentor, a time and a meeting link. You will get a notification.
            @endif
        </div>
    @endif

    <div class="p-4 bg-dark-800/50 rounded-xl border border-dark-700 space-y-3">
        <div class="flex items-center justify-between text-sm flex-wrap gap-2">
            <span class="text-dark-400">Scored attempts: <span class="text-white font-medium">{{ $standing['counted'] ?? 0 }}</span></span>
            <span class="text-dark-400">Best score:
                <span class="{{ $bestScore >= $minScore ? 'text-green-400' : 'text-amber-400' }} font-bold">{{ $completedSessions->count() ? round($bestScore) : '—' }}</span>
                <span class="text-dark-500">/ {{ $minScore }} to pass</span>
            </span>
        </div>
        <div class="w-full bg-dark-700 rounded-full h-2.5">
            <div class="h-2.5 rounded-full transition-all {{ $bestScore >= $minScore ? 'bg-green-500' : 'bg-amber-500' }}"
                 style="width: {{ min(100, ($bestScore / max($minScore, 1)) * 100) }}%"></div>
        </div>
        @if(!empty($standing['passed']))
            <p class="text-green-400 text-sm font-medium">✅ Passed. Your interview is with a reviewer now.</p>
        @elseif($retakeAt)
            <p class="text-amber-400 text-sm">Not passed this time. You can retake it 24 hours after your attempt, with new questions. Work on the next steps in your scorecard first.</p>
        @endif
        @if(!empty($standing['needs_mentor']) && empty($standing['passed']))
            <p class="text-sm" style="color: #fca5a5;">You have tried this interview {{ $standing['failed_count'] }} times. <a href="{{ route('help.index') }}" class="text-primary-400 hover:text-primary-300 font-medium">Book a session with a mentor</a>: they will go through your scorecards with you before the next attempt.</p>
        @endif
    </div>
</div>
