<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityRequest;
use App\Models\Activity;
use App\Models\Track;
use App\Services\ActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Activity Controller
 * 
 * Handles activity submission, viewing, and management for fellows.
 * Activities are the primary way to build Career Capital.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class ActivityController extends Controller
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    /**
     * Display listing of fellow's activities.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        $query = $user->activities()->with('track');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by track
        if ($request->filled('track_id')) {
            $query->where('track_id', $request->track_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->where('completed_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('completed_at', '<=', $request->to);
        }

        $activities = $query->orderByDesc('created_at')->paginate(15);

        // Get user's tracks for filter dropdown
        $tracks = Track::whereHas('fellowTracks', function ($q) use ($user) {
            $q->where('fellow_id', $user->id);
        })->get();

        // Get activity statistics
        $stats = [
            'total' => $user->activities()->count(),
            'approved' => $user->activities()->where('status', 'approved')->count(),
            'pending' => $user->activities()->where('status', 'pending')->count(),
            'rejected' => $user->activities()->where('status', 'rejected')->count(),
            'total_points' => $user->activities()->where('status', 'approved')->sum('points_earned'),
        ];

        return view('fellow.activities.index', [
            'activities' => $activities,
            'tracks' => $tracks,
            'stats' => $stats,
            'filters' => $request->only(['status', 'track_id', 'type', 'from', 'to']),
        ]);
    }

    /**
     * Show the activity submission form.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Get user's enrolled tracks
        $tracks = Track::whereHas('fellowTracks', function ($q) use ($user) {
            $q->where('fellow_id', $user->id);
        })->get();

        if ($tracks->isEmpty()) {
            return redirect()->route('tracks.select')
                ->with('warning', 'Please enroll in a track before submitting activities.');
        }

        // Get activity types from enum
        $activityTypes = \App\Enums\ActivityType::cases();

        return view('fellow.activities.create', [
            'tracks' => $tracks,
            'activityTypes' => $activityTypes,
            'preselectedTrack' => $request->query('track_id') ?? $request->attributes->get('activeTrack')?->track_id,
            'preselectedType' => $request->query('type'),
        ]);
    }

    /**
     * Store a newly submitted activity.
     */
    public function store(StoreActivityRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Handle file uploads if any
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('activities/' . $user->uuid, 'public');
                $attachments[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ];
            }
        }

        // Prepare metadata based on activity type
        $metadata = $this->buildMetadata($validated);

        // Get the track
        $track = \App\Models\Track::findOrFail($validated['track_id']);

        $activity = $this->activityService->create($user, $track, [
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'proof_url' => $validated['evidence_url'] ?? null,
            'proof_files' => $attachments,
            'metadata' => array_merge($metadata, [
                'completed_at' => $validated['completed_at'] ?? null,
                'hours_spent' => $validated['hours_spent'] ?? null,
                'tags' => $validated['tags'] ?? [],
            ]),
        ]);

        return redirect()->route('activities.show', $activity)
            ->with('success', 'Activity submitted successfully! It will be reviewed shortly.');
    }

    /**
     * Display a specific activity.
     */
    public function show(Activity $activity): View
    {
        // Authorize - only owner or admin can view
        $this->authorize('view', $activity);

        $activity->load(['track', 'fellow', 'reviewedBy']);

        return view('fellow.activities.show', [
            'activity' => $activity,
        ]);
    }

    /**
     * Show the form for editing an activity (only drafts/rejected).
     */
    public function edit(Activity $activity): View|RedirectResponse
    {
        $this->authorize('update', $activity);

        // Can only edit pending or revision_needed activities
        if (!in_array($activity->status->value, ['pending', 'revision_needed'])) {
            return redirect()->route('activities.show', $activity)
                ->with('error', 'This activity cannot be edited.');
        }

        $user = request()->user();
        $tracks = Track::whereHas('fellowTracks', function ($q) use ($user) {
            $q->where('fellow_id', $user->id);
        })->get();

        $activityTypes = \App\Enums\ActivityType::cases();

        return view('fellow.activities.edit', [
            'activity' => $activity,
            'tracks' => $tracks,
            'activityTypes' => $activityTypes,
        ]);
    }

    /**
     * Update an activity.
     */
    public function update(StoreActivityRequest $request, Activity $activity): RedirectResponse
    {
        $this->authorize('update', $activity);

        if (!in_array($activity->status->value, ['pending', 'revision_needed'])) {
            return redirect()->route('activities.show', $activity)
                ->with('error', 'This activity cannot be edited.');
        }

        $validated = $request->validated();
        
        // Handle new file uploads
        $attachments = $activity->metadata['attachments'] ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('activities/' . $request->user()->uuid, 'public');
                $attachments[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ];
            }
        }

        $metadata = $this->buildMetadata($validated);

        $activity->update([
            'track_id' => $validated['track_id'],
            'type' => \App\Enums\ActivityType::from($validated['type']),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'completed_at' => $validated['completed_at'],
            'evidence_url' => $validated['evidence_url'] ?? null,
            'hours_spent' => $validated['hours_spent'] ?? null,
            'status' => \App\Enums\ActivityStatus::PENDING, // Reset to pending after edit
            'metadata' => array_merge($metadata, [
                'attachments' => $attachments,
                'tags' => $validated['tags'] ?? [],
            ]),
        ]);

        return redirect()->route('activities.show', $activity)
            ->with('success', 'Activity updated and resubmitted for review.');
    }

    /**
     * Delete an activity (only drafts/pending).
     */
    public function destroy(Activity $activity): RedirectResponse
    {
        $this->authorize('delete', $activity);

        if (!in_array($activity->status->value, ['pending', 'revision_needed'])) {
            return redirect()->route('activities.show', $activity)
                ->with('error', 'Only pending activities can be deleted.');
        }

        $activity->delete();

        return redirect()->route('activities.index')
            ->with('success', 'Activity deleted successfully.');
    }

    /**
     * Mark an activity as complete.
     * 
     * This allows fellows to mark an activity as done and earn points.
     */
    public function complete(Request $request, Activity $activity): RedirectResponse
    {
        $this->authorize('update', $activity);

        $request->validate([
            'submission_url' => 'nullable|url|max:500',
            'reflection' => 'nullable|string|max:2000',
        ]);

        // Update activity to completed status
        $activity->update([
            'status' => \App\Enums\ActivityStatus::APPROVED,
            'completed_at' => now(),
            'metadata' => array_merge($activity->metadata ?? [], [
                'submission_url' => $request->submission_url,
                'reflection' => $request->reflection,
                'completed_by_fellow' => true,
            ]),
        ]);

        // Award points to the fellow
        $user = $request->user();
        $points = $activity->points ?? 25;
        
        // Update user's career capital score
        if (method_exists($user, 'addCareerCapitalPoints')) {
            $user->addCareerCapitalPoints($points, 'Activity completed: ' . $activity->title);
        }

        return redirect()->route('activities.show', $activity)
            ->with('success', "Activity completed! You earned +{$points} points.");
    }

    /**
     * Build metadata array based on activity type.
     */
    protected function buildMetadata(array $validated): array
    {
        $metadata = [];

        // Type-specific fields
        $typeFields = [
            'certification_name', 'certification_issuer', 'certification_url',
            'project_url', 'github_url',
            'event_name', 'audience_size',
            'publication_url', 'publication_type',
            'connection_count', 'event_type',
            'competition_name', 'placement',
        ];

        foreach ($typeFields as $field) {
            if (isset($validated[$field])) {
                $metadata[$field] = $validated[$field];
            }
        }

        return $metadata;
    }
}
