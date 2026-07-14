<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Services\AccountabilityService;
use App\Services\CurriculumService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admin Curriculum Controller
 * 
 * Manages track curricula from the admin side:
 * - CRUD milestones per track
 * - CRUD curriculum activities within milestones
 * - Reordering milestones and activities
 * - Viewing curriculum analytics and progress reports
 * - Managing accountability pairs
 * - Reviewing fellow submissions
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class CurriculumController extends Controller
{
    public function __construct(
        protected CurriculumService $curriculumService,
        protected AccountabilityService $accountabilityService
    ) {}

    // ==========================================
    // CURRICULUM OVERVIEW
    // ==========================================

    /**
     * Show curriculum management page for a track.
     * GET /admin/tracks/{track}/curriculum
     */
    public function index(Track $track)
    {
        $milestones = $this->curriculumService->getTrackCurriculum($track);
        $analytics = $this->curriculumService->getAdminCurriculumAnalytics($track);

        return view('admin.curriculum.index', compact('track', 'milestones', 'analytics'));
    }

    // ==========================================
    // MILESTONE MANAGEMENT
    // ==========================================

    /**
     * Show form to create a new milestone.
     * GET /admin/tracks/{track}/curriculum/milestones/create
     */
    public function createMilestone(Track $track)
    {
        $existingMilestones = TrackMilestone::where('track_id', $track->id)
            ->where('is_active', true)
            ->orderBy('sequence_order')
            ->get();

        return view('admin.curriculum.milestones.create', compact('track', 'existingMilestones'));
    }

    /**
     * Store a new milestone.
     * POST /admin/tracks/{track}/curriculum/milestones
     */
    public function storeMilestone(Request $request, Track $track)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'sequence_order' => 'nullable|integer|min:1',
            'is_required' => 'boolean',
            'unlock_after_milestone_id' => 'nullable|uuid|exists:track_milestones,id',
            'badge_name' => 'nullable|string|max:255',
            'badge_icon' => 'nullable|string|max:10',
            'badge_color' => 'nullable|string|max:20',
            'estimated_duration_days' => 'nullable|integer|min:1',
        ]);

        $milestone = $this->curriculumService->createMilestone($track, $validated, Auth::user());

        return redirect()
            ->route('admin.curriculum.index', $track)
            ->with('success', "Milestone \"{$milestone->title}\" created successfully.");
    }

    /**
     * Show form to edit a milestone.
     * GET /admin/tracks/{track}/curriculum/milestones/{milestone}/edit
     */
    public function editMilestone(Track $track, TrackMilestone $milestone)
    {
        $existingMilestones = TrackMilestone::where('track_id', $track->id)
            ->where('id', '!=', $milestone->id)
            ->where('is_active', true)
            ->orderBy('sequence_order')
            ->get();

        return view('admin.curriculum.milestones.edit', compact('track', 'milestone', 'existingMilestones'));
    }

    /**
     * Update a milestone.
     * PUT /admin/tracks/{track}/curriculum/milestones/{milestone}
     */
    public function updateMilestone(Request $request, Track $track, TrackMilestone $milestone)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'sequence_order' => 'nullable|integer|min:1',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'unlock_after_milestone_id' => 'nullable|uuid|exists:track_milestones,id',
            'badge_name' => 'nullable|string|max:255',
            'badge_icon' => 'nullable|string|max:10',
            'badge_color' => 'nullable|string|max:20',
            'estimated_duration_days' => 'nullable|integer|min:1',
        ]);

        $this->curriculumService->updateMilestone($milestone->id, $validated);

        return redirect()
            ->route('admin.curriculum.index', $track)
            ->with('success', "Milestone \"{$milestone->title}\" updated.");
    }

    /**
     * Delete a milestone.
     * DELETE /admin/tracks/{track}/curriculum/milestones/{milestone}
     */
    public function destroyMilestone(Track $track, TrackMilestone $milestone)
    {
        $this->curriculumService->deleteMilestone($milestone->id);

        return redirect()
            ->route('admin.curriculum.index', $track)
            ->with('success', "Milestone deleted.");
    }

    /**
     * Reorder milestones via AJAX.
     * POST /admin/tracks/{track}/curriculum/milestones/reorder
     */
    public function reorderMilestones(Request $request, Track $track)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'uuid|exists:track_milestones,id',
        ]);

        $this->curriculumService->reorderMilestones($track, $validated['order']);

        return response()->json(['success' => true]);
    }

    // ==========================================
    // CURRICULUM ACTIVITY MANAGEMENT
    // ==========================================

    /**
     * Show form to create a curriculum activity.
     * GET /admin/tracks/{track}/curriculum/milestones/{milestone}/activities/create
     */
    public function createActivity(Track $track, TrackMilestone $milestone)
    {
        $activityTypes = \App\Enums\ActivityType::cases();
        $difficultyLevels = \App\Enums\DifficultyLevel::cases();
        $evidenceTypes = \App\Enums\EvidenceType::cases();

        // Get existing activities in this milestone for chain parent dropdown
        $existingActivities = TrackCurriculumActivity::where('milestone_id', $milestone->id)
            ->where('is_active', true)
            ->orderBy('sequence_order')
            ->get();

        return view('admin.curriculum.activities.create', compact(
            'track', 'milestone', 'activityTypes', 'difficultyLevels', 'evidenceTypes', 'existingActivities'
        ));
    }

    /**
     * Store a new curriculum activity.
     * POST /admin/tracks/{track}/curriculum/milestones/{milestone}/activities
     */
    public function storeActivity(Request $request, Track $track, TrackMilestone $milestone)
    {
        // Convert dynamic resources array
        $structuredResources = [];
        if ($request->has('resources') && is_array($request->resources)) {
            foreach ($request->resources as $res) {
                if (empty($res['title'])) continue;
                
                $structured = [
                    'title' => $res['title'],
                    'type' => $res['type'] ?? 'link',
                ];
                
                if ($structured['type'] === 'file' && isset($res['content_file'])) {
                    $path = $res['content_file']->store('activity_resources', 'public');
                    $structured['content'] = \Illuminate\Support\Facades\Storage::url($path);
                } elseif ($structured['type'] === 'file' && !empty($res['existing_content'])) {
                    $structured['content'] = $res['existing_content'];
                } else {
                    $structured['content'] = $res['content_url'] ?? '';
                }
                
                $structuredResources[] = $structured;
            }
        }
        $request->merge(['resources' => $structuredResources]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'instructions' => 'nullable|string',
            'type' => 'required|string',
            'difficulty_level' => 'required|string',
            'points' => 'required|integer|min:1|max:1000',
            'sequence_order' => 'nullable|integer|min:1',
            'is_required' => 'boolean',
            'deadline_days' => 'nullable|integer|min:1|max:365',
            'grace_period_days' => 'nullable|integer|min:0|max:30',
            'late_penalty_percent' => 'nullable|integer|min:0|max:100',
            'evidence_requirements' => 'nullable|array',
            'evidence_requirements.*' => 'string',
            'evaluation_rubric' => 'nullable|array',
            'evaluation_rubric.*.criterion' => 'required_with:evaluation_rubric|string|max:255',
            'evaluation_rubric.*.description' => 'nullable|string|max:1000',
            'evaluation_rubric.*.weight' => 'required_with:evaluation_rubric|integer|min:1|max:100',
            'prerequisites' => 'nullable|array',
            'prerequisites.*' => 'uuid|exists:track_curriculum_activities,id',
            'chain_parent_id' => 'nullable|uuid|exists:track_curriculum_activities,id',
            'requires_peer_review' => 'boolean',
            'is_collaborative' => 'boolean',
            'requires_cross_track' => 'boolean',
            'resources' => 'nullable|array',
            'resources.*.title' => 'required_with:resources|string|max:255',
            'resources.*.type' => 'required_with:resources|string|in:link,file,youtube',
            'resources.*.content' => 'required_with:resources|string',
            'interview_config' => 'nullable|array',
            'interview_config.type' => 'required_if:type,mock_interview|string',
            'interview_config.mode' => ['nullable', \Illuminate\Validation\Rule::enum(\App\Enums\InterviewMode::class)],
            'interview_config.min_score' => 'nullable|integer|min:0|max:100',
            'interview_config.count' => 'nullable|integer|min:1|max:10',
            'interview_config.difficulty' => 'nullable|string|in:beginner,intermediate,advanced',
        ]);

        // Only include interview_config for mock_interview type
        if (($validated['type'] ?? '') !== 'mock_interview') {
            $validated['interview_config'] = null;
        }

        if (isset($validated['evaluation_rubric']) && is_array($validated['evaluation_rubric'])) {
            $validated['evaluation_rubric'] = array_values($validated['evaluation_rubric']);
        }
        if (isset($validated['prerequisites']) && is_array($validated['prerequisites'])) {
            $validated['prerequisites'] = array_values($validated['prerequisites']);
        }

        $validated['milestone_id'] = $milestone->id;
        $validated['track_id'] = $track->id;

        $activity = $this->curriculumService->createCurriculumActivity($validated, Auth::user());

        return redirect()
            ->route('admin.curriculum.index', $track)
            ->with('success', "Activity \"{$activity->title}\" created in {$milestone->title}.");
    }

    /**
     * Show form to edit a curriculum activity.
     * GET /admin/tracks/{track}/curriculum/activities/{activity}/edit
     */
    public function editActivity(Track $track, TrackCurriculumActivity $activity)
    {
        $milestone = $activity->milestone;
        $activityTypes = \App\Enums\ActivityType::cases();
        $difficultyLevels = \App\Enums\DifficultyLevel::cases();
        $evidenceTypes = \App\Enums\EvidenceType::cases();

        $existingActivities = TrackCurriculumActivity::where('milestone_id', $milestone->id)
            ->where('id', '!=', $activity->id)
            ->where('is_active', true)
            ->orderBy('sequence_order')
            ->get();

        return view('admin.curriculum.activities.edit', compact(
            'track', 'milestone', 'activity', 'activityTypes', 'difficultyLevels', 'evidenceTypes', 'existingActivities'
        ));
    }

    /**
     * Update a curriculum activity.
     * PUT /admin/tracks/{track}/curriculum/activities/{activity}
     */
    public function updateActivity(Request $request, Track $track, TrackCurriculumActivity $activity)
    {
        // Convert dynamic resources array
        $structuredResources = [];
        if ($request->has('resources') && is_array($request->resources)) {
            foreach ($request->resources as $res) {
                if (empty($res['title'])) continue;
                
                $structured = [
                    'title' => $res['title'],
                    'type' => $res['type'] ?? 'link',
                ];
                
                if ($structured['type'] === 'file' && isset($res['content_file'])) {
                    $path = $res['content_file']->store('activity_resources', 'public');
                    $structured['content'] = \Illuminate\Support\Facades\Storage::url($path);
                } elseif ($structured['type'] === 'file' && !empty($res['existing_content'])) {
                    $structured['content'] = $res['existing_content'];
                } else {
                    $structured['content'] = $res['content_url'] ?? '';
                }
                
                $structuredResources[] = $structured;
            }
        }
        $request->merge(['resources' => $structuredResources]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'instructions' => 'nullable|string',
            'type' => 'required|string',
            'difficulty_level' => 'required|string',
            'points' => 'required|integer|min:1|max:1000',
            'sequence_order' => 'nullable|integer|min:1',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'deadline_days' => 'nullable|integer|min:1|max:365',
            'grace_period_days' => 'nullable|integer|min:0|max:30',
            'late_penalty_percent' => 'nullable|integer|min:0|max:100',
            'evidence_requirements' => 'nullable|array',
            'evidence_requirements.*' => 'string',
            'evaluation_rubric' => 'nullable|array',
            'evaluation_rubric.*.criterion' => 'required_with:evaluation_rubric|string|max:255',
            'evaluation_rubric.*.description' => 'nullable|string|max:1000',
            'evaluation_rubric.*.weight' => 'required_with:evaluation_rubric|integer|min:1|max:100',
            'prerequisites' => 'nullable|array',
            'prerequisites.*' => 'uuid|exists:track_curriculum_activities,id',
            'chain_parent_id' => 'nullable|uuid|exists:track_curriculum_activities,id',
            'requires_peer_review' => 'boolean',
            'is_collaborative' => 'boolean',
            'requires_cross_track' => 'boolean',
            'resources' => 'nullable|array',
            'resources.*.title' => 'required_with:resources|string|max:255',
            'resources.*.type' => 'required_with:resources|string|in:link,file,youtube',
            'resources.*.content' => 'required_with:resources|string',
            'interview_config' => 'nullable|array',
            'interview_config.type' => 'required_if:type,mock_interview|string',
            'interview_config.mode' => ['nullable', \Illuminate\Validation\Rule::enum(\App\Enums\InterviewMode::class)],
            'interview_config.min_score' => 'nullable|integer|min:0|max:100',
            'interview_config.count' => 'nullable|integer|min:1|max:10',
            'interview_config.difficulty' => 'nullable|string|in:beginner,intermediate,advanced',
        ]);

        // Only include interview_config for mock_interview type
        if (($validated['type'] ?? '') !== 'mock_interview') {
            $validated['interview_config'] = null;
        }

        if (isset($validated['evaluation_rubric']) && is_array($validated['evaluation_rubric'])) {
            $validated['evaluation_rubric'] = array_values($validated['evaluation_rubric']);
        }
        if (isset($validated['prerequisites']) && is_array($validated['prerequisites'])) {
            $validated['prerequisites'] = array_values($validated['prerequisites']);
        }

        $this->curriculumService->updateCurriculumActivity($activity->id, $validated);

        return redirect()
            ->route('admin.curriculum.index', $track)
            ->with('success', "Activity \"{$activity->title}\" updated.");
    }

    /**
     * Delete a curriculum activity.
     * DELETE /admin/tracks/{track}/curriculum/activities/{activity}
     */
    public function destroyActivity(Track $track, TrackCurriculumActivity $activity)
    {
        $this->curriculumService->deleteCurriculumActivity($activity->id);

        return redirect()
            ->route('admin.curriculum.index', $track)
            ->with('success', "Activity deleted.");
    }

    /**
     * Reorder activities within a milestone via AJAX.
     * POST /admin/tracks/{track}/curriculum/milestones/{milestone}/activities/reorder
     */
    public function reorderActivities(Request $request, Track $track, TrackMilestone $milestone)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'uuid|exists:track_curriculum_activities,id',
        ]);

        $this->curriculumService->reorderActivities($milestone->id, $validated['order']);

        return response()->json(['success' => true]);
    }

    // ==========================================
    // REVIEW QUEUE
    // ==========================================

    /**
     * Show curriculum submissions pending review.
     * GET /admin/curriculum/reviews
     */
    public function reviewQueue(Request $request)
    {
        $trackId = $request->get('track_id');
        $track = $trackId ? Track::find($trackId) : null;
        $tracks = Track::active()->ordered()->get();

        $pendingReviews = $this->curriculumService->getPendingReviews(20, $track);

        return view('admin.curriculum.reviews.index', compact('pendingReviews', 'tracks', 'track'));
    }

    /**
     * Show a specific submission for review.
     * GET /admin/curriculum/reviews/{progress}
     */
    public function reviewShow(string $progressId)
    {
        $progress = \App\Models\FellowCurriculumProgress::with([
            'fellow', 'curriculumActivity.milestone', 'curriculumActivity.track',
            'reviewer', 'peerReviews',
        ])->findOrFail($progressId);

        return view('admin.curriculum.reviews.show', compact('progress'));
    }

    /**
     * Process a review decision (approve/reject).
     * POST /admin/curriculum/reviews/{progress}
     */
    public function reviewProcess(Request $request, string $progressId)
    {
        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'points' => 'required_if:decision,approve|nullable|integer|min:0|max:1000',
            'feedback' => 'nullable|string|max:2000',
            'rubric_scores' => 'nullable|array',
            'rubric_scores.*' => 'integer|min:0|max:100',
        ]);

        $progress = $this->curriculumService->reviewSubmission(
            Auth::user(),
            $progressId,
            $validated['decision'],
            $validated
        );

        $message = $validated['decision'] === 'approve'
            ? "Submission approved with {$progress->points_awarded} points."
            : "Submission rejected.";

        return redirect()
            ->route('admin.curriculum.reviews')
            ->with('success', $message);
    }

    // ==========================================
    // ACCOUNTABILITY PAIRS
    // ==========================================

    /**
     * Show accountability pairs for a track.
     * GET /admin/tracks/{track}/curriculum/pairs
     */
    public function pairs(Track $track)
    {
        $activePairs = $this->accountabilityService->getActivePairs($track);
        $pairStats = $this->accountabilityService->getPairStats($track);

        return view('admin.curriculum.pairs.index', compact('track', 'activePairs', 'pairStats'));
    }

    /**
     * Auto-pair unpaired fellows in a track.
     * POST /admin/tracks/{track}/curriculum/pairs/auto
     */
    public function autoPair(Request $request, Track $track)
    {
        $cohortId = $request->get('cohort_id');
        $pairedCount = $this->accountabilityService->autoPairTrack($track, $cohortId);

        return redirect()
            ->route('admin.curriculum.pairs', $track)
            ->with('success', "{$pairedCount} accountability pairs created.");
    }

    /**
     * Rotate all pairs for a track.
     * POST /admin/tracks/{track}/curriculum/pairs/rotate
     */
    public function rotatePairs(Track $track)
    {
        $pairedCount = $this->accountabilityService->rotatePairs($track);

        return redirect()
            ->route('admin.curriculum.pairs', $track)
            ->with('success', "Pairs rotated. {$pairedCount} new pairs created.");
    }

    // ==========================================
    // ANALYTICS
    // ==========================================

    /**
     * Show curriculum analytics for a track.
     * GET /admin/tracks/{track}/curriculum/analytics
     */
    public function analytics(Track $track)
    {
        $analytics = $this->curriculumService->getAdminCurriculumAnalytics($track);

        return view('admin.curriculum.analytics', compact('track', 'analytics'));
    }
}
