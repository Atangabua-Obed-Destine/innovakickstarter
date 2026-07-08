<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleInterviewRequest;
use App\Models\InterviewSession;
use App\Models\Track;
use App\Services\InterviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Interview Controller
 * 
 * Handles mock interview scheduling and management for fellows.
 * Supports AI, Human, and Peer interview types.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class InterviewController extends Controller
{
    public function __construct(
        protected InterviewService $interviewService
    ) {}

    /**
     * Display listing of fellow's interviews.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Upcoming interviews
        $upcoming = InterviewSession::where('fellow_id', $user->id)
            ->upcoming()
            ->with('track')
            ->orderBy('scheduled_at')
            ->get();

        // Past interviews
        $past = InterviewSession::where('fellow_id', $user->id)
            ->whereIn('status', ['completed', 'cancelled', 'no_show'])
            ->with(['track', 'interviewer'])
            ->orderByDesc('scheduled_at')
            ->paginate(10);

        // Interview statistics
        $stats = [
            'total_completed' => InterviewSession::where('fellow_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'average_score' => InterviewSession::where('fellow_id', $user->id)
                ->where('status', 'completed')
                ->whereNotNull('score')
                ->avg('score'),
            'this_week' => InterviewSession::where('fellow_id', $user->id)
                ->where('scheduled_at', '>=', now()->startOfWeek())
                ->count(),
        ];

        // Check weekly limits
        $canSchedule = $this->interviewService->canSchedule($user, \App\Enums\InterviewMode::AI);

        return view('fellow.interviews.index', [
            'upcoming' => $upcoming,
            'past' => $past,
            'stats' => $stats,
            'canSchedule' => $canSchedule,
        ]);
    }

    /**
     * Show the interview scheduling form.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        // Get user's tracks
        $tracks = Track::whereHas('fellowTracks', function ($q) use ($user) {
            $q->where('fellow_id', $user->id);
        })->get();

        // Get interview types from enum
        $interviewTypes = \App\Enums\InterviewType::cases();
        $interviewModes = \App\Enums\InterviewMode::cases();

        // Check availability for each mode (AI, human)
        $availability = [];
        foreach ($interviewModes as $mode) {
            $availability[$mode->value] = $this->interviewService->canSchedule($user, $mode);
        }

        // Get available time slots (simplified - could be more complex)
        $availableSlots = $this->getAvailableSlots();

        return view('fellow.interviews.create', [
            'tracks' => $tracks,
            'interviewTypes' => $interviewTypes,
            'interviewModes' => $interviewModes,
            'availability' => $availability,
            'availableSlots' => $availableSlots,
            'preselectedTrack' => $request->attributes->get('activeTrack')?->track_id,
        ]);
    }

    /**
     * Schedule a new interview.
     */
    public function store(ScheduleInterviewRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $track = \App\Models\Track::findOrFail($validated['track_id']);
        $interview = $this->interviewService->schedule(
            fellow: $user,
            track: $track,
            type: \App\Enums\InterviewType::from($validated['type']),
            mode: \App\Enums\InterviewMode::from($validated['mode']),
            options: [
                'scheduled_at' => \Carbon\Carbon::parse($validated['scheduled_at']),
                'duration_minutes' => $validated['duration_minutes'],
                'focus_areas' => $validated['focus_areas'] ?? [],
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()->route('interviews.show', $interview)
            ->with('success', 'Interview scheduled successfully! You\'ll receive a reminder before it starts.');
    }

    /**
     * Display a specific interview.
     */
    public function show(InterviewSession $interview): View
    {
        $this->authorize('view', $interview);

        $interview->load(['track', 'fellow', 'interviewer']);

        return view('fellow.interviews.show', [
            'interview' => $interview,
            'canStart' => $this->canStartInterview($interview),
            'canCancel' => $this->canCancelInterview($interview),
        ]);
    }

    /**
     * Start an interview session.
     */
    public function start(InterviewSession $interview): RedirectResponse
    {
        $this->authorize('update', $interview);

        if (!$this->canStartInterview($interview)) {
            return redirect()->route('interviews.show', $interview)
                ->with('error', 'This interview cannot be started yet.');
        }

        $interview = $this->interviewService->start($interview);

        // Redirect to interview room based on mode
        if ($interview->mode === \App\Enums\InterviewMode::AI) {
            return redirect()->route('interviews.ai-room', $interview);
        }

        // For human/peer interviews, redirect to meeting link or waiting room
        if ($interview->meeting_link) {
            return redirect()->away($interview->meeting_link);
        }

        return redirect()->route('interviews.room', $interview);
    }

    /**
     * Cancel an interview.
     */
    public function cancel(Request $request, InterviewSession $interview): RedirectResponse
    {
        $this->authorize('update', $interview);

        if (!$this->canCancelInterview($interview)) {
            return redirect()->route('interviews.show', $interview)
                ->with('error', 'This interview cannot be cancelled.');
        }

        $reason = $request->input('reason', 'Cancelled by user');
        $interview = $this->interviewService->cancel($interview, $reason);

        return redirect()->route('interviews.index')
            ->with('success', 'Interview cancelled successfully.');
    }

    /**
     * Complete an interview (for mentors/after AI interview).
     */
    public function complete(Request $request, InterviewSession $interview): RedirectResponse
    {
        $this->authorize('complete', $interview);

        $validated = $request->validate([
            'scores' => ['required', 'array'],
            'scores.*' => ['required', 'numeric', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string', 'max:5000'],
            'filler_word_count' => ['nullable', 'integer', 'min:0'],
            'speaking_pace_wpm' => ['nullable', 'integer', 'min:0'],
            'confidence_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $interview = $this->interviewService->complete(
            interview: $interview,
            scores: $validated['scores'],
            feedback: $validated['feedback'] ?? null,
            communicationMetrics: [
                'filler_word_count' => $validated['filler_word_count'] ?? null,
                'speaking_pace_wpm' => $validated['speaking_pace_wpm'] ?? null,
                'confidence_score' => $validated['confidence_score'] ?? null,
            ]
        );

        return redirect()->route('interviews.show', $interview)
            ->with('success', 'Interview completed successfully!');
    }

    /**
     * Get available time slots for scheduling.
     */
    protected function getAvailableSlots(): array
    {
        $slots = [];
        $startDate = now()->addHour()->startOfHour();

        // Generate slots for next 14 days
        for ($day = 0; $day < 14; $day++) {
            $date = $startDate->copy()->addDays($day);
            $daySlots = [];

            // Slots from 9 AM to 6 PM
            for ($hour = 9; $hour < 18; $hour++) {
                $slotTime = $date->copy()->setHour($hour)->setMinute(0)->setSecond(0);
                
                // Skip if in the past
                if ($slotTime->isPast()) {
                    continue;
                }

                $daySlots[] = [
                    'time' => $slotTime->format('H:i'),
                    'datetime' => $slotTime->toISOString(),
                    'available' => true, // Could check against existing bookings
                ];
            }

            if (!empty($daySlots)) {
                $slots[] = [
                    'date' => $date->format('Y-m-d'),
                    'label' => $date->format('D, M j'),
                    'slots' => $daySlots,
                ];
            }
        }

        return $slots;
    }

    /**
     * Check if interview can be started.
     */
    protected function canStartInterview(InterviewSession $interview): bool
    {
        // Can only start scheduled interviews
        if ($interview->status !== \App\Enums\InterviewStatus::SCHEDULED) {
            return false;
        }

        // Can start up to 15 minutes before and 30 minutes after scheduled time
        $scheduledAt = $interview->scheduled_at;
        $now = now();

        return $now->between(
            $scheduledAt->copy()->subMinutes(15),
            $scheduledAt->copy()->addMinutes(30)
        );
    }

    /**
     * Check if interview can be cancelled.
     */
    protected function canCancelInterview(InterviewSession $interview): bool
    {
        // Can only cancel scheduled interviews
        if ($interview->status !== \App\Enums\InterviewStatus::SCHEDULED) {
            return false;
        }

        // Can cancel up to 1 hour before
        return now()->lt($interview->scheduled_at->copy()->subHour());
    }

    /**
     * Display the AI interview room (Enhanced version).
     * 
     * Features:
     * - Voice recognition (Web Speech API)
     * - Code editor for technical interviews
     * - Whiteboard for system design
     * - Video recording capability
     * - Interview preparation checklist
     */
    public function aiRoom(InterviewSession $interview): View
    {
        $this->authorize('view', $interview);

        if ($interview->mode !== \App\Enums\InterviewMode::AI) {
            abort(404);
        }

        // Use enhanced room with all new features
        $curriculumActivity = $interview->curriculum_activity_id
            ? \App\Models\TrackCurriculumActivity::find($interview->curriculum_activity_id)
            : null;

        return view('fellow.interviews.ai-room-enhanced', [
            'interview' => $interview,
            'curriculumActivity' => $curriculumActivity,
        ]);
    }

    /**
     * Display practice mode interview room.
     * Practice interviews don't affect Career Capital scores.
     */
    public function practiceRoom(Request $request): View
    {
        $user = $request->user();
        
        // Create a temporary practice interview session
        $activeTrack = $request->attributes->get('activeTrack');
        $track = $activeTrack?->track ?? $user->activeTrack()?->track ?? Track::first();
        $type = \App\Enums\InterviewType::tryFrom($request->get('type', 'behavioral')) 
            ?? \App\Enums\InterviewType::BEHAVIORAL;

        // Create a practice interview (not persisted or marked as practice)
        $practiceInterview = new InterviewSession([
            'id' => 'practice-' . uniqid(),
            'fellow_id' => $user->id,
            'track_id' => $track?->id,
            'type' => $type,
            'mode' => \App\Enums\InterviewMode::AI,
            'status' => \App\Enums\InterviewStatus::IN_PROGRESS,
            'difficulty_level' => $request->get('difficulty', 'intermediate'),
            'scheduled_at' => now(),
            'is_practice' => true,
        ]);

        // For practice, we save it but mark as practice
        $practiceInterview->save();

        return view('fellow.interviews.ai-room-enhanced', [
            'interview' => $practiceInterview,
        ]);
    }

    /**
     * Get interview questions (API endpoint for AI room).
     */
    public function getQuestions(InterviewSession $interview): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $interview);

        $questions = $this->interviewService->generateQuestions(
            $interview->type,
            $interview->difficulty ?? 'intermediate',
            $interview->track,
            $interview->fellow
        );

        return response()->json([
            'questions' => $questions,
            'time_limit' => $this->getTimeLimit($interview->type),
        ]);
    }

    /**
     * Evaluate a response during AI interview (API endpoint).
     * 
     * Supports multiple response modes:
     * - text: Standard text response
     * - voice: Voice-to-text transcription
     * - code: Code with language specification
     * - whiteboard: Drawing + text explanation
     */
    public function evaluateResponse(Request $request, InterviewSession $interview): \Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $interview);

        $validated = $request->validate([
            'question_index' => ['required', 'integer', 'min:0'],
            'question' => ['required', 'string'],
            'response' => ['required', 'string', 'min:5'],
            'response_mode' => ['nullable', 'string', 'in:text,voice,code,whiteboard'],
            'code_language' => ['nullable', 'string'],
            'whiteboard_image' => ['nullable', 'string'], // Base64 image
        ]);

        $responseMode = $validated['response_mode'] ?? 'text';
        
        // Enhance response with context based on mode
        $responseContext = $validated['response'];
        if ($responseMode === 'code' && !empty($validated['code_language'])) {
            $responseContext = "[Code in {$validated['code_language']}]\n" . $validated['response'];
        }

        $evaluation = $this->interviewService->evaluateResponse(
            $validated['question'],
            $responseContext,
            $interview->type,
            $interview->difficulty ?? 'intermediate'
        );

        // Store the response in the interview session
        $responses = $interview->responses ?? [];
        $responses[] = [
            'question_index' => $validated['question_index'],
            'question' => $validated['question'],
            'response' => $validated['response'],
            'response_mode' => $responseMode,
            'code_language' => $validated['code_language'] ?? null,
            'has_whiteboard' => !empty($validated['whiteboard_image']),
            'evaluation' => $evaluation,
            'timestamp' => now()->toISOString(),
        ];
        $interview->update(['responses' => $responses]);

        return response()->json($evaluation);
    }

    /**
     * Get time limit based on interview type.
     */
    protected function getTimeLimit(\App\Enums\InterviewType $type): int
    {
        return match($type) {
            \App\Enums\InterviewType::BEHAVIORAL => 180, // 3 minutes
            \App\Enums\InterviewType::TECHNICAL_CODING => 900, // 15 minutes
            \App\Enums\InterviewType::SYSTEM_DESIGN => 1800, // 30 minutes
            default => 300, // 5 minutes
        };
    }
}
