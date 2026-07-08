<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Cohort;
use App\Models\CohortFellow;
use App\Models\InterviewSession;
use App\Models\Notification;
use App\Models\Program;
use App\Models\ProgramFellow;
use App\Services\CareerCapitalCalculator;
use App\Services\WeeklyProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard Controller
 * 
 * Handles the fellow dashboard with Career Capital overview,
 * recent activities, upcoming interviews, and progress tracking.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class DashboardController extends Controller
{
    public function __construct(
        protected CareerCapitalCalculator $calculator,
        protected WeeklyProgressService $weeklyProgressService
    ) {}

    /**
     * Display the fellow onboarding page.
     */
    public function onboarding(): View
    {
        return view('fellow.onboarding');
    }

    /**
     * Display the fellow dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Get active track from session (resolved by ResolveActiveTrack middleware)
        // Falls back to DB primary track if no session selection
        $activeTrack = $request->attributes->get('activeTrack') ?? $user->activeTrack();
        
        // Alias for view compatibility (dashboard view uses $primaryTrack)
        $primaryTrack = $activeTrack;
        
        // Get all tracks for the user (already resolved by middleware)
        $fellowTracks = $request->attributes->get('fellowTracks') ?? $user->fellowTracks()
            ->with('track')
            ->orderByDesc('is_primary')
            ->orderByDesc('score')
            ->get();
        
        // Get current cohort information
        $currentCohort = null;
        $cohortStats = [
            'rank' => null,
            'cohortSize' => 0,
            'bestStreak' => 0,
        ];
        
        if ($primaryTrack) {
            // Find active cohort for user in their primary track
            $cohortFellow = CohortFellow::whereHas('cohort', function($q) use ($primaryTrack) {
                $q->where('track_id', $primaryTrack->track_id)
                  ->whereIn('status', [Cohort::STATUS_ACTIVE, Cohort::STATUS_UPCOMING]);
            })
                ->where('fellow_id', $user->id)
                ->whereIn('status', ['enrolled', 'active'])
                ->with(['cohort', 'cohort.track'])
                ->first();
            
            if ($cohortFellow && $cohortFellow->cohort) {
                $currentCohort = $cohortFellow->cohort;
                $cohortStats['cohortSize'] = $currentCohort->fellows_count;
                $cohortStats['rank'] = $cohortFellow->rank ?? 
                    CohortFellow::where('cohort_id', $currentCohort->id)
                        ->where('cohort_score', '>', $cohortFellow->cohort_score ?? 0)
                        ->count() + 1;
            }
        }

        // Get Career Capital breakdown
        $scoreBreakdown = $primaryTrack 
            ? $this->calculator->getScoreBreakdown($user, $primaryTrack->track)
            : null;

        // Recent activities (filtered by active track when available)
        $recentActivitiesQuery = $user->activities()->with('track');
        if ($activeTrack) {
            $recentActivitiesQuery->where('track_id', $activeTrack->track_id);
        }
        $recentActivities = $recentActivitiesQuery
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Pending activities count (filtered by active track)
        $pendingQuery = $user->activities()->where('status', 'pending');
        if ($activeTrack) {
            $pendingQuery->where('track_id', $activeTrack->track_id);
        }
        $pendingActivitiesCount = $pendingQuery->count();

        // Upcoming interviews
        $upcomingInterviews = InterviewSession::where('fellow_id', $user->id)
            ->upcoming()
            ->with('track')
            ->limit(3)
            ->get();

        // Weekly progress status
        $currentWeekProgress = $this->weeklyProgressService->getCurrentProgress($user);
        $weeklyProgressHistory = $this->weeklyProgressService->getHistory($user, 12);
        $missingPillars = $this->weeklyProgressService->getMissingPillars($user);
        $streak = $this->weeklyProgressService->getStreak($user);

        // Unread notifications
        $notifications = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Calculate progress metrics (filtered by active track)
        $monthlyQuery = $user->activities()
            ->where('created_at', '>=', now()->startOfMonth())
            ->where('status', 'approved');
        if ($activeTrack) {
            $monthlyQuery->where('track_id', $activeTrack->track_id);
        }
        $activitiesThisMonth = $monthlyQuery->count();

        $lastMonthQuery = $user->activities()
            ->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth()
            ])
            ->where('status', 'approved');
        if ($activeTrack) {
            $lastMonthQuery->where('track_id', $activeTrack->track_id);
        }
        $activitiesLastMonth = $lastMonthQuery->count();

        $activityGrowth = $activitiesLastMonth > 0
            ? round((($activitiesThisMonth - $activitiesLastMonth) / $activitiesLastMonth) * 100, 1)
            : ($activitiesThisMonth > 0 ? 100 : 0);

        // Get current program information
        $currentProgram = null;
        $programEnrollment = null;
        
        $programFellow = ProgramFellow::whereIn('status', [ProgramFellow::STATUS_ENROLLED, ProgramFellow::STATUS_ACTIVE])
            ->where('fellow_id', $user->id)
            ->whereHas('program', function($q) {
                $q->whereIn('status', [Program::STATUS_ACTIVE, Program::STATUS_ENROLLING]);
            })
            ->with(['program'])
            ->orderByDesc('enrolled_at')
            ->first();
        
        if ($programFellow) {
            $currentProgram = $programFellow->program;
            $programEnrollment = $programFellow;
        }

        // Get unpaid fees information
        $unpaidFeesQuery = \App\Models\Fee::where('fellow_id', $user->id)
            ->whereNotIn('status', [\App\Models\Fee::STATUS_PAID, \App\Models\Fee::STATUS_WAIVED])
            ->whereColumn('amount_paid', '<', 'amount_total');
        
        $unpaidFeesCount = (clone $unpaidFeesQuery)->count();
        $nextUnpaidFee = $unpaidFeesQuery->orderBy('final_due_date', 'asc')->first();

        // Get active attendance session and fellow's record for it
        $activeAttendanceSession = \App\Models\AttendanceSession::where('status', 'active')->first();
        $attendanceRecord = null;
        if ($activeAttendanceSession) {
            $attendanceRecord = \App\Models\AttendanceRecord::where('session_id', $activeAttendanceSession->id)
                ->where('fellow_id', $user->id)
                ->first();
        }

        return view('dashboard.index', [
            'user' => $user,
            'primaryTrack' => $primaryTrack,
            'fellowTracks' => $fellowTracks,
            'scoreBreakdown' => $scoreBreakdown,
            'recentActivities' => $recentActivities,
            'pendingActivitiesCount' => $pendingActivitiesCount,
            'upcomingInterviews' => $upcomingInterviews,
            'weeklyProgress' => $currentWeekProgress,
            'weeklyProgressHistory' => $weeklyProgressHistory,
            'missingPillars' => $missingPillars,
            'streak' => $streak,
            'notifications' => $notifications,
            'activitiesThisMonth' => $activitiesThisMonth,
            'activityGrowth' => $activityGrowth,
            'currentCohort' => $currentCohort,
            'stats' => $cohortStats,
            'currentProgram' => $currentProgram,
            'programEnrollment' => $programEnrollment,
            'internshipProfile' => $user->internshipProfile,
            'unpaidFeesCount' => $unpaidFeesCount,
            'nextUnpaidFee' => $nextUnpaidFee,
            'activeAttendanceSession' => $activeAttendanceSession,
            'attendanceRecord' => $attendanceRecord,
            'pendingTrackEnrollments' => $user->fellowTracks()
                ->with('track')
                ->awaitingReview()
                ->orderByDesc('requested_at')
                ->get(),
        ]);
    }

    /**
     * Get dashboard stats via AJAX for real-time updates.
     */
    public function stats(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $activeTrack = $request->attributes->get('activeTrack') ?? $user->activeTrack();

        return response()->json([
            'score' => $activeTrack?->score ?? 0,
            'tier' => $activeTrack?->tier ?? 'rookie',
            'total_points' => $activeTrack?->total_points_earned ?? 0,
            'pending_activities' => $user->activities()->where('status', 'pending')->count(),
            'upcoming_interviews' => InterviewSession::where('fellow_id', $user->id)->upcoming()->count(),
            'unread_notifications' => Notification::where('user_id', $user->id)->whereNull('read_at')->count(),
        ]);
    }

    /**
     * Display the score breakdown detail page.
     */
    public function scoreBreakdown(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $activeTrack = $request->attributes->get('activeTrack') ?? $user->activeTrack();
        $primaryTrack = $activeTrack; // Alias for view compatibility

        if (!$primaryTrack) {
            return redirect()->route('tracks.select')
                ->with('warning', 'Please select a track first.');
        }

        $breakdown = $this->calculator->getScoreBreakdown($user, $primaryTrack->track);
        
        // Get activities by category for detailed breakdown
        $activitiesByCategory = $user->activities()
            ->where('track_id', $primaryTrack->track_id)
            ->where('status', 'approved')
            ->get()
            ->groupBy(fn($a) => $this->getActivityCategory($a->type));

        // Historical score data for chart (from weekly progress history)
        $scoreHistory = $this->weeklyProgressService->getHistory($user, 20)
            ->map(fn($p) => [
                'date' => $p->week_start?->format('M d, Y') ?? 'N/A',
                'score' => $p->score_snapshot ?? 0,
            ])->toArray();

        return view('dashboard.score-breakdown', [
            'user' => $user,
            'primaryTrack' => $primaryTrack,
            'breakdown' => $breakdown,
            'activitiesByCategory' => $activitiesByCategory,
            'scoreHistory' => $scoreHistory,
        ]);
    }

    /**
     * Display the Track Comparison page.
     * 
     * Side-by-side visual comparison of all enrolled tracks with
     * radar charts, activity timelines, and recommendation insights.
     */
    public function trackComparison(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $fellowTracks = $request->attributes->get('fellowTracks') ?? $user->fellowTracks()
            ->with('track')
            ->orderByDesc('is_primary')
            ->orderByDesc('score')
            ->get();

        if ($fellowTracks->count() < 1) {
            return redirect()->route('tracks.select')
                ->with('info', 'Enroll in at least one track to see comparisons.');
        }

        // Gather per-track activity stats
        $trackStats = [];
        foreach ($fellowTracks as $ft) {
            $trackId = $ft->track_id;

            // Activity counts by status
            $approvedCount = $user->activities()
                ->where('track_id', $trackId)
                ->where('status', 'approved')
                ->count();

            $pendingCount = $user->activities()
                ->where('track_id', $trackId)
                ->where('status', 'pending')
                ->count();

            $totalActivities = $user->activities()
                ->where('track_id', $trackId)
                ->count();

            // Points earned
            $totalPoints = $user->activities()
                ->where('track_id', $trackId)
                ->where('status', 'approved')
                ->sum('points_earned');

            // Interview count
            $interviewCount = \App\Models\InterviewSession::where('fellow_id', $user->id)
                ->where('track_id', $trackId)
                ->count();

            // Monthly trend (last 6 months)
            $monthlyTrend = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $count = $user->activities()
                    ->where('track_id', $trackId)
                    ->where('status', 'approved')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
                $monthlyTrend[] = [
                    'label' => $month->format('M'),
                    'count' => $count,
                ];
            }

            // Last active
            $lastActivity = $user->activities()
                ->where('track_id', $trackId)
                ->latest()
                ->first();

            $trackStats[] = [
                'fellowTrack' => $ft,
                'track' => $ft->track,
                'approvedCount' => $approvedCount,
                'pendingCount' => $pendingCount,
                'totalActivities' => $totalActivities,
                'totalPoints' => $totalPoints,
                'interviewCount' => $interviewCount,
                'monthlyTrend' => $monthlyTrend,
                'lastActivity' => $lastActivity,
                'categories' => [
                    'technical' => $ft->technical_score ?? 0,
                    'interview' => $ft->interview_score ?? 0,
                    'portfolio' => $ft->portfolio_score ?? 0,
                    'collaboration' => $ft->collaboration_score ?? 0,
                    'learning' => $ft->learning_score ?? 0,
                ],
            ];
        }

        $meta = $request->attributes->get('trackSwitcherMeta') ?? [];

        return view('dashboard.track-comparison', [
            'user' => $user,
            'fellowTracks' => $fellowTracks,
            'trackStats' => $trackStats,
            'meta' => $meta,
        ]);
    }

    /**
     * Map activity type to career capital category.
     * 
     * Uses the CareerCapitalCategory enum values:
     * technical, interview, portfolio, collaboration, learning
     */
    protected function getActivityCategory($type): string
    {
        return match ($type->value ?? $type) {
            'project', 'competition', 'hackathon' => 'technical',
            'mock_interview', 'coding_challenge', 'system_design' => 'interview',
            'content_creation', 'publication', 'case_study' => 'portfolio',
            'networking', 'mentorship', 'code_review', 'pair_programming' => 'collaboration',
            'learning', 'certification', 'workshop', 'course' => 'learning',
            default => 'technical', // Default to technical
        };
    }
}
