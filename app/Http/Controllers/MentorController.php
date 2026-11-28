<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InterviewSession;
use App\Models\Notification;
use App\Models\Activity;
use App\Enums\InterviewType;
use App\Enums\InterviewMode;
use App\Services\CareerCapitalCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MentorController extends Controller
{
    public function __construct(
        protected CareerCapitalCalculator $calculator
    ) {}
    /**
     * Display the mentor dashboard.
     */
    public function dashboard()
    {
        $mentor = Auth::user();
        
        // Get upcoming interviews assigned to this mentor
        $upcomingInterviews = InterviewSession::where('interviewer_id', $mentor->id)
            ->where('scheduled_at', '>=', now())
            ->where('status', 'scheduled')
            ->with(['fellow', 'track'])
            ->orderBy('scheduled_at', 'asc')
            ->take(5)
            ->get();
        
        // Get interviews pending review (completed but not scored)
        $pendingReviews = InterviewSession::where('interviewer_id', $mentor->id)
            ->where('status', 'completed')
            ->whereNull('interviewer_notes')
            ->with(['fellow', 'track'])
            ->orderBy('completed_at', 'desc')
            ->take(5)
            ->get();
        
        // Get recent completed interviews
        $recentInterviews = InterviewSession::where('interviewer_id', $mentor->id)
            ->where('status', 'completed')
            ->whereNotNull('interviewer_notes')
            ->with(['fellow', 'track'])
            ->orderBy('completed_at', 'desc')
            ->take(10)
            ->get();
        
        // Stats
        $stats = [
            'total_interviews' => InterviewSession::where('interviewer_id', $mentor->id)->count(),
            'completed_this_month' => InterviewSession::where('interviewer_id', $mentor->id)
                ->where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->count(),
            'upcoming_count' => InterviewSession::where('interviewer_id', $mentor->id)
                ->where('scheduled_at', '>=', now())
                ->where('status', 'scheduled')
                ->count(),
            'pending_reviews' => InterviewSession::where('interviewer_id', $mentor->id)
                ->where('status', 'completed')
                ->whereNull('interviewer_notes')
                ->count(),
            'average_rating' => InterviewSession::where('interviewer_id', $mentor->id)
                ->whereNotNull('interviewer_rating')
                ->avg('interviewer_rating') ?? 0,
        ];
        
        // Get mentees (fellows assigned to this mentor)
        $mentees = User::with('primaryTrack.track')
            ->whereHas('interviewSessions', function($query) use ($mentor) {
                $query->where('interviewer_id', $mentor->id);
            })->take(10)->get();
        
        return view('mentor.dashboard', compact(
            'upcomingInterviews',
            'pendingReviews',
            'recentInterviews',
            'stats',
            'mentees'
        ));
    }

    /**
     * Display list of interviews for the mentor.
     */
    public function interviews(Request $request)
    {
        $mentor = Auth::user();
        
        $query = InterviewSession::where('interviewer_id', $mentor->id)
            ->with(['fellow', 'track']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('scheduled_at', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->where('scheduled_at', '<=', $request->to_date);
        }
        
        // Sort
        $sortBy = $request->input('sort', 'scheduled_at');
        $sortDir = $request->input('direction', 'desc');
        $query->orderBy($sortBy, $sortDir);
        
        $interviews = $query->paginate(15);
        
        // Get interview types for filter dropdown
        $interviewTypes = InterviewType::cases();
        
        return view('mentor.interviews.index', compact('interviews', 'interviewTypes'));
    }

    /**
     * Display the interview review page.
     */
    public function reviewInterview(InterviewSession $interview)
    {
        // Ensure the mentor is assigned to this interview
        if ($interview->interviewer_id !== Auth::id()) {
            abort(403, 'You are not assigned to this interview.');
        }
        
        // Load relationships
        $interview->load(['fellow', 'track']);
        
        // Get fellow's previous interviews for context
        $previousInterviews = InterviewSession::with(['track', 'interviewer'])
            ->where('fellow_id', $interview->fellow_id)
            ->where('id', '!=', $interview->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->take(5)
            ->get();
        
        // Get fellow's recent activities
        $recentActivities = Activity::with('track')
            ->where('fellow_id', $interview->fellow_id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return view('mentor.interviews.review', compact(
            'interview',
            'previousInterviews',
            'recentActivities'
        ));
    }

    /**
     * The mentor records four 1-10 scores and feedback (or a no-show). The 0-100 score is
     * the average x 10; then Career Capital and the curriculum are updated.
     */
    public function completeInterview(Request $request, InterviewSession $interview)
    {
        if ($interview->interviewer_id !== Auth::id()) {
            abort(403, 'You are not assigned to this interview.');
        }
        $service = app(\App\Services\InterviewService::class);

        if ($request->boolean('no_show')) {
            $request->validate(['no_show_notes' => 'nullable|string|max:500']);
            try {
                $service->markNoShow($interview, $request->input('no_show_notes'));
            } catch (\RuntimeException $e) {
                return redirect()->route('mentor.interviews.review', $interview)->with('error', $e->getMessage());
            }

            return redirect()->route('mentor.interviews')->with('info', 'Interview marked as no-show.');
        }

        $validated = $request->validate([
            'technical_score' => 'required|integer|min:1|max:10',
            'communication_score' => 'required|integer|min:1|max:10',
            'problem_solving_score' => 'required|integer|min:1|max:10',
            'overall_score' => 'required|integer|min:1|max:10',
            'feedback' => 'required|string|min:50|max:5000',
            'strengths' => 'nullable|string|max:3000',
            'areas_for_improvement' => 'nullable|string|max:3000',
            'recommendations' => 'nullable|string|max:3000',
            'internal_notes' => 'nullable|string|max:3000',
        ]);

        try {
            $interview = $service->completeHuman($interview, Auth::user(), $validated);
        } catch (\RuntimeException $e) {
            return redirect()->route('mentor.interviews.review', $interview)->with('error', $e->getMessage());
        }

        return redirect()->route('mentor.interviews')
            ->with('success', 'Feedback submitted. Score: ' . rtrim(rtrim(number_format((float) $interview->score, 1), '0'), '.') . '/100.');
    }

    /**
     * Show mentor's availability settings.
     */
    public function availability()
    {
        $mentor = Auth::user();
        
        // Get current availability settings
        $availability = $mentor->mentor_availability ?? [
            'monday' => ['available' => false, 'slots' => []],
            'tuesday' => ['available' => false, 'slots' => []],
            'wednesday' => ['available' => false, 'slots' => []],
            'thursday' => ['available' => false, 'slots' => []],
            'friday' => ['available' => false, 'slots' => []],
            'saturday' => ['available' => false, 'slots' => []],
            'sunday' => ['available' => false, 'slots' => []],
        ];
        
        return view('mentor.availability', compact('availability'));
    }

    /**
     * Update mentor's availability.
     */
    public function updateAvailability(Request $request)
    {
        $mentor = Auth::user();
        
        $validated = $request->validate([
            'availability' => 'required|array',
            'availability.*.available' => 'boolean',
            'availability.*.slots' => 'array',
        ]);
        
        $mentor->update([
            'mentor_availability' => $validated['availability'],
        ]);
        
        return redirect()->route('mentor.availability')
            ->with('success', 'Availability updated successfully!');
    }

    /**
     * Show mentor profile/settings.
     */
    public function profile()
    {
        $mentor = Auth::user();
        
        // Get mentor's specializations
        $specializations = $mentor->mentor_specializations ?? [];
        
        // Get mentor's interview types they can conduct
        $interviewTypes = InterviewType::cases();
        
        return view('mentor.profile', compact('mentor', 'specializations', 'interviewTypes'));
    }
}
