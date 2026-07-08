<?php

namespace App\Http\Controllers;

use App\Models\InterviewSession;
use App\Models\Track;
use App\Services\LiveAIInterviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Live Interview Controller
 * 
 * Handles real-time conversational AI interviews with dynamic follow-ups,
 * voice interaction, and natural conversation flow.
 * 
 * Features:
 * - Real-time back-and-forth conversation
 * - Context-aware follow-up questions
 * - Voice input (Web Speech API) + Voice output (TTS)
 * - Conversation memory and flow control
 * - Live feedback and coaching
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class LiveInterviewController extends Controller
{
    public function __construct(
        protected LiveAIInterviewService $liveAIService
    ) {}

    /**
     * Display the live interview lobby/preparation screen.
     */
    public function lobby(Request $request): View
    {
        $user = $request->user();
        $tracks = Track::whereHas('fellowTracks', fn($q) => $q->where('fellow_id', $user->id))->get();
        
        if ($tracks->isEmpty()) {
            $tracks = Track::take(3)->get();
        }

        return view('fellow.interviews.live.lobby', [
            'tracks' => $tracks,
            'interviewTypes' => \App\Enums\InterviewType::cases(),
            'user' => $user,
            'preselectedTrack' => $request->attributes->get('activeTrack')?->track_id,
        ]);
    }

    /**
     * Start a new live interview session.
     */
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'track_id' => ['required', 'exists:tracks,id'],
            'type' => ['required', 'string'],
            'difficulty' => ['nullable', 'string', 'in:beginner,intermediate,advanced'],
            'is_practice' => ['nullable', 'boolean'],
            'curriculum_activity_id' => ['nullable', 'exists:track_curriculum_activities,id'],
            'curriculum_progress_id' => ['nullable', 'exists:fellow_curriculum_progress,id'],
        ]);

        $user = $request->user();
        $track = Track::findOrFail($validated['track_id']);
        $type = \App\Enums\InterviewType::from($validated['type']);
        $difficulty = $validated['difficulty'] ?? 'intermediate';

        // Create the interview session
        $interview = InterviewSession::create([
            'fellow_id' => $user->id,
            'track_id' => $track->id,
            'type' => $type,
            'mode' => \App\Enums\InterviewMode::AI,
            'status' => \App\Enums\InterviewStatus::IN_PROGRESS,
            'difficulty' => $difficulty,
            'is_practice' => $validated['is_practice'] ?? false,
            'scheduled_at' => now(),
            'started_at' => now(),
            'curriculum_activity_id' => $validated['curriculum_activity_id'] ?? null,
            'curriculum_progress_id' => $validated['curriculum_progress_id'] ?? null,
        ]);

        // Initialize conversation with opening
        $opening = $this->liveAIService->startConversation($interview, $track, $type, $difficulty);

        return response()->json([
            'success' => true,
            'interview_id' => $interview->id,
            'opening' => $opening,
            'redirect' => route('interviews.live.room', $interview),
        ]);
    }

    /**
     * Display the live interview room.
     */
    public function room(InterviewSession $interview): View
    {
        $this->authorize('view', $interview);

        if ($interview->status === \App\Enums\InterviewStatus::COMPLETED) {
            return redirect()->route('interviews.show', $interview);
        }

        // Load conversation history if resuming
        $conversation = $this->liveAIService->getConversation($interview);

        // Load curriculum context if this is a curriculum-linked interview
        $curriculumActivity = $interview->curriculum_activity_id
            ? \App\Models\TrackCurriculumActivity::find($interview->curriculum_activity_id)
            : null;

        return view('fellow.interviews.live.room', [
            'interview' => $interview,
            'conversation' => $conversation,
            'curriculumActivity' => $curriculumActivity,
        ]);
    }

    /**
     * Send a message in the live interview (API endpoint).
     * This is the core of the conversational experience.
     */
    public function sendMessage(Request $request, InterviewSession $interview): JsonResponse
    {
        $this->authorize('update', $interview);

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1'],
            'input_mode' => ['nullable', 'string', 'in:text,voice'],
            'audio_duration' => ['nullable', 'numeric'],
        ]);

        try {
            // Get AI response with context awareness
            $response = $this->liveAIService->processMessage(
                interview: $interview,
                userMessage: $validated['message'],
                inputMode: $validated['input_mode'] ?? 'text'
            );

            return response()->json($response);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SendMessage Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => "I appreciate your response. Could you elaborate a bit more on that?",
                'type' => 'follow_up',
                'error_debug' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }

    /**
     * Request a hint during the interview.
     */
    public function getHint(Request $request, InterviewSession $interview): JsonResponse
    {
        $this->authorize('view', $interview);

        $hint = $this->liveAIService->generateHint($interview);

        return response()->json([
            'hint' => $hint,
            'hints_used' => ($interview->hints_used ?? 0) + 1,
        ]);
    }

    /**
     * Request to skip the current question.
     */
    public function skipQuestion(InterviewSession $interview): JsonResponse
    {
        $this->authorize('update', $interview);

        $response = $this->liveAIService->skipToNextQuestion($interview);

        return response()->json($response);
    }

    /**
     * End the live interview and get final evaluation.
     */
    public function end(InterviewSession $interview): JsonResponse
    {
        $this->authorize('update', $interview);

        $results = $this->liveAIService->endInterview($interview);

        $interview->update([
            'status' => \App\Enums\InterviewStatus::COMPLETED,
            'completed_at' => now(),
            'score' => $results['overall_score'],
            'overall_score' => $results['overall_score'],
            'ai_feedback' => $results['summary'],
        ]);

        // If this interview is linked to a curriculum activity, handle auto-progression
        if ($interview->isLinkedToCurriculum()) {
            try {
                $curriculumService = app(\App\Services\CurriculumService::class);
                $curriculumService->handleInterviewCompletion($interview);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to handle curriculum interview completion in live end", [
                    'interview_id' => $interview->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $redirectUrl = $interview->isLinkedToCurriculum()
            ? route('curriculum.activity.show', $interview->curriculum_activity_id)
            : route('interviews.show', $interview);

        return response()->json([
            'success' => true,
            'results' => $results,
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * Get interview progress and stats.
     */
    public function getProgress(InterviewSession $interview): JsonResponse
    {
        $this->authorize('view', $interview);

        $progress = $this->liveAIService->getProgress($interview);

        return response()->json($progress);
    }

    /**
     * Get available TTS voices for the interview.
     */
    public function getVoices(): JsonResponse
    {
        return response()->json([
            'voices' => [
                ['id' => 'default', 'name' => 'Professional (Default)', 'gender' => 'neutral'],
                ['id' => 'friendly', 'name' => 'Friendly Mentor', 'gender' => 'female'],
                ['id' => 'formal', 'name' => 'Formal Interviewer', 'gender' => 'male'],
            ],
            'default' => 'default',
        ]);
    }
}
