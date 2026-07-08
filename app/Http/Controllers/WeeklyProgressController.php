<?php

namespace App\Http\Controllers;

use App\Models\WeeklyProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Weekly Progress Controller
 * 
 * Handles weekly check-ins and progress tracking for fellows.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class WeeklyProgressController extends Controller
{
    /**
     * Display the weekly progress overview.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get current week's progress
        $currentWeek = WeeklyProgress::where('fellow_id', $user->id)
            ->where('week_start', now()->startOfWeek())
            ->first();
        
        // Get progress history
        $history = WeeklyProgress::where('fellow_id', $user->id)
            ->orderByDesc('week_start')
            ->limit(12)
            ->get();
        
        // Calculate streak
        $streak = $this->calculateStreak($user->id);
        
        // Get stats - count completed pillars
        $totalActivities = $history->reduce(function ($carry, $week) {
            $count = 0;
            if ($week->build_completed) $count++;
            if ($week->brand_completed) $count++;
            if ($week->interview_completed) $count++;
            if ($week->collaborate_completed) $count++;
            return $carry + $count;
        }, 0);
        
        $stats = [
            'total_weeks' => WeeklyProgress::where('fellow_id', $user->id)->count(),
            'current_streak' => $streak,
            'best_week_score' => (int)(WeeklyProgress::where('fellow_id', $user->id)->max('total_points') ?? 0),
            'total_activities' => $totalActivities,
        ];
        
        return view('weekly-progress.index', [
            'currentWeek' => $currentWeek,
            'history' => $history,
            'stats' => $stats,
            'hasSubmittedThisWeek' => $currentWeek !== null,
        ]);
    }

    /**
     * Show the weekly check-in form.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        
        // Check if already submitted this week
        $existing = WeeklyProgress::where('fellow_id', $user->id)
            ->where('week_start', now()->startOfWeek())
            ->first();
        
        if ($existing) {
            return redirect()->route('weekly-progress.index')
                ->with('info', 'You have already submitted your weekly check-in.');
        }
        
        // Get current week info
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $weekNumber = now()->weekOfYear;
        $year = now()->year;
        
        // Get active track from global session context
        $primaryTrack = $request->attributes->get('activeTrack') ?? $user->activeTrack();
        
        return view('weekly-progress.create', [
            'primaryTrack' => $primaryTrack,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'weekNumber' => $weekNumber,
            'year' => $year,
        ]);
    }

    /**
     * Store a new weekly check-in.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        // Check if already submitted this week
        $existing = WeeklyProgress::where('fellow_id', $user->id)
            ->where('week_start', now()->startOfWeek())
            ->first();
        
        if ($existing) {
            return redirect()->route('weekly-progress.index')
                ->with('info', 'You have already submitted your weekly check-in.');
        }
        
        $validated = $request->validate([
            'build_completed' => 'nullable|in:0,1',
            'brand_completed' => 'nullable|in:0,1',
            'interview_completed' => 'nullable|in:0,1',
            'collaborate_completed' => 'nullable|in:0,1',
        ]);
        
        // Get active track from global session context
        $primaryTrack = $request->attributes->get('activeTrack') ?? $user->activeTrack();
        
        $buildCompleted = ($validated['build_completed'] ?? '0') == '1';
        $brandCompleted = ($validated['brand_completed'] ?? '0') == '1';
        $interviewCompleted = ($validated['interview_completed'] ?? '0') == '1';
        $collaborateCompleted = ($validated['collaborate_completed'] ?? '0') == '1';
        
        $allPillarsCompleted = $buildCompleted && $brandCompleted && $interviewCompleted && $collaborateCompleted;
        
        // Calculate points (10 per pillar completed)
        $totalPoints = 0;
        if ($buildCompleted) $totalPoints += 10;
        if ($brandCompleted) $totalPoints += 10;
        if ($interviewCompleted) $totalPoints += 10;
        if ($collaborateCompleted) $totalPoints += 10;
        
        WeeklyProgress::create([
            'fellow_id' => $user->id,
            'track_id' => $primaryTrack?->track_id ?? null,
            'week_start' => now()->startOfWeek(),
            'week_end' => now()->endOfWeek(),
            'week_number' => now()->weekOfYear,
            'year' => now()->year,
            'build_completed' => $buildCompleted,
            'brand_completed' => $brandCompleted,
            'interview_completed' => $interviewCompleted,
            'collaborate_completed' => $collaborateCompleted,
            'build_completed_at' => $buildCompleted ? now() : null,
            'brand_completed_at' => $brandCompleted ? now() : null,
            'interview_completed_at' => $interviewCompleted ? now() : null,
            'collaborate_completed_at' => $collaborateCompleted ? now() : null,
            'build_points' => $buildCompleted ? 10 : 0,
            'brand_points' => $brandCompleted ? 10 : 0,
            'interview_points' => $interviewCompleted ? 10 : 0,
            'collaborate_points' => $collaborateCompleted ? 10 : 0,
            'total_points' => $totalPoints,
            'all_pillars_completed' => $allPillarsCompleted,
            'score_frozen' => false,
        ]);
        
        return redirect()->route('weekly-progress.index')
            ->with('success', 'Weekly check-in submitted successfully!');
    }

    /**
     * Calculate the user's check-in streak.
     */
    private function calculateStreak(int $userId): int
    {
        $streak = 0;
        $weekStart = now()->startOfWeek();
        
        while (true) {
            $exists = WeeklyProgress::where('fellow_id', $userId)
                ->where('week_start', $weekStart)
                ->exists();
            
            if (!$exists) {
                break;
            }
            
            $streak++;
            $weekStart = $weekStart->subWeek();
            
            // Limit to prevent infinite loops
            if ($streak > 52) {
                break;
            }
        }
        
        return $streak;
    }
}
