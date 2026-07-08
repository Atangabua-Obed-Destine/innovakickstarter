<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAdminSettingsRequest;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Cohort;
use App\Models\CohortFellow;
use App\Models\InterviewSession;
use App\Models\Program;
use App\Models\ProgramFellow;
use App\Models\Track;
use App\Models\User;
use App\Services\ActivityService;
use App\Services\AdminSettingsService;
use App\Services\AuditService;
use App\Services\WeeklyProgressService;
use App\Enums\InterviewType;
use App\Enums\InterviewMode;
use App\Enums\InterviewStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

/**
 * Admin Controller
 * 
 * Handles all administrative functions including:
 * - Dashboard overview
 * - Activity approval queue
 * - User management
 * - Track configuration
 * - System settings
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class AdminController extends Controller
{
    public function __construct(
        protected ActivityService $activityService,
        protected AdminSettingsService $settingsService,
        protected WeeklyProgressService $progressService,
        protected AuditService $auditService
    ) {}

    /**
     * Display the admin dashboard.
     */
    public function dashboard(): View
    {
        // Key metrics
        $activeSession = \App\Models\AttendanceSession::where('status', 'active')->first();
        $metrics = [
            'total_fellows' => User::role('fellow')->where('is_active', true)->count(),
            'total_recruiters' => User::role('recruiter')->count(),
            'total_mentors' => User::role('mentor')->count(),
            'pending_activities' => Activity::where('status', 'pending')->count(),
            'activities_today' => Activity::whereDate('created_at', today())->count(),
            'interviews_today' => InterviewSession::whereDate('scheduled_at', today())->count(),
            'active_tracks' => Track::where('is_active', true)->count(),
            'active_cohorts' => Cohort::where('status', 'active')->count(),
            'total_cohorts' => Cohort::count(),
            'cohort_enrollments' => \DB::table('cohort_fellows')
                ->whereIn('status', ['enrolled', 'active'])
                ->count(),
            // Program metrics
            'active_programs' => Program::whereIn('status', [Program::STATUS_ACTIVE, Program::STATUS_ENROLLING])->count(),
            'total_programs' => Program::count(),
            'program_enrollments' => \DB::table('program_fellows')
                ->whereIn('status', [ProgramFellow::STATUS_ENROLLED, ProgramFellow::STATUS_ACTIVE])
                ->count(),
            // Attendance metrics
            'active_attendance_session' => $activeSession,
            'attendance_today' => \App\Models\AttendanceRecord::whereDate('clock_in_time', today())->count(),
        ];

        // Recent activities awaiting approval
        $pendingActivities = Activity::where('status', 'pending')
            ->with(['fellow', 'track'])
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        // Recent registrations
        $recentUsers = User::with('roles')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Weekly progress stats
        $weeklyStats = $this->progressService->getStatistics();

        // Recent audit logs
        $recentLogs = $this->auditService->getRecent(10);

        // Get tracks with stats for dashboard table
        $tracks = Track::where('is_active', true)
            ->withCount('fellows')
            ->get();
        
        // Get active cohorts summary
        $activeCohorts = Cohort::where('status', 'active')
            ->with('track')
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        // Get active programs summary
        $activePrograms = Program::whereIn('status', [Program::STATUS_ACTIVE, Program::STATUS_ENROLLING])
            ->withCount(['fellows as active_fellows_count' => function ($query) {
                $query->whereIn('program_fellows.status', [ProgramFellow::STATUS_ENROLLED, ProgramFellow::STATUS_ACTIVE]);
            }])
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'pendingActivities' => $pendingActivities,
            'recentUsers' => $recentUsers,
            'weeklyStats' => $weeklyStats,
            'recentLogs' => $recentLogs,
            'tracks' => $tracks,
            'activeCohorts' => $activeCohorts,
            'activePrograms' => $activePrograms,
            'enrollmentTrends' => $this->getEnrollmentTrends(),
            'scoreDistribution' => $this->getScoreDistribution(),
            'systemAlerts' => $this->getSystemAlerts($weeklyStats, $activeCohorts),
            'growthRates' => $this->getGrowthRates(),
        ]);
    }

    /**
     * Get monthly enrollment (fellow registration) trends for the last 12 months.
     */
    protected function getEnrollmentTrends(): array
    {
        $trends = User::role('fellow')
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Fill in missing months with 0
        $result = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $result[] = [
                'label' => $date->format('M'),
                'short' => substr($date->format('M'), 0, 1),
                'count' => $trends[$key] ?? 0,
            ];
        }
        return $result;
    }

    /**
     * Get career capital score distribution across all fellow tracks.
     */
    protected function getScoreDistribution(): array
    {
        $buckets = [
            ['range' => '90-100', 'min' => 90, 'max' => 100, 'color' => 'bg-green-500'],
            ['range' => '80-89', 'min' => 80, 'max' => 89.99, 'color' => 'bg-teal-500'],
            ['range' => '70-79', 'min' => 70, 'max' => 79.99, 'color' => 'bg-blue-500'],
            ['range' => '60-69', 'min' => 60, 'max' => 69.99, 'color' => 'bg-primary-500'],
            ['range' => '50-59', 'min' => 50, 'max' => 59.99, 'color' => 'bg-amber-500'],
            ['range' => 'Below 50', 'min' => 0, 'max' => 49.99, 'color' => 'bg-red-500'],
        ];

        $total = \App\Models\FellowTrack::count();
        $distribution = [];

        foreach ($buckets as $bucket) {
            $count = \App\Models\FellowTrack::whereBetween('score', [$bucket['min'], $bucket['max']])->count();
            $distribution[] = [
                'range' => $bucket['range'],
                'count' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100) : 0,
                'color' => $bucket['color'],
            ];
        }

        return $distribution;
    }

    /**
     * Build dynamic system alerts.
     */
    protected function getSystemAlerts(array $weeklyStats, $activeCohorts): array
    {
        $alerts = [];

        // Inactive fellows alert
        $inactiveFellows = $weeklyStats['this_week']['not_started'] ?? 0;
        if ($inactiveFellows > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => "{$inactiveFellows} fellow" . ($inactiveFellows === 1 ? '' : 's') . " haven't completed any activities this week",
                'subtitle' => 'Consider sending reminder emails to inactive fellows.',
                'color' => 'amber',
            ];
        }

        // Pending activities alert
        $pendingCount = Activity::where('status', 'pending')->count();
        if ($pendingCount > 10) {
            $alerts[] = [
                'type' => 'warning',
                'title' => "{$pendingCount} activities are waiting for review",
                'subtitle' => 'The review queue is growing. Please review pending activities.',
                'color' => 'orange',
            ];
        }

        // Cohort enrollment deadline alerts
        foreach ($activeCohorts as $cohort) {
            if ($cohort->enrollment_end_date && $cohort->enrollment_end_date->isFuture() && $cohort->enrollment_end_date->diffInDays(now()) <= 7) {
                $daysLeft = now()->diffInDays($cohort->enrollment_end_date);
                $enrolled = \DB::table('cohort_fellows')
                    ->where('cohort_id', $cohort->id)
                    ->whereIn('status', ['enrolled', 'active'])
                    ->count();
                $alerts[] = [
                    'type' => 'info',
                    'title' => "{$cohort->name} enrollment closes in {$daysLeft} day" . ($daysLeft === 1 ? '' : 's'),
                    'subtitle' => "Current enrollment: {$enrolled}" . ($cohort->max_fellows ? "/{$cohort->max_fellows}" : '') . " fellows",
                    'color' => 'blue',
                ];
            }
        }

        return $alerts;
    }

    /**
     * Calculate growth rates for dashboard stat cards.
     */
    protected function getGrowthRates(): array
    {
        $thisMonth = User::role('fellow')->where('created_at', '>=', now()->startOfMonth())->count();
        $lastMonth = User::role('fellow')
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count();
        $fellowGrowth = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100) : ($thisMonth > 0 ? 100 : 0);

        $interviewsToday = InterviewSession::whereDate('scheduled_at', today())->count();
        $interviewsYesterday = InterviewSession::whereDate('scheduled_at', today()->subDay())->count();
        $interviewGrowth = $interviewsYesterday > 0
            ? round((($interviewsToday - $interviewsYesterday) / $interviewsYesterday) * 100)
            : ($interviewsToday > 0 ? 100 : 0);

        return [
            'fellows' => $fellowGrowth,
            'interviews' => $interviewGrowth,
        ];
    }

    /**
     * Display activity approval queue.
     */
    public function activityQueue(Request $request): View
    {
        $query = Activity::where('status', 'pending')
            ->with(['fellow', 'track']);

        // Filter by track
        if ($request->filled('track_id')) {
            $query->where('track_id', $request->track_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Sort by oldest first (FIFO)
        $activities = $query->orderBy('created_at')->paginate(20);

        $tracks = Track::where('is_active', true)->get();

        return view('admin.activities.queue', [
            'activities' => $activities,
            'tracks' => $tracks,
            'filters' => $request->only(['track_id', 'type']),
        ]);
    }

    /**
     * Review a specific activity.
     */
    public function reviewActivity(Activity $activity): View
    {
        $activity->load(['fellow.primaryTrack.track', 'track']);

        // Get fellow's history for context
        $fellowHistory = Activity::with(['track'])
            ->where('fellow_id', $activity->fellow_id)
            ->where('id', '!=', $activity->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.activities.review', [
            'activity' => $activity,
            'fellowHistory' => $fellowHistory,
        ]);
    }

    /**
     * Update activity review (unified approve/reject from review page).
     */
    public function updateActivityReview(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected,revision'],
            'points' => ['nullable', 'integer', 'min:0', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'internal_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $decision = $validated['decision'];
        $feedback = $validated['notes'] ?? null;

        if ($decision === 'approved') {
            $this->activityService->approve(
                $activity,
                $request->user(),
                $feedback,
                $validated['points'] ?? null
            );
            $message = 'Activity approved successfully!';
        } elseif ($decision === 'rejected') {
            $this->activityService->reject(
                $activity,
                $request->user(),
                $feedback ?? 'Rejected by admin.'
            );
            $message = 'Activity rejected with feedback.';
        } else {
            $this->activityService->requestRevision(
                $activity,
                $request->user(),
                $feedback ?? 'Revision needed.'
            );
            $message = 'Revision request sent to fellow.';
        }

        // Save internal notes if provided
        if (!empty($validated['internal_notes'])) {
            $activity->update(['admin_notes' => $validated['internal_notes']]);
        }

        return redirect()->route('admin.activities.queue')
            ->with('success', $message);
    }

    /**
     * Approve an activity.
     */
    public function approveActivity(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'feedback' => ['nullable', 'string', 'max:2000'],
            'points_override' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $this->activityService->approve(
            $activity,
            $request->user(),
            $validated['feedback'] ?? null,
            $validated['points_override'] ?? null
        );

        return redirect()->route('admin.activities.queue')
            ->with('success', 'Activity approved successfully!');
    }

    /**
     * Reject an activity.
     */
    public function rejectActivity(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'feedback' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        $this->activityService->reject(
            $activity,
            $request->user(),
            $validated['feedback']
        );

        return redirect()->route('admin.activities.queue')
            ->with('success', 'Activity rejected with feedback.');
    }

    /**
     * Request revision on activity.
     */
    public function needsRevision(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'feedback' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        $this->activityService->requestRevision(
            $activity,
            $request->user(),
            $validated['feedback']
        );

        return redirect()->route('admin.activities.queue')
            ->with('success', 'Revision request sent to fellow.');
    }

    /**
     * Display fellow management.
     */
    public function fellows(Request $request): View
    {
        $query = User::role('fellow')->with(['primaryTrack.track', 'internshipProfile']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by tier
        if ($request->filled('tier')) {
            $query->whereHas('primaryTrack', fn($q) => 
                $q->where('tier', $request->tier)
            );
        }

        // Filter by track
        if ($request->filled('track_id')) {
            $query->whereHas('fellowTracks', fn($q) => 
                $q->where('track_id', $request->track_id)
            );
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $fellows = $query->orderByDesc('created_at')->paginate(20);
        $tracks = Track::where('is_active', true)->get();

        return view('admin.fellows.index', [
            'fellows' => $fellows,
            'tracks' => $tracks,
            'filters' => $request->only(['search', 'tier', 'track_id', 'status']),
        ]);
    }

    /**
     * View fellow details.
     */
    public function showFellow(User $user): View
    {
        if (!$user->hasRole('fellow')) {
            abort(404);
        }

        $user->load([
            'fellowTracks.track',
            'activities' => fn($q) => $q->with('track')->latest()->limit(10),
            'interviewSessions' => fn($q) => $q->latest()->limit(5),
            'weeklyProgress' => fn($q) => $q->latest()->limit(8),
            'internshipProfile.reviewer',
        ]);

        $auditLogs = $this->auditService->getForUser($user, 20);

        return view('admin.fellows.show', [
            'fellow' => $user,
            'auditLogs' => $auditLogs,
        ]);
    }

    /**
     * Toggle fellow active status.
     */
    public function toggleFellowStatus(User $user): RedirectResponse
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Fellow {$status} successfully.");
    }

    /**
     * Display track management.
     */
    public function tracks(): View
    {
        $tracks = Track::withCount('fellowTracks')
            ->orderBy('name')
            ->get();

        return view('admin.tracks.index', [
            'tracks' => $tracks,
        ]);
    }

    /**
     * Show track edit form.
     */
    public function editTrack(Track $track): View
    {
        return view('admin.tracks.edit', [
            'track' => $track,
        ]);
    }

    /**
     * Update track settings.
     */
    public function updateTrack(Request $request, Track $track): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['required', 'string'],
            'is_active' => ['boolean'],
            'max_fellows' => ['nullable', 'integer', 'min:0'],
            'category_weights' => ['nullable', 'array'],
        ]);

        $track->update($validated);

        return redirect()->route('admin.tracks.index')
            ->with('success', 'Track updated successfully.');
    }

    /**
     * Display system settings.
     */
    public function settings(): View
    {
        $settings = $this->settingsService->getAllGrouped();

        return view('admin.settings.index', [
            'settings' => $settings,
            'tierThresholds' => $this->settingsService->getTierThresholds(),
            'categoryWeights' => $this->settingsService->getCategoryWeights(),
            'interviewLimits' => $this->settingsService->getInterviewLimits(),
            'subscriptionPricing' => $this->settingsService->getSubscriptionPricing(),
            'platformSettings' => $this->settingsService->getPlatformSettings(),
        ]);
    }

    /**
     * Update settings group.
     */
    public function updateSettings(UpdateAdminSettingsRequest $request, string $group): RedirectResponse
    {
        $validated = $request->validated();

        foreach ($validated as $key => $value) {
            $this->settingsService->set($key, $value, $request->user());
        }

        return redirect()->route('admin.settings')
            ->with('success', ucfirst($group) . ' settings updated successfully.');
    }

    /**
     * Display audit logs.
     */
    public function auditLogs(Request $request): View
    {
        $logs = $this->auditService->search([
            'user_id' => $request->user_id,
            'action' => $request->action,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'search' => $request->search,
            'per_page' => 50,
        ]);

        return view('admin.audit-logs', [
            'logs' => $logs,
            'filters' => $request->only(['user_id', 'action', 'date_from', 'date_to', 'search']),
        ]);
    }

    /**
     * Initialize default settings.
     */
    public function initializeSettings(): RedirectResponse
    {
        $this->settingsService->initializeDefaults();

        return redirect()->route('admin.settings')
            ->with('success', 'Default settings initialized.');
    }

    /*
    |--------------------------------------------------------------------------
    | Track Management
    |--------------------------------------------------------------------------
    */

    /**
     * Show track creation form.
     */
    public function createTrack(): View
    {
        $categories = \App\Enums\TrackCategory::cases();

        return view('admin.tracks.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a new track.
     */
    public function storeTrack(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tracks,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tracks,slug'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['required', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:7'],
            'is_active' => ['boolean'],
            'max_fellows' => ['nullable', 'integer', 'min:0'],
            'duration_weeks' => ['nullable', 'integer', 'min:1', 'max:52'],
        ]);

        $validated['slug'] = $validated['slug'] ?? \Illuminate\Support\Str::slug($validated['name']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        $track = Track::create($validated);

        $this->auditService->log('track_created', auth()->user(), $track, 'Track created: ' . $validated['name']);

        return redirect()->route('admin.tracks.index')
            ->with('success', 'Track created successfully.');
    }

    /**
     * Delete a track.
     */
    public function destroyTrack(Track $track): RedirectResponse
    {
        // Check if track has fellows enrolled
        if ($track->fellowTracks()->exists()) {
            return redirect()->route('admin.tracks.index')
                ->with('error', 'Cannot delete track with enrolled fellows. Deactivate it instead.');
        }

        $trackName = $track->name;
        $track->delete();

        $this->auditService->log('track_deleted', auth()->user(), null, 'Track deleted: ' . $trackName);

        return redirect()->route('admin.tracks.index')
            ->with('success', 'Track deleted successfully.');
    }

    /**
     * Toggle track active status.
     */
    public function toggleTrack(Track $track): RedirectResponse
    {
        $track->update(['is_active' => !$track->is_active]);

        $status = $track->is_active ? 'activated' : 'deactivated';
        $this->auditService->log('track_' . $status, auth()->user(), $track, "Track {$status}: {$track->name}");

        return redirect()->route('admin.tracks.index')
            ->with('success', "Track {$status} successfully.");
    }

    /*
    |--------------------------------------------------------------------------
    | Recruiter Management
    |--------------------------------------------------------------------------
    */

    /**
     * Display recruiter listing.
     */
    public function recruiters(Request $request): View
    {
        $query = User::role('recruiter')
            ->with('subscription');

        // Filters
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('subscription')) {
            $query->whereHas('subscription', function ($q) use ($request) {
                $q->where('tier', $request->subscription);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $recruiters = $query->orderByDesc('created_at')->paginate(20);

        // Stats
        $stats = [
            'total' => User::role('recruiter')->count(),
            'active' => User::role('recruiter')->where('is_active', true)->count(),
            'pending_approval' => User::role('recruiter')->where('is_active', false)->whereNull('suspended_at')->count(),
            'suspended' => User::role('recruiter')->whereNotNull('suspended_at')->count(),
        ];

        return view('admin.recruiters.index', [
            'recruiters' => $recruiters,
            'stats' => $stats,
            'filters' => $request->only(['status', 'subscription', 'search']),
        ]);
    }

    /**
     * Show recruiter details.
     */
    public function showRecruiter(User $user): View
    {
        abort_unless($user->hasRole('recruiter'), 404);

        $user->load(['subscription', 'shortlistedFellows']);

        // Get recruiter activity
        $activity = [
            'profile_views' => $user->subscription?->profile_views_used ?? 0,
            'contacts_made' => $user->subscription?->direct_contacts_used ?? 0,
            'shortlisted' => $user->shortlistedFellows()->count(),
        ];

        return view('admin.recruiters.show', [
            'recruiter' => $user,
            'activity' => $activity,
        ]);
    }

    /**
     * Approve a recruiter.
     */
    public function approveRecruiter(User $user): RedirectResponse
    {
        abort_unless($user->hasRole('recruiter'), 404);

        $user->update(['is_active' => true]);
        $this->auditService->log('recruiter_approved', auth()->user(), $user, 'Recruiter approved');

        // TODO: Send approval notification email

        return redirect()->route('admin.recruiters.index')
            ->with('success', 'Recruiter approved successfully.');
    }

    /**
     * Suspend a recruiter.
     */
    public function suspendRecruiter(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->hasRole('recruiter'), 404);

        $user->update([
            'is_active' => false,
            'suspended_at' => now(),
            'suspension_reason' => $request->input('reason'),
        ]);

        $this->auditService->log('recruiter_suspended', auth()->user(), $user, 'Recruiter suspended', [
            'reason' => $request->input('reason'),
        ]);

        return redirect()->route('admin.recruiters.index')
            ->with('success', 'Recruiter suspended successfully.');
    }

    /**
     * Activate a suspended recruiter.
     */
    public function activateRecruiter(User $user): RedirectResponse
    {
        abort_unless($user->hasRole('recruiter'), 404);

        $user->update([
            'is_active' => true,
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        $this->auditService->log('recruiter_activated', auth()->user(), $user, 'Recruiter activated');

        return redirect()->route('admin.recruiters.index')
            ->with('success', 'Recruiter activated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Mentor Management
    |--------------------------------------------------------------------------
    */

    /**
     * Display mentor listing.
     */
    public function mentors(Request $request): View
    {
        $query = User::role('mentor')
            ->withCount('conductedInterviews');

        // Filters
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $mentors = $query->orderByDesc('created_at')->paginate(20);

        // Stats
        $stats = [
            'total' => User::role('mentor')->count(),
            'active' => User::role('mentor')->where('is_active', true)->count(),
            'pending_approval' => User::role('mentor')->where('is_active', false)->whereNull('suspended_at')->count(),
            'total_interviews' => InterviewSession::whereNotNull('interviewer_id')->count(),
        ];

        return view('admin.mentors.index', [
            'mentors' => $mentors,
            'stats' => $stats,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Show mentor details.
     */
    public function showMentor(User $user): View
    {
        abort_unless($user->hasRole('mentor'), 404);

        $user->load('conductedInterviews');

        // Get mentor stats
        $stats = [
            'total_interviews' => $user->conductedInterviews()->count(),
            'completed_interviews' => $user->conductedInterviews()->where('status', 'completed')->count(),
            'average_rating' => $user->conductedInterviews()->whereNotNull('mentor_rating')->avg('mentor_rating') ?? 0,
            'upcoming_interviews' => $user->conductedInterviews()->where('status', 'scheduled')->where('scheduled_at', '>', now())->count(),
        ];

        // Recent interviews
        $recentInterviews = $user->conductedInterviews()
            ->with(['fellow', 'track'])
            ->orderByDesc('scheduled_at')
            ->limit(10)
            ->get();

        return view('admin.mentors.show', [
            'mentor' => $user,
            'stats' => $stats,
            'recentInterviews' => $recentInterviews,
        ]);
    }

    /**
     * Approve a mentor.
     */
    public function approveMentor(User $user): RedirectResponse
    {
        abort_unless($user->hasRole('mentor'), 404);

        $user->update(['is_active' => true]);
        $this->auditService->log('mentor_approved', auth()->user(), $user, 'Mentor approved');

        return redirect()->route('admin.mentors.index')
            ->with('success', 'Mentor approved successfully.');
    }

    /**
     * Suspend a mentor.
     */
    public function suspendMentor(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->hasRole('mentor'), 404);

        $user->update([
            'is_active' => false,
            'suspended_at' => now(),
            'suspension_reason' => $request->input('reason'),
        ]);

        $this->auditService->log('mentor_suspended', auth()->user(), $user, 'Mentor suspended', [
            'reason' => $request->input('reason'),
        ]);

        return redirect()->route('admin.mentors.index')
            ->with('success', 'Mentor suspended successfully.');
    }

    /**
     * Activate a suspended mentor.
     */
    public function activateMentor(User $user): RedirectResponse
    {
        abort_unless($user->hasRole('mentor'), 404);

        $user->update([
            'is_active' => true,
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        $this->auditService->log('mentor_activated', auth()->user(), $user, 'Mentor activated');

        return redirect()->route('admin.mentors.index')
            ->with('success', 'Mentor activated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Cohort Management
    |--------------------------------------------------------------------------
    */

    /**
     * Display cohort listing with filters.
     */
    public function cohorts(Request $request): View
    {
        $query = Cohort::with(['track', 'creator'])
            ->withCount('activeFellows');

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: show visible cohorts (not archived or cancelled)
            $query->visible();
        }

        // Track filter
        if ($request->filled('track_id')) {
            $query->where('track_id', $request->track_id);
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Get cohorts with pagination
        $cohorts = $query->orderByRaw("
            CASE status
                WHEN 'active' THEN 1
                WHEN 'upcoming' THEN 2
                WHEN 'draft' THEN 3
                WHEN 'completed' THEN 4
                ELSE 5
            END
        ")->orderBy('start_date', 'desc')->paginate(12);

        // Stats for cards
        $stats = [
            'total' => Cohort::visible()->count(),
            'active' => Cohort::where('status', Cohort::STATUS_ACTIVE)->count(),
            'upcoming' => Cohort::where('status', Cohort::STATUS_UPCOMING)->count(),
            'completed' => Cohort::where('status', Cohort::STATUS_COMPLETED)->count(),
        ];

        // Available tracks for filter
        $tracks = Track::where('is_active', true)->orderBy('name')->get();

        return view('admin.cohorts.index', [
            'cohorts' => $cohorts,
            'stats' => $stats,
            'tracks' => $tracks,
            'filters' => $request->only(['status', 'track_id', 'search']),
        ]);
    }

    /**
     * Show cohort creation form.
     */
    public function createCohort(): View
    {
        $tracks = Track::where('is_active', true)->orderBy('name')->get();

        return view('admin.cohorts.create', [
            'tracks' => $tracks,
            'statuses' => Cohort::STATUSES,
        ]);
    }

    /**
     * Store a new cohort.
     */
    public function storeCohort(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'track_id' => 'required|exists:tracks,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'enrollment_opens_at' => 'nullable|date|before_or_equal:start_date',
            'enrollment_closes_at' => 'nullable|date|after_or_equal:enrollment_opens_at|before_or_equal:start_date',
            'max_fellows' => 'required|integer|min:1|max:500',
            'min_fellows' => 'required|integer|min:1|lte:max_fellows',
            'status' => 'required|in:draft,upcoming',
        ]);

        // Add creator
        $validated['created_by'] = auth()->id();

        // Create cohort
        $cohort = Cohort::create($validated);

        // Log action
        $this->auditService->log(
            'cohort.create',
            auth()->user(),
            $cohort,
            "Created cohort: {$cohort->name}"
        );

        return redirect()->route('admin.cohorts.show', $cohort)
            ->with('success', "Cohort '{$cohort->name}' created successfully.");
    }

    /**
     * Show cohort details with fellows list.
     */
    public function showCohort(Cohort $cohort): View
    {
        $cohort->load([
            'track',
            'creator',
            'fellows' => function ($query) {
                $query->orderBy('cohort_fellows.cohort_score', 'desc');
            }
        ]);

        // Get fellows grouped by status
        $activeFellows = $cohort->fellows->filter(fn($f) => in_array($f->pivot->status, ['enrolled', 'active']));
        $completedFellows = $cohort->fellows->filter(fn($f) => $f->pivot->status === 'completed');
        $droppedFellows = $cohort->fellows->filter(fn($f) => in_array($f->pivot->status, ['dropped', 'removed']));

        // Get available fellows to add (not in any active cohort for this track)
        $existingFellowIds = $cohort->fellows->pluck('id');
        $availableFellows = User::role('fellow')
            ->with('primaryTrack.track')
            ->whereNotIn('id', $existingFellowIds)
            ->whereDoesntHave('cohorts', function ($q) use ($cohort) {
                $q->where('track_id', $cohort->track_id)
                    ->whereIn('cohort_fellows.status', ['enrolled', 'active']);
            })
            ->orderBy('name')
            ->get();

        return view('admin.cohorts.show', [
            'cohort' => $cohort,
            'activeFellows' => $activeFellows,
            'completedFellows' => $completedFellows,
            'droppedFellows' => $droppedFellows,
            'availableFellows' => $availableFellows,
        ]);
    }

    /**
     * Show cohort edit form.
     */
    public function editCohort(Cohort $cohort): View
    {
        $tracks = Track::where('is_active', true)->orderBy('name')->get();

        return view('admin.cohorts.edit', [
            'cohort' => $cohort,
            'tracks' => $tracks,
            'statuses' => Cohort::STATUSES,
        ]);
    }

    /**
     * Update a cohort.
     */
    public function updateCohort(Request $request, Cohort $cohort): RedirectResponse
    {
        $rules = [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'track_id' => 'required|exists:tracks,id',
            'max_fellows' => 'required|integer|min:1|max:500',
            'min_fellows' => 'required|integer|min:1|lte:max_fellows',
        ];

        // Date validation depends on cohort status
        if (!$cohort->hasStarted()) {
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'required|date|after:start_date';
            $rules['enrollment_opens_at'] = 'nullable|date|before_or_equal:start_date';
            $rules['enrollment_closes_at'] = 'nullable|date|after_or_equal:enrollment_opens_at|before_or_equal:start_date';
        } else {
            // Can only extend end_date for running cohorts
            $rules['end_date'] = 'required|date|after:start_date';
        }

        $validated = $request->validate($rules);

        // If cohort has started, don't allow changing start_date
        if ($cohort->hasStarted()) {
            unset($validated['start_date'], $validated['enrollment_opens_at'], $validated['enrollment_closes_at']);
        }

        $cohort->update($validated);

        // Log action
        $this->auditService->log(
            'cohort.update',
            auth()->user(),
            $cohort,
            "Updated cohort: {$cohort->name}"
        );

        return redirect()->route('admin.cohorts.show', $cohort)
            ->with('success', "Cohort '{$cohort->name}' updated successfully.");
    }

    /**
     * Delete a cohort (soft delete).
     */
    public function destroyCohort(Cohort $cohort): RedirectResponse
    {
        // Don't allow deleting active cohorts with enrolled fellows
        if ($cohort->isActive() && $cohort->fellows_count > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete an active cohort with enrolled fellows. Please complete or cancel the cohort first.');
        }

        $name = $cohort->name;
        $cohort->delete();

        // Log action
        $this->auditService->log(
            'cohort.delete',
            auth()->user(),
            null,
            "Deleted cohort: {$name}"
        );

        return redirect()->route('admin.cohorts.index')
            ->with('success', "Cohort '{$name}' deleted successfully.");
    }

    /**
     * Transition cohort to a new status.
     */
    public function transitionCohort(Request $request, Cohort $cohort): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Cohort::STATUSES),
        ]);

        $oldStatus = $cohort->status;
        $newStatus = $validated['status'];

        try {
            $cohort->transitionTo($newStatus);

            // Log action
            $this->auditService->log(
                'cohort.transition',
                auth()->user(),
                $cohort,
                "Changed cohort status from {$oldStatus} to {$newStatus}"
            );

            return redirect()->back()
                ->with('success', "Cohort status changed to '{$cohort->status_label}'.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Enroll a fellow in a cohort.
     */
    public function enrollFellow(Request $request, Cohort $cohort): RedirectResponse
    {
        $validated = $request->validate([
            'fellow_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $fellow = User::findOrFail($validated['fellow_id']);

        // Verify fellow has the fellow role
        if (!$fellow->hasRole('fellow')) {
            return redirect()->back()
                ->with('error', 'Selected user is not a fellow.');
        }

        try {
            $cohort->enrollFellow(
                $fellow,
                auth()->user(),
                $validated['notes'] ?? null
            );

            // Log action
            $this->auditService->log(
                'cohort.enroll',
                auth()->user(),
                $cohort,
                "Enrolled {$fellow->name} in {$cohort->name}"
            );

            return redirect()->back()
                ->with('success', "{$fellow->name} enrolled successfully.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove a fellow from a cohort.
     */
    public function removeFellow(Request $request, Cohort $cohort, User $fellow): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $result = $cohort->removeFellow($fellow, $validated['reason']);

        if (!$result) {
            return redirect()->back()
                ->with('error', 'Fellow is not enrolled in this cohort.');
        }

        // Log action
        $this->auditService->log(
            'cohort.remove',
            auth()->user(),
            $cohort,
            "Removed {$fellow->name} from {$cohort->name}",
            ['reason' => $validated['reason']]
        );

        return redirect()->back()
            ->with('success', "{$fellow->name} removed from cohort.");
    }

    /**
     * Bulk enroll fellows from CSV or selection.
     */
    public function bulkEnrollFellows(Request $request, Cohort $cohort): RedirectResponse
    {
        $validated = $request->validate([
            'fellow_ids' => 'required|array|min:1',
            'fellow_ids.*' => 'exists:users,id',
        ]);

        $enrolled = 0;
        $errors = [];

        foreach ($validated['fellow_ids'] as $fellowId) {
            $fellow = User::find($fellowId);
            if (!$fellow || !$fellow->hasRole('fellow')) {
                $errors[] = "User ID {$fellowId} is not a valid fellow.";
                continue;
            }

            try {
                $cohort->enrollFellow($fellow, auth()->user());
                $enrolled++;
            } catch (\Exception $e) {
                $errors[] = "{$fellow->name}: {$e->getMessage()}";
            }
        }

        $message = "{$enrolled} fellow(s) enrolled successfully.";
        if (count($errors) > 0) {
            $message .= " " . count($errors) . " failed: " . implode('; ', array_slice($errors, 0, 3));
        }

        return redirect()->back()
            ->with($errors ? 'warning' : 'success', $message);
    }

    /**
     * Mark fellow as completed in cohort.
     */
    public function markFellowCompleted(Cohort $cohort, User $fellow): RedirectResponse
    {
        $result = $cohort->markFellowCompleted($fellow);

        if (!$result) {
            return redirect()->back()
                ->with('error', 'Fellow is not actively enrolled in this cohort.');
        }

        // Log action
        $this->auditService->log(
            'cohort.complete_fellow',
            auth()->user(),
            $cohort,
            "Marked {$fellow->name} as completed in {$cohort->name}"
        );

        return redirect()->back()
            ->with('success', "{$fellow->name} marked as completed.");
    }

    /*
    |--------------------------------------------------------------------------
    | Interview Management
    |--------------------------------------------------------------------------
    */

    /**
     * Display interview listing with filters.
     */
    public function interviews(Request $request): View
    {
        $query = InterviewSession::with(['fellow', 'track', 'interviewer']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Mode filter (AI/Human)
        if ($request->filled('mode')) {
            $query->where('mode', $request->mode);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Track filter
        if ($request->filled('track_id')) {
            $query->where('track_id', $request->track_id);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_at', '<=', $request->date_to);
        }

        // Search by fellow name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('fellow', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Unassigned filter (human interviews without mentor)
        if ($request->filled('unassigned') && $request->unassigned) {
            $query->where('mode', InterviewMode::HUMAN)
                  ->whereNull('interviewer_id');
        }

        $interviews = $query->orderByDesc('scheduled_at')->paginate(20);

        // Get stats
        $stats = [
            'total' => InterviewSession::count(),
            'scheduled' => InterviewSession::where('status', InterviewStatus::SCHEDULED)->count(),
            'completed' => InterviewSession::where('status', InterviewStatus::COMPLETED)->count(),
            'in_progress' => InterviewSession::where('status', InterviewStatus::IN_PROGRESS)->count(),
            'cancelled' => InterviewSession::where('status', InterviewStatus::CANCELLED)->count(),
            'ai_interviews' => InterviewSession::where('mode', InterviewMode::AI)->count(),
            'human_interviews' => InterviewSession::where('mode', InterviewMode::HUMAN)->count(),
            'unassigned' => InterviewSession::where('mode', InterviewMode::HUMAN)
                ->whereNull('interviewer_id')
                ->where('status', InterviewStatus::SCHEDULED)
                ->count(),
            'avg_score' => InterviewSession::where('status', InterviewStatus::COMPLETED)
                ->whereNotNull('score')
                ->avg('score'),
            'today' => InterviewSession::whereDate('scheduled_at', today())->count(),
        ];

        // Get filter options
        $tracks = Track::where('is_active', true)->get();
        $mentors = User::role('mentor')->where('is_active', true)->get();

        return view('admin.interviews.index', [
            'interviews' => $interviews,
            'stats' => $stats,
            'tracks' => $tracks,
            'mentors' => $mentors,
            'interviewTypes' => InterviewType::cases(),
            'interviewModes' => InterviewMode::cases(),
            'interviewStatuses' => InterviewStatus::cases(),
            'filters' => $request->only(['status', 'mode', 'type', 'track_id', 'date_from', 'date_to', 'search', 'unassigned']),
        ]);
    }

    /**
     * Show interview details.
     */
    public function showInterview(InterviewSession $interview): View
    {
        $interview->load(['fellow', 'track', 'interviewer']);

        // Get available mentors for assignment
        $availableMentors = User::role('mentor')
            ->where('is_active', true)
            ->get();

        // Get fellow's other interviews for context
        $fellowInterviews = InterviewSession::with(['track', 'interviewer'])
            ->where('fellow_id', $interview->fellow_id)
            ->where('id', '!=', $interview->id)
            ->orderByDesc('scheduled_at')
            ->limit(5)
            ->get();

        return view('admin.interviews.show', [
            'interview' => $interview,
            'availableMentors' => $availableMentors,
            'fellowInterviews' => $fellowInterviews,
        ]);
    }

    /**
     * Assign a mentor to a human interview.
     */
    public function assignMentor(Request $request, InterviewSession $interview): RedirectResponse
    {
        $request->validate([
            'mentor_id' => 'required|exists:users,id',
        ]);

        // Verify the interview is human mode
        if ($interview->mode !== InterviewMode::HUMAN) {
            return redirect()->back()
                ->with('error', 'Cannot assign mentor to AI interviews.');
        }

        $mentor = User::findOrFail($request->mentor_id);
        
        // Verify user is a mentor
        if (!$mentor->hasRole('mentor')) {
            return redirect()->back()
                ->with('error', 'Selected user is not a mentor.');
        }

        $oldMentor = $interview->interviewer;
        $interview->update(['interviewer_id' => $mentor->id]);

        $this->auditService->log('interview_mentor_assigned', auth()->user(), $interview, 'Mentor assigned to interview', [
            'mentor_id' => $mentor->id,
            'mentor_name' => $mentor->name,
            'old_mentor_id' => $oldMentor?->id,
        ]);

        return redirect()->route('admin.interviews.show', $interview)
            ->with('success', "Mentor {$mentor->name} assigned successfully.");
    }

    /**
     * Cancel an interview.
     */
    public function cancelInterview(Request $request, InterviewSession $interview): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        // Can only cancel scheduled or pending interviews
        if (!in_array($interview->status, [InterviewStatus::SCHEDULED, InterviewStatus::PENDING])) {
            return redirect()->back()
                ->with('error', 'Only scheduled or pending interviews can be cancelled.');
        }

        $interview->update([
            'status' => InterviewStatus::CANCELLED,
            'cancellation_reason' => $request->reason,
        ]);

        $this->auditService->log('interview_cancelled', auth()->user(), $interview, 'Interview cancelled by admin', [
            'reason' => $request->reason,
        ]);

        return redirect()->route('admin.interviews.index')
            ->with('success', 'Interview cancelled successfully.');
    }

    /**
     * Reschedule an interview.
     */
    public function rescheduleInterview(Request $request, InterviewSession $interview): RedirectResponse
    {
        $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        $oldDate = $interview->scheduled_at;
        $interview->update([
            'scheduled_at' => $request->scheduled_at,
            'status' => InterviewStatus::SCHEDULED,
        ]);

        $this->auditService->log('interview_rescheduled', auth()->user(), $interview, 'Interview rescheduled by admin', [
            'old_date' => $oldDate?->toIso8601String(),
            'new_date' => $interview->scheduled_at->toIso8601String(),
        ]);

        return redirect()->route('admin.interviews.show', $interview)
            ->with('success', 'Interview rescheduled successfully.');
    }

    /**
     * Display interview analytics dashboard.
     */
    public function interviewAnalytics(Request $request): View
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Overall stats
        $overallStats = [
            'total_interviews' => InterviewSession::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'completed' => InterviewSession::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', InterviewStatus::COMPLETED)->count(),
            'avg_score' => InterviewSession::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', InterviewStatus::COMPLETED)
                ->whereNotNull('score')
                ->avg('score'),
            'completion_rate' => 0,
        ];
        
        if ($overallStats['total_interviews'] > 0) {
            $overallStats['completion_rate'] = round(($overallStats['completed'] / $overallStats['total_interviews']) * 100, 1);
        }

        // Interviews by type
        $byType = InterviewSession::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('type', DB::raw('count(*) as count'), DB::raw('avg(score) as avg_score'))
            ->where('status', InterviewStatus::COMPLETED)
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type->label(),
                    'type_value' => $item->type->value,
                    'count' => $item->count,
                    'avg_score' => round($item->avg_score ?? 0, 1),
                ];
            });

        // Interviews by mode (AI vs Human)
        $byMode = InterviewSession::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('mode', DB::raw('count(*) as count'), DB::raw('avg(score) as avg_score'))
            ->where('status', InterviewStatus::COMPLETED)
            ->groupBy('mode')
            ->get()
            ->map(function ($item) {
                return [
                    'mode' => $item->mode->label(),
                    'mode_value' => $item->mode->value,
                    'count' => $item->count,
                    'avg_score' => round($item->avg_score ?? 0, 1),
                ];
            });

        // Daily interview trend (last 30 days)
        $dailyTrend = InterviewSession::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Score distribution
        $scoreRanges = [
            '0-50' => InterviewSession::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', InterviewStatus::COMPLETED)
                ->whereBetween('score', [0, 50])->count(),
            '51-70' => InterviewSession::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', InterviewStatus::COMPLETED)
                ->whereBetween('score', [51, 70])->count(),
            '71-85' => InterviewSession::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', InterviewStatus::COMPLETED)
                ->whereBetween('score', [71, 85])->count(),
            '86-100' => InterviewSession::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', InterviewStatus::COMPLETED)
                ->whereBetween('score', [86, 100])->count(),
        ];

        // Top performers
        $topPerformers = InterviewSession::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', InterviewStatus::COMPLETED)
            ->whereNotNull('score')
            ->with('fellow')
            ->select('fellow_id', DB::raw('avg(score) as avg_score'), DB::raw('count(*) as interview_count'))
            ->groupBy('fellow_id')
            ->having('interview_count', '>=', 2)
            ->orderByDesc('avg_score')
            ->limit(10)
            ->get();

        // Mentor leaderboard
        $mentorStats = InterviewSession::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('mode', InterviewMode::HUMAN)
            ->where('status', InterviewStatus::COMPLETED)
            ->whereNotNull('interviewer_id')
            ->with('interviewer')
            ->select('interviewer_id', DB::raw('count(*) as count'), DB::raw('avg(interviewer_rating) as avg_rating'))
            ->groupBy('interviewer_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Interviews by track
        $byTrack = InterviewSession::whereBetween('created_at', [$dateFrom, $dateTo])
            ->with('track')
            ->select('track_id', DB::raw('count(*) as count'), DB::raw('avg(score) as avg_score'))
            ->where('status', InterviewStatus::COMPLETED)
            ->whereNotNull('track_id')
            ->groupBy('track_id')
            ->get()
            ->map(function ($item) {
                return [
                    'track' => $item->track?->name ?? 'Unknown',
                    'count' => $item->count,
                    'avg_score' => round($item->avg_score ?? 0, 1),
                ];
            });

        return view('admin.interviews.analytics', [
            'overallStats' => $overallStats,
            'byType' => $byType,
            'byMode' => $byMode,
            'dailyTrend' => $dailyTrend,
            'scoreRanges' => $scoreRanges,
            'topPerformers' => $topPerformers,
            'mentorStats' => $mentorStats,
            'byTrack' => $byTrack,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * Export interviews to CSV.
     */
    public function exportInterviews(Request $request)
    {
        $query = InterviewSession::with(['fellow', 'track', 'interviewer']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('mode')) {
            $query->where('mode', $request->mode);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_at', '<=', $request->date_to);
        }

        $interviews = $query->orderByDesc('scheduled_at')->get();

        $filename = 'interviews_export_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($interviews) {
            $file = fopen('php://output', 'w');
            
            // CSV header
            fputcsv($file, [
                'ID', 'Fellow', 'Email', 'Track', 'Type', 'Mode', 'Status',
                'Score', 'Scheduled At', 'Completed At', 'Mentor', 'Duration (min)'
            ]);

            foreach ($interviews as $interview) {
                fputcsv($file, [
                    $interview->id,
                    $interview->fellow?->name ?? 'N/A',
                    $interview->fellow?->email ?? 'N/A',
                    $interview->track?->name ?? 'N/A',
                    $interview->type->label(),
                    $interview->mode->label(),
                    $interview->status->label(),
                    $interview->score ?? 'N/A',
                    $interview->scheduled_at?->format('Y-m-d H:i'),
                    $interview->completed_at?->format('Y-m-d H:i'),
                    $interview->interviewer?->name ?? 'N/A',
                    $interview->duration_minutes ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================================================================
    // PROGRAM MANAGEMENT METHODS
    // =========================================================================

    /**
     * Display program listing with statistics.
     */
    public function programs(Request $request): View
    {
        $query = Program::withCount(['fellows', 'activeFellows', 'graduates'])
            ->with('creator');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sponsor_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Year filter
        if ($request->filled('year')) {
            $query->whereYear('start_date', $request->year);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $programs = $query->paginate(15)->withQueryString();

        // Get summary statistics
        $stats = [
            'total' => Program::count(),
            'active' => Program::where('status', Program::STATUS_ACTIVE)->count(),
            'enrolling' => Program::where('status', Program::STATUS_ENROLLING)->count(),
            'upcoming' => Program::where('status', Program::STATUS_UPCOMING)->count(),
            'graduated' => Program::where('status', Program::STATUS_GRADUATED)->count(),
            'total_fellows' => ProgramFellow::count(),
            'total_graduates' => ProgramFellow::where('status', 'completed')->count(),
            'employed_alumni' => ProgramFellow::whereNotNull('employment_status')
                ->where('employment_status', 'employed')->count(),
        ];

        // Get years for filter dropdown
        $years = Program::selectRaw('DISTINCT YEAR(start_date) as year')
            ->whereNotNull('start_date')
            ->orderByDesc('year')
            ->pluck('year');

        return view('admin.programs.index', [
            'programs' => $programs,
            'stats' => $stats,
            'years' => $years,
            'statuses' => Program::getStatuses(),
            'filters' => $request->only(['search', 'status', 'year', 'sort_by', 'sort_dir']),
        ]);
    }

    /**
     * Show the form for creating a new program.
     */
    public function createProgram(): View
    {
        return view('admin.programs.create', [
            'statuses' => Program::getStatuses(),
        ]);
    }

    /**
     * Store a newly created program.
     */
    public function storeProgram(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:programs,name',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'graduation_date' => 'nullable|date|after_or_equal:end_date',
            'max_capacity' => 'nullable|integer|min:1',
            'status' => 'required|in:' . implode(',', array_keys(Program::getStatuses())),
            'sponsor_name' => 'nullable|string|max:255',
            'sponsor_logo' => 'nullable|url|max:500',
            'sponsor_website' => 'nullable|url|max:500',
            'certificate_template' => 'nullable|string|max:500',
            'certificate_prefix' => 'nullable|string|max:50',
            'milestones' => 'nullable|array',
            'milestones.*.name' => 'required_with:milestones|string|max:255',
            'milestones.*.target_date' => 'nullable|date',
            'milestones.*.description' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        // Generate slug from name
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        
        // Ensure unique slug
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (Program::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter++;
        }

        $validated['created_by'] = auth()->id();

        $program = Program::create($validated);

        $this->auditService->log('program_created', auth()->user(), $program, 'Program created', [
            'program_name' => $program->name,
            'status' => $program->status,
        ]);

        return redirect()->route('admin.programs.show', $program)
            ->with('success', "Program '{$program->name}' created successfully.");
    }

    /**
     * Display the specified program with detailed statistics.
     */
    public function showProgram(Program $program): View
    {
        $program->load(['creator', 'fellows' => function ($query) {
            $query->orderByPivot('created_at', 'desc');
        }]);

        // Fellow statistics by status
        $fellowStats = [
            'total' => $program->fellows()->count(),
            'enrolled' => $program->fellows()->wherePivot('status', 'enrolled')->count(),
            'active' => $program->fellows()->wherePivot('status', 'active')->count(),
            'completed' => $program->fellows()->wherePivot('status', 'completed')->count(),
            'dropped' => $program->fellows()->wherePivot('status', 'dropped')->count(),
            'removed' => $program->fellows()->wherePivot('status', 'removed')->count(),
        ];

        // Alumni employment stats
        $alumniStats = [
            'employed' => $program->fellows()
                ->wherePivot('status', 'completed')
                ->wherePivot('employment_status', 'employed')
                ->count(),
            'freelancing' => $program->fellows()
                ->wherePivot('status', 'completed')
                ->wherePivot('employment_status', 'freelancing')
                ->count(),
            'further_education' => $program->fellows()
                ->wherePivot('status', 'completed')
                ->wherePivot('employment_status', 'further_education')
                ->count(),
            'seeking' => $program->fellows()
                ->wherePivot('status', 'completed')
                ->wherePivot('employment_status', 'seeking')
                ->count(),
        ];

        // Get track distribution of fellows
        $trackDistribution = DB::table('program_fellows')
            ->join('users', 'program_fellows.fellow_id', '=', 'users.id')
            ->join('tracks', 'users.track_id', '=', 'tracks.id')
            ->where('program_fellows.program_id', $program->id)
            ->select('tracks.name', DB::raw('count(*) as count'))
            ->groupBy('tracks.id', 'tracks.name')
            ->get();

        // Milestone progress
        $milestoneProgress = [];
        if ($program->milestones) {
            foreach ($program->milestones as $index => $milestone) {
                $completedCount = DB::table('program_fellows')
                    ->where('program_id', $program->id)
                    ->where('status', '!=', 'removed')
                    ->whereJsonContains('milestones_completed', (string) $index)
                    ->count();

                $milestoneProgress[] = [
                    'index' => $index,
                    'name' => $milestone['name'] ?? 'Milestone ' . ($index + 1),
                    'target_date' => $milestone['target_date'] ?? null,
                    'description' => $milestone['description'] ?? null,
                    'completed_count' => $completedCount,
                    'completion_rate' => $fellowStats['total'] > 0 
                        ? round(($completedCount / $fellowStats['total']) * 100, 1) 
                        : 0,
                ];
            }
        }

        // Get available fellows to enroll (not already in this program)
        $availableFellows = User::role('fellow')
            ->where('is_active', true)
            ->whereDoesntHave('programs', function ($query) use ($program) {
                $query->where('program_id', $program->id);
            })
            ->orderBy('name')
            ->get();

        // Recent activity
        $recentEnrollments = $program->programFellows()
            ->with('fellow')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.programs.show', [
            'program' => $program,
            'fellowStats' => $fellowStats,
            'alumniStats' => $alumniStats,
            'trackDistribution' => $trackDistribution,
            'milestoneProgress' => $milestoneProgress,
            'availableFellows' => $availableFellows,
            'recentEnrollments' => $recentEnrollments,
            'fellowStatuses' => ProgramFellow::getStatuses(),
            'employmentStatuses' => ProgramFellow::getEmploymentStatuses(),
        ]);
    }

    /**
     * Show the form for editing a program.
     */
    public function editProgram(Program $program): View
    {
        return view('admin.programs.edit', [
            'program' => $program,
            'statuses' => Program::getStatuses(),
        ]);
    }

    /**
     * Update the specified program.
     */
    public function updateProgram(Request $request, Program $program): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:programs,name,' . $program->id,
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'graduation_date' => 'nullable|date|after_or_equal:end_date',
            'max_capacity' => 'nullable|integer|min:1',
            'status' => 'required|in:' . implode(',', array_keys(Program::getStatuses())),
            'sponsor_name' => 'nullable|string|max:255',
            'sponsor_logo' => 'nullable|url|max:500',
            'sponsor_website' => 'nullable|url|max:500',
            'certificate_template' => 'nullable|string|max:500',
            'certificate_prefix' => 'nullable|string|max:50',
            'milestones' => 'nullable|array',
            'milestones.*.name' => 'required_with:milestones|string|max:255',
            'milestones.*.target_date' => 'nullable|date',
            'milestones.*.description' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        // Update slug if name changed
        if ($program->name !== $validated['name']) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
            $baseSlug = $validated['slug'];
            $counter = 1;
            while (Program::where('slug', $validated['slug'])->where('id', '!=', $program->id)->exists()) {
                $validated['slug'] = $baseSlug . '-' . $counter++;
            }
        }

        $oldStatus = $program->status;
        $program->update($validated);

        $this->auditService->log('program_updated', auth()->user(), $program, 'Program updated', [
            'program_name' => $program->name,
            'old_status' => $oldStatus,
            'new_status' => $program->status,
        ]);

        return redirect()->route('admin.programs.show', $program)
            ->with('success', "Program '{$program->name}' updated successfully.");
    }

    /**
     * Delete the specified program.
     */
    public function destroyProgram(Program $program): RedirectResponse
    {
        // Prevent deletion if has fellows
        if ($program->fellows()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete program with enrolled fellows. Remove all fellows first.');
        }

        $programName = $program->name;

        $this->auditService->log('program_deleted', auth()->user(), $program, 'Program deleted', [
            'program_name' => $programName,
        ]);

        $program->delete();

        return redirect()->route('admin.programs.index')
            ->with('success', "Program '{$programName}' deleted successfully.");
    }

    /**
     * Transition program status.
     */
    public function transitionProgram(Request $request, Program $program): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Program::getStatuses())),
        ]);

        $oldStatus = $program->status;
        $newStatus = $request->status;

        // Validate transition logic
        $validTransitions = [
            Program::STATUS_DRAFT => [Program::STATUS_UPCOMING, Program::STATUS_ENROLLING],
            Program::STATUS_UPCOMING => [Program::STATUS_ENROLLING, Program::STATUS_ACTIVE, Program::STATUS_DRAFT],
            Program::STATUS_ENROLLING => [Program::STATUS_ACTIVE, Program::STATUS_UPCOMING],
            Program::STATUS_ACTIVE => [Program::STATUS_GRADUATED, Program::STATUS_ARCHIVED],
            Program::STATUS_GRADUATED => [Program::STATUS_ARCHIVED],
            Program::STATUS_ARCHIVED => [], // Cannot transition from archived
        ];

        if (!in_array($newStatus, $validTransitions[$oldStatus] ?? [])) {
            return redirect()->back()
                ->with('error', "Cannot transition from '{$oldStatus}' to '{$newStatus}'.");
        }

        // If transitioning to graduated, auto-graduate all active fellows
        if ($newStatus === Program::STATUS_GRADUATED) {
            $program->fellows()
                ->wherePivot('status', 'active')
                ->each(function ($fellow) use ($program) {
                    $program->graduateFellow($fellow, now());
                });
        }

        $program->update(['status' => $newStatus]);

        $this->auditService->log('program_status_changed', auth()->user(), $program, 'Program status changed', [
            'program_name' => $program->name,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        return redirect()->route('admin.programs.show', $program)
            ->with('success', "Program status changed from '{$oldStatus}' to '{$newStatus}'.");
    }

    /**
     * Enroll a fellow in the program.
     */
    public function enrollFellowInProgram(Request $request, Program $program): RedirectResponse
    {
        $request->validate([
            'fellow_id' => 'required|exists:users,id',
            'status' => 'nullable|in:enrolled,active',
            'notes' => 'nullable|string|max:1000',
        ]);

        $fellow = User::findOrFail($request->fellow_id);

        // Check if already enrolled
        if ($program->fellows()->where('fellow_id', $fellow->id)->exists()) {
            return redirect()->back()
                ->with('error', "{$fellow->name} is already enrolled in this program.");
        }

        // Check capacity
        if ($program->max_capacity && $program->fellows()->count() >= $program->max_capacity) {
            return redirect()->back()
                ->with('error', 'Program is at maximum capacity.');
        }

        // Enroll the fellow
        $enrollment = $program->enrollFellow($fellow, auth()->user(), $request->notes);

        // Update status if requested differently
        if ($request->input('status', 'enrolled') === 'active') {
            $enrollment->update(['status' => 'active']);
        }

        $this->auditService->log('fellow_enrolled_in_program', auth()->user(), $program, 'Fellow enrolled in program', [
            'fellow_id' => $fellow->id,
            'fellow_name' => $fellow->name,
            'program_name' => $program->name,
        ]);

        return redirect()->route('admin.programs.show', $program)
            ->with('success', "{$fellow->name} enrolled successfully.");
    }

    /**
     * Bulk enroll fellows in the program.
     */
    public function bulkEnrollFellowsInProgram(Request $request, Program $program): RedirectResponse
    {
        $request->validate([
            'fellow_ids' => 'required|array|min:1',
            'fellow_ids.*' => 'exists:users,id',
            'status' => 'nullable|in:enrolled,active',
        ]);

        $enrolled = 0;
        $skipped = 0;
        $status = $request->input('status', 'enrolled');

        foreach ($request->fellow_ids as $fellowId) {
            $fellow = User::find($fellowId);
            if (!$fellow) continue;

            // Check if already enrolled
            if ($program->fellows()->where('fellow_id', $fellow->id)->exists()) {
                $skipped++;
                continue;
            }

            // Check capacity
            if ($program->max_capacity && $program->fellows()->count() >= $program->max_capacity) {
                break;
            }

            $enrollment = $program->enrollFellow($fellow, auth()->user());
            if ($status === 'active') {
                $enrollment->update(['status' => 'active']);
            }
            $enrolled++;
        }

        $this->auditService->log('bulk_enrollment_in_program', auth()->user(), $program, 'Bulk enrollment in program', [
            'enrolled_count' => $enrolled,
            'skipped_count' => $skipped,
            'program_name' => $program->name,
        ]);

        $message = "Enrolled {$enrolled} fellow(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} already enrolled.";
        }

        return redirect()->route('admin.programs.show', $program)
            ->with('success', $message);
    }

    /**
     * Remove a fellow from the program.
     */
    public function removeFellowFromProgram(Request $request, Program $program, User $fellow): RedirectResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $enrollment = $program->fellows()->where('fellow_id', $fellow->id)->first();
        if (!$enrollment) {
            return redirect()->back()
                ->with('error', "{$fellow->name} is not enrolled in this program.");
        }

        $program->removeFellow($fellow, $request->reason);

        $this->auditService->log('fellow_removed_from_program', auth()->user(), $program, 'Fellow removed from program', [
            'fellow_id' => $fellow->id,
            'fellow_name' => $fellow->name,
            'program_name' => $program->name,
            'reason' => $request->reason,
        ]);

        return redirect()->route('admin.programs.show', $program)
            ->with('success', "{$fellow->name} removed from program.");
    }

    /**
     * Graduate a fellow from the program.
     */
    public function graduateFellowFromProgram(Request $request, Program $program, User $fellow): RedirectResponse
    {
        $request->validate([
            'graduation_date' => 'nullable|date',
            'issue_certificate' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $enrollment = $program->fellows()->where('fellow_id', $fellow->id)->first();
        if (!$enrollment) {
            return redirect()->back()
                ->with('error', "{$fellow->name} is not enrolled in this program.");
        }

        $graduationDate = $request->input('graduation_date', now());
        $program->graduateFellow($fellow, $graduationDate);

        // Issue certificate if requested
        if ($request->boolean('issue_certificate')) {
            $program->issueCertificate($fellow);
        }

        // Update notes if provided
        if ($request->filled('notes')) {
            $program->fellows()->updateExistingPivot($fellow->id, [
                'notes' => $request->notes,
            ]);
        }

        $this->auditService->log('fellow_graduated_from_program', auth()->user(), $program, 'Fellow graduated from program', [
            'fellow_id' => $fellow->id,
            'fellow_name' => $fellow->name,
            'program_name' => $program->name,
            'graduation_date' => $graduationDate,
            'certificate_issued' => $request->boolean('issue_certificate'),
        ]);

        return redirect()->route('admin.programs.show', $program)
            ->with('success', "{$fellow->name} graduated successfully." . 
                ($request->boolean('issue_certificate') ? ' Certificate issued.' : ''));
    }

    /**
     * Issue certificate to a graduated fellow.
     */
    public function issueCertificateForProgram(Program $program, User $fellow): RedirectResponse
    {
        $enrollment = ProgramFellow::where('program_id', $program->id)
            ->where('fellow_id', $fellow->id)
            ->first();

        if (!$enrollment) {
            return redirect()->back()
                ->with('error', "{$fellow->name} is not enrolled in this program.");
        }

        if ($enrollment->status !== 'completed') {
            return redirect()->back()
                ->with('error', 'Can only issue certificates to graduated fellows.');
        }

        if ($enrollment->certificate_number) {
            return redirect()->back()
                ->with('error', 'Certificate already issued for this fellow.');
        }

        $program->issueCertificate($fellow);

        $this->auditService->log('certificate_issued', auth()->user(), $program, 'Certificate issued', [
            'fellow_id' => $fellow->id,
            'fellow_name' => $fellow->name,
            'program_name' => $program->name,
        ]);

        return redirect()->route('admin.programs.show', $program)
            ->with('success', "Certificate issued to {$fellow->name}.");
    }

    /**
     * Update alumni employment outcome.
     */
    public function updateAlumniOutcome(Request $request, Program $program, User $fellow): RedirectResponse
    {
        $request->validate([
            'employment_status' => 'required|in:' . implode(',', array_keys(ProgramFellow::getEmploymentStatuses())),
            'employer_name' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'job_start_date' => 'nullable|date',
            'salary_range' => 'nullable|string|max:100',
        ]);

        $enrollment = ProgramFellow::where('program_id', $program->id)
            ->where('fellow_id', $fellow->id)
            ->first();

        if (!$enrollment) {
            return redirect()->back()
                ->with('error', "{$fellow->name} is not enrolled in this program.");
        }

        if ($enrollment->status !== 'completed') {
            return redirect()->back()
                ->with('error', 'Can only track outcomes for graduated fellows.');
        }

        $enrollment->updateEmploymentOutcome(
            $request->employment_status,
            $request->only(['employer_name', 'job_title', 'job_start_date', 'salary_range'])
        );

        // Recalculate program statistics
        $program->recalculateStatistics();

        $this->auditService->log('alumni_outcome_updated', auth()->user(), $program, 'Alumni outcome updated', [
            'fellow_id' => $fellow->id,
            'fellow_name' => $fellow->name,
            'employment_status' => $request->employment_status,
        ]);

        return redirect()->route('admin.programs.show', $program)
            ->with('success', "Employment outcome updated for {$fellow->name}.");
    }

    /**
     * Send announcement to all program fellows.
     */
    public function sendProgramAnnouncement(Request $request, Program $program): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'send_email' => 'nullable|boolean',
        ]);

        // Get all active/enrolled fellows in the program
        $fellows = $program->fellows()
            ->wherePivotIn('status', ['enrolled', 'active'])
            ->get();

        if ($fellows->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No active fellows in this program to send announcement to.');
        }

        // Store announcement in meta
        $announcements = $program->meta['announcements'] ?? [];
        $announcements[] = [
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'title' => $request->title,
            'message' => $request->message,
            'sent_at' => now()->toIso8601String(),
            'sent_by' => auth()->id(),
            'recipient_count' => $fellows->count(),
            'email_sent' => $request->boolean('send_email'),
        ];

        $program->update([
            'meta' => array_merge($program->meta ?? [], ['announcements' => $announcements]),
        ]);

        // TODO: If send_email is true, dispatch email job to all fellows
        // Mail::to($fellows)->queue(new ProgramAnnouncementMail($program, $request->title, $request->message));

        $this->auditService->log('program_announcement_sent', auth()->user(), $program, 'Program announcement sent', [
            'title' => $request->title,
            'recipient_count' => $fellows->count(),
            'email_sent' => $request->boolean('send_email'),
        ]);

        return redirect()->route('admin.programs.show', $program)
            ->with('success', "Announcement sent to {$fellows->count()} fellow(s).");
    }

    /**
     * Export program fellows to CSV.
     */
    public function exportProgramFellows(Request $request, Program $program)
    {
        $fellows = $program->fellows()
            ->with(['track', 'cohorts'])
            ->get();

        $filename = \Illuminate\Support\Str::slug($program->name) . '_fellows_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($fellows, $program) {
            $file = fopen('php://output', 'w');

            // CSV header
            fputcsv($file, [
                'Name', 'Email', 'Track', 'Status', 'Enrolled At', 'Graduated At',
                'Certificate Number', 'Employment Status', 'Employer', 'Job Title'
            ]);

            foreach ($fellows as $fellow) {
                $pivot = $fellow->pivot;
                fputcsv($file, [
                    $fellow->name,
                    $fellow->email,
                    $fellow->track?->name ?? 'N/A',
                    $pivot->status,
                    $pivot->enrolled_at?->format('Y-m-d'),
                    $pivot->graduated_at?->format('Y-m-d'),
                    $pivot->certificate_number ?? 'N/A',
                    $pivot->employment_status ?? 'N/A',
                    $pivot->employer_name ?? 'N/A',
                    $pivot->job_title ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
