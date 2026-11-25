<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\FellowCurriculumProgress;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Services\AccountabilityService;
use App\Services\CurriculumService;
use App\Services\StreakService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Fellow Curriculum Controller
 * 
 * Fellow-facing curriculum features:
 * - View track curriculum with milestone progression
 * - Start and submit curriculum activities
 * - View personal progress, streaks, and badges
 * - Complete peer reviews for accountability partner
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class CurriculumController extends Controller
{
    public function __construct(
        protected CurriculumService $curriculumService,
        protected StreakService $streakService,
        protected AccountabilityService $accountabilityService
    ) {}

    // ==========================================
    // CURRICULUM DASHBOARD
    // ==========================================

    /**
     * Show the curriculum progress page for the fellow's primary track.
     * GET /curriculum
     */
    public function index(Request $request)
    {
        $fellow = Auth::user();
        
        // Use the global active track from session (set by header track switcher)
        $activeTrack = $request->attributes->get('activeTrack');
        $primaryTrack = $activeTrack?->track ?? $fellow->primaryTrack?->track;

        if (!$primaryTrack) {
            return redirect()->route('tracks.select')
                ->with('warning', 'Please select a track first to access your curriculum.');
        }

        return $this->showForTrack($primaryTrack);
    }

    /**
     * Show curriculum for a specific track.
     * GET /curriculum/{track}
     */
    public function track(Track $track)
    {
        $fellow = Auth::user();

        if (!$fellow->isEnrolledIn($track)) {
            return redirect()->route('curriculum.index')
                ->with('error', 'You are not enrolled in this track.');
        }

        return $this->showForTrack($track);
    }

    /**
     * Internal method to render the curriculum dashboard.
     */
    protected function showForTrack(Track $track)
    {
        $fellow = Auth::user();

        // Initialize curriculum if needed
        $this->curriculumService->initializeFellowCurriculum($fellow, $track);

        // Get full dashboard data
        $dashboard = $this->curriculumService->getFellowCurriculumDashboard($fellow, $track);

        // Get streak summary
        $streakSummary = $this->streakService->getStreakSummary($fellow, $track);

        // Get accountability partner
        $partner = $this->accountabilityService->getPartner($fellow, $track);

        // Peer reviews waiting for me
        $peerReviews = $this->curriculumService->getPeerReviewsForFellow($fellow, $track);

        // Leaderboard: Top 5 fellows in this track based on their track score
        $leaderboard = \App\Models\User::whereHas('fellowTracks', fn($q) => $q->where('track_id', $track->id))
            ->with(['fellowTracks' => fn($q) => $q->where('track_id', $track->id)])
            ->get()
            ->sortByDesc(fn($u) => $u->fellowTracks->first()->score ?? 0)
            ->take(5);

        // Determine Active Milestone
        $activeMilestone = null;
        if (isset($dashboard['milestones'])) {
            foreach ($dashboard['milestones'] as $milestone) {
                $msActivities = $milestone->curriculumActivities ?? collect();
                if ($msActivities->isEmpty()) continue;

                $msProgress = $dashboard['progress']->whereIn('curriculum_activity_id', $msActivities->pluck('id'));
                $msCompleted = $msProgress->where('status', 'completed')->count();
                $msTotal = $msActivities->count();

                $isUnlocked = $milestone->isUnlockedFor($fellow);
                $isComplete = $msTotal > 0 && $msCompleted === $msTotal;

                if ($isUnlocked && !$isComplete) {
                    $activeMilestone = $milestone;
                    break;
                }
            }
            
            // If all are completed, the active is the last one
            if (!$activeMilestone && $dashboard['milestones']->isNotEmpty()) {
                $activeMilestone = $dashboard['milestones']->last();
            }
        }

        $orientation = app(\App\Services\OrientationService::class);
        $orientationRequired = $orientation->isRequired($fellow, $track);
        $page = app(\App\Services\CurriculumPageService::class);
        $milestones = $dashboard['milestones'] ?? collect();
        $progressRows = $dashboard['progress'] ?? collect();

        // "You are here": the first chapter whose required work isn't finished (same rule as the journey bar).
        $completedIds = $progressRows->filter(fn ($p) => ($p->status?->value ?? $p->status) === 'completed')->pluck('curriculum_activity_id')->flip();
        $currentIndex = $page->currentChapterIndex($milestones, $completedIds);

        return view('fellow.curriculum.index', array_merge($dashboard, [
            'currentMilestoneId' => $currentIndex !== null ? $milestones->values()[$currentIndex]->id : null,
            'orientationRequired' => $orientationRequired,
            'orientationDone' => $orientation->isComplete($fellow, $track),
            'nextStep' => $page->nextStep($fellow, $track, $milestones, $progressRows, $orientationRequired),
            'classmates' => $page->classmates($fellow, $track, $milestones),
            'extras' => $page->extras($fellow, $track),
            'streakSummary' => $streakSummary,
            'partner' => $partner,
            'peerReviews' => $peerReviews,
            'leaderboard' => $leaderboard,
            'activeMilestone' => $activeMilestone,
            'currentTrack' => $track,
        ]));
    }

    // ==========================================
    // ACTIVITY INTERACTION
    // ==========================================

    /**
     * Show a specific curriculum activity.
     * GET /curriculum/activities/{activity}
     */
    public function showActivity(TrackCurriculumActivity $activity)
    {
        $fellow = Auth::user();

        if (!$fellow->isEnrolledIn($activity->track_id)) {
            return redirect()->route('curriculum.index')
                ->with('error', 'You are not enrolled in this track.');
        }

        $progress = FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->where('curriculum_activity_id', $activity->id)
            ->first();

        $activity->load(['milestone', 'track', 'chainParent', 'chainChildren', 'comments.user', 'comments.replies.user']);

        // For mock_interview activities, load linked interview sessions
        $linkedInterviews = collect();
        if ($activity->requiresInterviewSession()) {
            $linkedInterviews = $this->curriculumService->getLinkedInterviews($fellow, $activity);
        }

        // Collaborative activities: find (or show) the fellow's review partner.
        $reviewPartner = null;
        if ($activity->is_collaborative && $progress && in_array($progress->status, [\App\Enums\CurriculumStatus::AVAILABLE, \App\Enums\CurriculumStatus::IN_PROGRESS, \App\Enums\CurriculumStatus::SUBMITTED, \App\Enums\CurriculumStatus::UNDER_REVIEW, \App\Enums\CurriculumStatus::COMPLETED, \App\Enums\CurriculumStatus::REJECTED], true)) {
            $reviewPartner = app(\App\Services\ReviewPartnerService::class)->matchFellow($fellow, $activity);
        }

        // Interactive lesson: steps to tick, quick checks, the starter notebook and a code sandbox.
        $isOpen = $progress && $progress->status !== \App\Enums\CurriculumStatus::LOCKED;
        $lessonSections = \App\Support\LessonSteps::parse($activity->instructions);
        $quickChecks = $isOpen ? app(\App\Services\FlashReviewService::class)->quickChecks($fellow, $activity) : collect();
        $orientationRequired = app(\App\Services\OrientationService::class)->isRequired($fellow, $activity->track_id);
        $colabUrl = null;
        $notebookPath = null;
        foreach ((array) $activity->resources as $resource) {
            $url = is_array($resource) ? (string) ($resource['content'] ?? '') : (string) $resource;
            if (!$colabUrl && str_starts_with($url, 'https://colab.research.google.com/')) {
                $colabUrl = $url;
            }
            $path = (string) parse_url($url, PHP_URL_PATH);
            if (!$notebookPath && str_starts_with($path, '/course-files/') && str_ends_with($path, '.ipynb') && !str_contains($url, 'colab.')) {
                $notebookPath = $path;
            }
        }
        $practice = $activity->track?->experience['practice'] ?? null;
        $codeSkills = ['python', 'pandas', 'sql', 'statistics', 'cleaning', 'visualization'];
        $sandbox = $isOpen && is_array($practice) && ($notebookPath || array_intersect((array) $activity->skills, $codeSkills))
            ? ['notebook' => $notebookPath, 'sqlTables' => $practice['sql_tables'] ?? [], 'pythonData' => array_values($practice['python_files'] ?? [])[0] ?? null,
                'startWith' => in_array('sql', (array) $activity->skills, true) && !in_array('python', (array) $activity->skills, true) ? 'sql' : 'python']
            : null;

        return view('fellow.curriculum.activity-show', compact('activity', 'progress', 'linkedInterviews', 'reviewPartner', 'lessonSections', 'quickChecks', 'orientationRequired', 'colabUrl', 'sandbox', 'isOpen'));
    }

    /**
     * Start working on a curriculum activity.
     * For mock_interview activities, auto-creates an interview and redirects to interview room.
     * POST /curriculum/activities/{activity}/start
     */
    public function startActivity(TrackCurriculumActivity $activity)
    {
        $fellow = Auth::user();

        // New fellows complete the track orientation before their first activity.
        if (app(\App\Services\OrientationService::class)->isRequired($fellow, $activity->track_id)) {
            return redirect()->route('curriculum.orientation')
                ->with('warning', 'Welcome! Complete your orientation first: it takes about 20 minutes and opens Chapter 1.');
        }

        try {
            $result = $this->curriculumService->startActivity($fellow, $activity->id);

            // If this is an interview activity, redirect to the interview room
            if (is_array($result) && isset($result['interview'])) {
                $interview = $result['interview'];

                if ($interview->mode === \App\Enums\InterviewMode::AI) {
                    // AI interviews are already IN_PROGRESS from createLinkedInterview
                    return redirect()
                        ->route('interviews.ai-room', $interview)
                        ->with('success', 'Interview session started! Good luck.');
                }

                // For human/peer interviews, redirect to the interview show page
                return redirect()
                    ->route('interviews.show', $interview)
                    ->with('success', 'Interview session created! Schedule it when ready.');
            }

            return redirect()
                ->route('curriculum.activity.show', $activity)
                ->with('success', 'Activity started! Good luck.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Launch another interview attempt for a curriculum activity.
     * POST /curriculum/activities/{activity}/interview
     */
    public function launchInterview(TrackCurriculumActivity $activity)
    {
        $fellow = Auth::user();

        try {
            $interview = $this->curriculumService->launchInterviewAttempt($fellow, $activity->id);

            if ($interview->mode === \App\Enums\InterviewMode::AI) {
                // AI interviews are already IN_PROGRESS from createLinkedInterview
                return redirect()
                    ->route('interviews.ai-room', $interview)
                    ->with('success', 'New interview session started!');
            }

            return redirect()
                ->route('interviews.show', $interview)
                ->with('success', 'New interview session created!');
        } catch (\Exception $e) {
            return redirect()
                ->route('curriculum.activity.show', $activity)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show submission form for a curriculum activity.
     * GET /curriculum/progress/{progress}/submit
     */
    public function submitForm(FellowCurriculumProgress $progress)
    {
        $fellow = Auth::user();

        if ($progress->fellow_id !== $fellow->id) {
            abort(403);
        }

        $progress->load(['curriculumActivity.milestone', 'curriculumActivity.track']);
        $evidenceTypes = $progress->curriculumActivity->evidence_requirements ?? [];

        return view('fellow.curriculum.submit', compact('progress', 'evidenceTypes'));
    }

    /**
     * Submit a curriculum activity.
     * POST /curriculum/progress/{progress}/submit
     */
    public function submit(Request $request, FellowCurriculumProgress $progress)
    {
        $fellow = Auth::user();

        if ($progress->fellow_id !== $fellow->id) {
            abort(403);
        }

        $validated = $request->validate([
            'evidence_url' => 'nullable|url|max:500',
            'evidence_text' => 'nullable|string|max:5000',
            'evidence_files' => 'nullable|array|max:5',
            'evidence_files.*' => 'file|max:10240',
            'reflection' => 'nullable|string|max:2000',
            'ai_used' => 'required|in:yes,no',
            'ai_used_how' => 'required_if:ai_used,yes|nullable|string|max:1000',
        ], [
            'ai_used.required' => 'Tell us whether you used AI tools for this submission.',
            'ai_used_how.required_if' => 'Briefly describe how you used AI tools.',
        ]);

        // Handle file uploads
        $uploadedFiles = [];
        if ($request->hasFile('evidence_files')) {
            foreach ($request->file('evidence_files') as $file) {
                $path = $file->store('curriculum-evidence/' . $fellow->id);
                $uploadedFiles[] = $path;
            }
        }

        $data = [
            'evidence_url' => $validated['evidence_url'] ?? null,
            'evidence_text' => $validated['evidence_text'] ?? null,
            'evidence_files' => !empty($uploadedFiles) ? $uploadedFiles : null,
            'reflection' => $validated['reflection'] ?? null,
            'ai_assistance' => [
                'used' => $validated['ai_used'] === 'yes',
                'how' => $validated['ai_used'] === 'yes' ? trim((string) ($validated['ai_used_how'] ?? '')) : null,
            ],
        ];

        try {
            $this->curriculumService->submitActivity($fellow, $progress->id, $data);

            return redirect()
                ->route('curriculum.activity.show', $progress->curriculumActivity)
                ->with('success', 'Activity submitted for review!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    // ==========================================
    // PEER REVIEW
    // ==========================================

    /**
     * Show peer review form.
     * GET /curriculum/peer-review/{progress}
     */
    public function peerReviewForm(FellowCurriculumProgress $progress)
    {
        $fellow = Auth::user();

        // Verify this user is assigned to review this submission
        $isReviewer = $progress->peerReviews()->where('reviewer_id', $fellow->id)->exists();

        if (!$isReviewer) {
            return redirect()->route('curriculum.index')
                ->with('error', 'You are not assigned to review this submission.');
        }

        $progress->load(['fellow', 'curriculumActivity']);

        return view('fellow.curriculum.peer-review', compact('progress'));
    }

    /**
     * Submit peer review.
     * POST /curriculum/peer-review/{progress}
     */
    public function peerReviewSubmit(Request $request, FellowCurriculumProgress $progress)
    {
        $fellow = Auth::user();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:2000',
        ]);

        try {
            $this->curriculumService->completePeerReview(
                $fellow,
                $progress->id,
                $validated['rating'],
                $validated['feedback'] ?? null
            );

            return redirect()
                ->route('curriculum.index')
                ->with('success', 'Peer review submitted. Thank you!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Bypass peer review manually.
     * POST /curriculum/progress/{progress}/bypass-peer-review
     */
    public function bypassPeerReview(Request $request, FellowCurriculumProgress $progress)
    {
        $fellow = Auth::user();

        if ($progress->fellow_id !== $fellow->id) {
            abort(403);
        }

        if ($progress->status !== \App\Enums\CurriculumStatus::PEER_REVIEW) {
            return redirect()->back()->with('error', 'This submission is not awaiting peer review.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $progress->bypassPeerReview($validated['reason']);

        return redirect()
            ->route('curriculum.activity.show', $progress->curriculumActivity)
            ->with('success', 'Peer review bypassed. Your submission is now awaiting admin review.');
    }

    // ==========================================
    // BADGES
    // ==========================================

    /**
     * Show all earned badges.
     * GET /curriculum/badges
     */
    public function badges()
    {
        $fellow = Auth::user();
        $badges = $fellow->badges()->orderBy('earned_at', 'desc')->get();

        return view('fellow.curriculum.badges', compact('badges'));
    }

    /**
     * Mark a badge as shared.
     * POST /curriculum/badges/{badge}/share
     */
    public function shareBadge(\App\Models\FellowBadge $badge)
    {
        $fellow = Auth::user();

        if ($badge->fellow_id !== $fellow->id) {
            abort(403);
        }

        $badge->markShared();

        return response()->json([
            'success' => true,
            'shareable_url' => $badge->shareable_url,
        ]);
    }
}
