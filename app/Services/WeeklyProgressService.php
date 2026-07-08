<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\User;
use App\Models\WeeklyProgress;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Weekly Progress Service
 * 
 * Handles the "4 Pillars" weekly accountability system.
 * Fellows must complete all 4 pillars weekly to maintain Career Capital.
 * 
 * Pillars:
 * 1. BUILD - Submit project/code contribution
 * 2. BRAND - Publish content (blog, LinkedIn, Twitter)
 * 3. INTERVIEW - Complete mock interview session
 * 4. COLLABORATE - Code reviews, mentoring, peer sessions
 * 
 * @author IKS Engineering Team
 * @version 2.0
 */
class WeeklyProgressService
{
    public function __construct(
        protected CareerCapitalCalculator $calculator
    ) {}

    /**
     * Submit weekly progress.
     */
    public function submit(User $fellow, array $data): WeeklyProgress
    {
        $weekStart = $this->getCurrentWeekStart();

        // Check if already submitted this week
        $existing = WeeklyProgress::where('fellow_id', $fellow->id)
            ->where('week_start', $weekStart)
            ->first();

        if ($existing) {
            return $this->update($existing, $data);
        }

        $buildCompleted = !empty($data['build_completed']);
        $brandCompleted = !empty($data['brand_completed']);
        $interviewCompleted = !empty($data['interview_completed']);
        $collaborateCompleted = !empty($data['collaborate_completed']);

        $progress = WeeklyProgress::create([
            'fellow_id' => $fellow->id,
            'track_id' => $fellow->primaryTrack?->track_id,
            'week_start' => $weekStart,
            'week_end' => $weekStart->copy()->endOfWeek(),
            'year' => $weekStart->year,
            'week_number' => $weekStart->weekOfYear,
            'build_completed' => $buildCompleted,
            'brand_completed' => $brandCompleted,
            'interview_completed' => $interviewCompleted,
            'collaborate_completed' => $collaborateCompleted,
            'build_activity_id' => $data['build_activity_id'] ?? null,
            'brand_activity_id' => $data['brand_activity_id'] ?? null,
            'interview_activity_id' => $data['interview_activity_id'] ?? null,
            'collaborate_activity_id' => $data['collaborate_activity_id'] ?? null,
            'build_points' => $buildCompleted ? 25 : 0,
            'brand_points' => $brandCompleted ? 25 : 0,
            'interview_points' => $interviewCompleted ? 25 : 0,
            'collaborate_points' => $collaborateCompleted ? 25 : 0,
            'total_points' => ($buildCompleted + $brandCompleted + $interviewCompleted + $collaborateCompleted) * 25,
            'all_pillars_completed' => $buildCompleted && $brandCompleted && $interviewCompleted && $collaborateCompleted,
            'notes' => $data['notes'] ?? null,
            'submitted_at' => now(),
        ]);

        // Log action
        AuditLog::create([
            'fellow_id' => $fellow->id,
            'action' => 'weekly_progress_submitted',
            'auditable_type' => WeeklyProgress::class,
            'auditable_id' => $progress->id,
            'justification' => 'Weekly progress submitted',
            'new_values' => [
                'week_start' => $weekStart->toDateString(),
                'pillars_completed' => $progress->completed_pillars_count,
            ],
        ]);

        // Check if complete and notify
        if ($progress->all_pillars_completed) {
            Notification::create([
                'user_id' => $fellow->id,
                'type' => 'weekly_progress_complete',
                'title' => 'Weekly Pillars Complete! 🎯',
                'message' => 'Congratulations! You\'ve completed all 4 pillars this week.',
                'data' => ['progress_id' => $progress->id],
            ]);
        }

        return $progress;
    }

    /**
     * Update existing weekly progress.
     */
    public function update(WeeklyProgress $progress, array $data): WeeklyProgress
    {
        $wasComplete = $progress->all_pillars_completed;

        $buildCompleted = $data['build_completed'] ?? $progress->build_completed;
        $brandCompleted = $data['brand_completed'] ?? $progress->brand_completed;
        $interviewCompleted = $data['interview_completed'] ?? $progress->interview_completed;
        $collaborateCompleted = $data['collaborate_completed'] ?? $progress->collaborate_completed;

        $progress->update([
            'build_completed' => $buildCompleted,
            'brand_completed' => $brandCompleted,
            'interview_completed' => $interviewCompleted,
            'collaborate_completed' => $collaborateCompleted,
            'build_activity_id' => $data['build_activity_id'] ?? $progress->build_activity_id,
            'brand_activity_id' => $data['brand_activity_id'] ?? $progress->brand_activity_id,
            'interview_activity_id' => $data['interview_activity_id'] ?? $progress->interview_activity_id,
            'collaborate_activity_id' => $data['collaborate_activity_id'] ?? $progress->collaborate_activity_id,
            'build_points' => $buildCompleted ? 25 : 0,
            'brand_points' => $brandCompleted ? 25 : 0,
            'interview_points' => $interviewCompleted ? 25 : 0,
            'collaborate_points' => $collaborateCompleted ? 25 : 0,
            'total_points' => ($buildCompleted + $brandCompleted + $interviewCompleted + $collaborateCompleted) * 25,
            'all_pillars_completed' => $buildCompleted && $brandCompleted && $interviewCompleted && $collaborateCompleted,
            'notes' => $data['notes'] ?? $progress->notes,
        ]);

        // Notify if just completed
        if (!$wasComplete && $progress->all_pillars_completed) {
            Notification::create([
                'user_id' => $progress->fellow_id,
                'type' => 'weekly_progress_complete',
                'title' => 'Weekly Pillars Complete! 🎯',
                'message' => 'Congratulations! You\'ve completed all 4 pillars this week.',
                'data' => ['progress_id' => $progress->id],
            ]);
        }

        return $progress->fresh();
    }

    /**
     * Get current week progress for a fellow.
     */
    public function getCurrentProgress(User $fellow): ?WeeklyProgress
    {
        return WeeklyProgress::where('fellow_id', $fellow->id)
            ->where('week_start', $this->getCurrentWeekStart())
            ->first();
    }

    /**
     * Get progress history for a fellow.
     */
    public function getHistory(User $fellow, int $weeks = 12): Collection
    {
        return WeeklyProgress::where('fellow_id', $fellow->id)
            ->orderByDesc('week_start')
            ->limit($weeks)
            ->get();
    }

    /**
     * Get completion streak.
     */
    public function getStreak(User $fellow): int
    {
        $history = $this->getHistory($fellow, 52);
        $streak = 0;

        foreach ($history as $progress) {
            if ($progress->all_pillars_completed) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Get missing pillars for current week.
     */
    public function getMissingPillars(User $fellow): array
    {
        $progress = $this->getCurrentProgress($fellow);
        $missing = [];

        if (!$progress || !$progress->build_completed) {
            $missing[] = 'BUILD';
        }
        if (!$progress || !$progress->brand_completed) {
            $missing[] = 'BRAND';
        }
        if (!$progress || !$progress->interview_completed) {
            $missing[] = 'INTERVIEW';
        }
        if (!$progress || !$progress->collaborate_completed) {
            $missing[] = 'COLLABORATE';
        }

        return $missing;
    }

    /**
     * Check and apply weekly accountability penalty.
     */
    public function processWeeklyAccountability(): array
    {
        $results = [
            'processed' => 0,
            'penalized' => 0,
            'completed' => 0,
        ];

        // Get all active fellows
        $fellows = User::role('fellow')
            ->where('is_active', true)
            ->get();

        $lastWeekStart = $this->getLastWeekStart();

        foreach ($fellows as $fellow) {
            $results['processed']++;

            $progress = WeeklyProgress::where('fellow_id', $fellow->id)
                ->where('week_start', $lastWeekStart)
                ->first();

            if (!$progress || !$progress->all_pillars_completed) {
                // Apply penalty
                $this->calculator->applyWeeklyAccountabilityPenalty($fellow);
                $results['penalized']++;

                // Notify fellow
                $missing = $progress ? $this->getMissingPillarsFromProgress($progress) : ['All 4 Pillars'];
                
                Notification::create([
                    'user_id' => $fellow->id,
                    'type' => 'accountability_penalty',
                    'title' => 'Score Frozen - Weekly Pillars Incomplete',
                    'message' => 'Your Career Capital score has been frozen because you didn\'t complete all 4 pillars last week.',
                    'data' => ['missing' => $missing],
                ]);
            } else {
                $results['completed']++;
            }
        }

        return $results;
    }

    /**
     * Get missing pillars from a progress record.
     */
    protected function getMissingPillarsFromProgress(WeeklyProgress $progress): array
    {
        $missing = [];

        if (!$progress->build_completed) $missing[] = 'BUILD';
        if (!$progress->brand_completed) $missing[] = 'BRAND';
        if (!$progress->interview_completed) $missing[] = 'INTERVIEW';
        if (!$progress->collaborate_completed) $missing[] = 'COLLABORATE';

        return $missing;
    }

    /**
     * Send weekly reminders.
     */
    public function sendWeeklyReminders(): int
    {
        $remindersSent = 0;

        $fellows = User::role('fellow')
            ->where('is_active', true)
            ->get();

        foreach ($fellows as $fellow) {
            $missing = $this->getMissingPillars($fellow);

            if (!empty($missing)) {
                Notification::create([
                    'user_id' => $fellow->id,
                    'type' => 'weekly_progress_reminder',
                    'title' => 'Complete Your Weekly Pillars!',
                    'message' => 'Don\'t forget to complete: ' . implode(', ', $missing),
                    'data' => ['missing' => $missing],
                ]);
                $remindersSent++;
            }
        }

        return $remindersSent;
    }

    /**
     * Get completion statistics for admin dashboard.
     */
    public function getStatistics(): array
    {
        $weekStart = $this->getCurrentWeekStart();
        $lastWeekStart = $this->getLastWeekStart();

        $totalFellows = User::role('fellow')->where('is_active', true)->count();

        $thisWeekComplete = WeeklyProgress::where('week_start', $weekStart)
            ->where('all_pillars_completed', true)
            ->count();

        $thisWeekPartial = WeeklyProgress::where('week_start', $weekStart)
            ->where('all_pillars_completed', false)
            ->count();

        $lastWeekComplete = WeeklyProgress::where('week_start', $lastWeekStart)
            ->where('all_pillars_completed', true)
            ->count();

        return [
            'total_fellows' => $totalFellows,
            'this_week' => [
                'completed' => $thisWeekComplete,
                'partial' => $thisWeekPartial,
                'not_started' => max(0, $totalFellows - $thisWeekComplete - $thisWeekPartial),
                'completion_rate' => $totalFellows > 0 
                    ? round(($thisWeekComplete / $totalFellows) * 100, 1)
                    : 0,
            ],
            'last_week' => [
                'completed' => $lastWeekComplete,
                'completion_rate' => $totalFellows > 0 
                    ? round(($lastWeekComplete / $totalFellows) * 100, 1)
                    : 0,
            ],
            'pillar_breakdown' => $this->getPillarBreakdown($weekStart),
            'avg_streak' => $this->getAverageStreak(),
        ];
    }

    /**
     * Get pillar completion breakdown.
     */
    protected function getPillarBreakdown(Carbon $weekStart): array
    {
        $progresses = WeeklyProgress::where('week_start', $weekStart)->get();
        $total = $progresses->count() ?: 1;

        return [
            'build' => round(
                ($progresses->where('build_completed', true)->count() / $total) * 100,
                1
            ),
            'brand' => round(
                ($progresses->where('brand_completed', true)->count() / $total) * 100,
                1
            ),
            'interview' => round(
                ($progresses->where('interview_completed', true)->count() / $total) * 100,
                1
            ),
            'collaborate' => round(
                ($progresses->where('collaborate_completed', true)->count() / $total) * 100,
                1
            ),
        ];
    }

    /**
     * Get average streak across all fellows.
     */
    protected function getAverageStreak(): float
    {
        $fellows = User::role('fellow')->where('is_active', true)->get();
        
        if ($fellows->isEmpty()) {
            return 0;
        }

        $totalStreak = 0;
        foreach ($fellows as $fellow) {
            $totalStreak += $this->getStreak($fellow);
        }

        return round($totalStreak / $fellows->count(), 1);
    }

    /**
     * Get current week start date.
     */
    protected function getCurrentWeekStart(): Carbon
    {
        return now()->startOfWeek();
    }

    /**
     * Get last week start date.
     */
    protected function getLastWeekStart(): Carbon
    {
        return now()->subWeek()->startOfWeek();
    }

    /**
     * Get leaderboard of fellows with longest streaks.
     */
    public function getStreakLeaderboard(int $limit = 10): array
    {
        $fellows = User::role('fellow')
            ->where('is_active', true)
            ->with('primaryTrack.track')
            ->get();

        $leaderboard = [];

        foreach ($fellows as $fellow) {
            $streak = $this->getStreak($fellow);
            
            if ($streak > 0) {
                $leaderboard[] = [
                    'fellow' => $fellow,
                    'streak' => $streak,
                    'track' => $fellow->primaryTrack?->track?->name,
                ];
            }
        }

        // Sort by streak descending
        usort($leaderboard, fn($a, $b) => $b['streak'] <=> $a['streak']);

        return array_slice($leaderboard, 0, $limit);
    }
}
