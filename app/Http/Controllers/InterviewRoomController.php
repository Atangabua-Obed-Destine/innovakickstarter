<?php

namespace App\Http\Controllers;

use App\Enums\InterviewStatus;
use App\Models\InterviewSession;
use App\Services\Interview\InterviewEngine;
use App\Services\Interview\InterviewException;
use App\Services\Interview\SpeechMetrics;
use App\Services\Interview\WhisperTranscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The AI interview room: briefing, one question at a time, voice transcription,
 * integrity events, finishing and the "scoring your interview" wait.
 * Every endpoint is for the session's own fellow only.
 */
class InterviewRoomController extends Controller
{
    public function __construct(protected InterviewEngine $engine) {}

    public function room(Request $request, InterviewSession $interview)
    {
        $this->owner($request, $interview);

        if (!$this->engine->isEngine($interview)) {
            return redirect()->route('interviews.show', $interview)
                ->with('error', $interview->status === InterviewStatus::IN_PROGRESS
                    ? 'This interview was started with the old interview room and cannot be continued. Cancel it and start a new one.'
                    : 'This interview is not open.');
        }
        if ($this->engine->isExpired($interview)) {
            $this->engine->expire($interview);
        }
        if ($interview->status !== InterviewStatus::IN_PROGRESS) {
            return redirect()->route('interviews.show', $interview);
        }

        $interview->loadMissing(['track', 'curriculumActivity']);
        $practice = (array) ($interview->track?->experience['practice'] ?? []);

        return view('fellow.interviews.room', [
            'interview' => $interview,
            'briefing' => $this->engine->briefing($interview),
            'sandbox' => [
                'sqlTables' => collect((array) ($practice['sql_tables'] ?? []))->map(fn ($u) => url($u))->all(),
                'pythonFiles' => collect((array) ($practice['python_files'] ?? []))->map(fn ($u) => url($u))->all(),
                'jsCanonUrl' => asset('course-files/practice/practice_canon.js'),
                'canonUrl' => asset('course-files/practice/practice_canon.py'),
            ],
            'transcription' => app(WhisperTranscriber::class)->available(),
        ]);
    }

    public function begin(Request $request, InterviewSession $interview): JsonResponse
    {
        $this->owner($request, $interview);
        $request->validate(['consent' => ['accepted']]);

        return $this->run(fn () => $this->engine->begin($interview));
    }

    public function state(Request $request, InterviewSession $interview): JsonResponse
    {
        $this->owner($request, $interview);

        return $this->run(function () use ($interview) {
            if ($this->engine->isExpired($interview)) {
                $this->engine->expire($interview);
            }
            if ($interview->status === InterviewStatus::IN_PROGRESS && $this->engine->timeUp($interview)) {
                return $this->engine->finish($interview, 'time_up');
            }

            return $this->engine->publicState($interview->fresh());
        });
    }

    public function answer(Request $request, InterviewSession $interview): JsonResponse
    {
        $this->owner($request, $interview);
        $data = $request->validate([
            'turn' => ['required', 'integer', 'min:1'],
            'answer' => ['nullable', 'string', 'max:8000'],
            'code' => ['nullable', 'string', 'max:20000'],
            'explanation_mode' => ['nullable', 'in:voice,typed'],
            'transcription_source' => ['nullable', 'in:groq,browser,none'],
            'voice_seconds' => ['nullable', 'numeric', 'min:0', 'max:900'],
            'run_output' => ['nullable', 'string', 'max:4000'],
            'mic_off' => ['nullable', 'boolean'],
            'skip' => ['nullable', 'boolean'],
            'stats' => ['nullable', 'array'],
            'stats.keystrokes' => ['nullable', 'integer', 'min:0'],
            'stats.max_burst' => ['nullable', 'integer', 'min:0'],
            'events' => ['nullable', 'array', 'max:200'],
        ]);

        return $this->run(fn () => $this->engine->answer($interview, $data));
    }

    public function finish(Request $request, InterviewSession $interview): JsonResponse
    {
        $this->owner($request, $interview);
        $request->validate(['events' => ['nullable', 'array', 'max:200']]);
        if ($request->filled('events')) {
            $this->engine->recordEvents($interview, $request->input('events'));
        }

        return $this->run(fn () => $this->engine->finish($interview, $request->input('reason') === 'time_up' ? 'time_up' : 'fellow_ended'));
    }

    /** Integrity events and heartbeats (also sent with navigator.sendBeacon when the page closes). */
    public function events(Request $request, InterviewSession $interview): JsonResponse
    {
        $this->owner($request, $interview);
        $events = $request->input('events');
        if (is_string($events) && strlen($events) > 60000) {
            return response()->json(['ok' => false], 413);
        }
        $this->engine->recordEvents($interview, $events ?? []);

        return response()->json(['ok' => true]);
    }

    /**
     * Speech to text with Groq Whisper. The audio is never stored. On any failure the
     * browser uses its own speech recognition text ({fallback: true}, HTTP 503).
     */
    public function transcribe(Request $request, InterviewSession $interview, WhisperTranscriber $whisper): JsonResponse
    {
        $this->owner($request, $interview);
        if ($interview->status !== InterviewStatus::IN_PROGRESS || $this->engine->isExpired($interview)) {
            return response()->json(['message' => 'This interview is not in progress.'], 409);
        }
        $request->validate([
            'audio' => ['required', 'file', 'max:' . (WhisperTranscriber::MAX_BYTES / 1024),
                'mimetypes:audio/webm,video/webm,audio/ogg,video/ogg,application/ogg,audio/mp4,video/mp4,audio/x-m4a,audio/m4a,audio/aac,audio/wav,audio/x-wav,audio/wave,audio/vnd.wave,audio/mpeg'],
            'turn' => ['required', 'integer', 'min:0'],
            'duration_seconds' => ['nullable', 'numeric', 'min:0', 'max:' . (WhisperTranscriber::MAX_SECONDS + 5)],
        ]);
        $turn = (int) $request->input('turn');
        if ($turn !== 0 && $turn !== (int) $interview->current_turn) {
            return response()->json(['message' => 'This recording is for another question.'], 409);
        }

        try {
            $result = $whisper->transcribe($request->file('audio'), $whisper->promptFor((array) $interview->context));
        } catch (\Throwable $e) {
            // No user data in the log: only what went wrong.
            Log::warning('Interview transcription failed', ['interview' => $interview->id, 'error' => mb_substr($e->getMessage(), 0, 160)]);

            return response()->json(['fallback' => true, 'message' => 'Transcription is not available right now.'], 503);
        }

        $seconds = $result['duration_seconds'] ?? (is_numeric($request->input('duration_seconds')) ? (float) $request->input('duration_seconds') : null);
        if ($seconds !== null && $seconds > WhisperTranscriber::MAX_SECONDS + 5) {
            return response()->json(['message' => 'Recordings can be at most 5 minutes. Please answer in shorter parts.'], 422);
        }
        $words = SpeechMetrics::words($result['text']);
        if ($turn > 0) {
            $this->engine->recordTranscription($interview, $turn, $seconds, $words);
        }

        return response()->json([
            'text' => $result['text'],
            'duration_seconds' => $seconds,
            'words_per_minute' => $seconds ? SpeechMetrics::wpm($words, $seconds) : null,
        ]);
    }

    public function scoreStatus(Request $request, InterviewSession $interview): JsonResponse
    {
        $this->owner($request, $interview);

        // If no queue worker picked the scoring up after a few minutes, run it after this response.
        if ($interview->status === InterviewStatus::COMPLETED && $interview->scoring_status === 'queued'
            && $interview->completed_at && $interview->completed_at->lt(now()->subMinutes(3))
            && \Illuminate\Support\Facades\Cache::add('interview-score-fallback:' . $interview->id, 1, now()->addMinutes(10))) {
            \App\Jobs\ScoreInterview::dispatchAfterResponse($interview->id);
        }

        return response()->json([
            'status' => $interview->status?->value,
            'scoring_status' => $interview->scoring_status,
            'ready' => in_array($interview->scoring_status, ['done', 'failed', 'manual'], true) || $interview->status !== InterviewStatus::COMPLETED,
            'url' => route('interviews.show', $interview),
        ]);
    }

    protected function owner(Request $request, InterviewSession $interview): void
    {
        abort_unless($request->user()?->id === $interview->fellow_id, 403);
    }

    protected function run(callable $fn): JsonResponse
    {
        try {
            return response()->json($fn());
        } catch (InterviewException $e) {
            return response()->json(['message' => $e->getMessage(), 'reason' => $e->reason], $e->status);
        }
    }
}
