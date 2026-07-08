<?php

namespace App\Services;

use App\Enums\CareerCapitalCategory;
use App\Enums\Tier;
use App\Models\Activity;
use App\Models\AdminSetting;
use App\Models\FellowTrack;
use App\Models\InterviewSession;
use App\Models\Track;
use App\Models\User;
use App\Models\WeeklyProgress;
use Illuminate\Support\Facades\DB;

/**
 * Career Capital Calculator Service
 * 
 * The brain of the IKS platform. Calculates and updates Career Capital scores
 * based on activities, interviews, and the 4-pillar weekly accountability system.
 * 
 * Scoring Formula:
 * Career Capital = Σ (Category Score × Category Weight)
 * 
 * Categories (default weights):
 * - Technical Skills: 25%
 * - Interview Performance: 25%
 * - Portfolio Quality: 20%
 * - Collaboration: 15%
 * - Continuous Learning: 15%
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class CareerCapitalCalculator
{
    /**
     * Maximum possible score.
     */
    public const MAX_SCORE = 100.0;

    /**
     * Minimum possible score.
     */
    public const MIN_SCORE = 0.0;

    /**
     * Calculate the complete Career Capital score for a fellow in a track.
     */
    public function calculateScore(User $fellow, Track $track): float
    {
        $fellowTrack = $fellow->fellowTracks()
            ->where('track_id', $track->id)
            ->first();

        if (!$fellowTrack) {
            return 0.0;
        }

        // Get rubric weights from track or use defaults
        $rubric = $track->scoring_rubric ?? CareerCapitalCategory::defaultRubric();

        // Calculate each category score
        $technicalScore = $this->calculateTechnicalScore($fellow, $track);
        $interviewScore = $this->calculateInterviewScore($fellow, $track);
        $portfolioScore = $this->calculatePortfolioScore($fellow, $track);
        $collaborationScore = $this->calculateCollaborationScore($fellow, $track);
        $learningScore = $this->calculateLearningScore($fellow, $track);

        // Apply weights
        $weightedScore = 
            ($technicalScore * ($rubric['technical']['weight'] ?? 25) / 100) +
            ($interviewScore * ($rubric['interview']['weight'] ?? 25) / 100) +
            ($portfolioScore * ($rubric['portfolio']['weight'] ?? 20) / 100) +
            ($collaborationScore * ($rubric['collaboration']['weight'] ?? 15) / 100) +
            ($learningScore * ($rubric['learning']['weight'] ?? 15) / 100);

        // Apply any decay if weekly progress is incomplete
        $weightedScore = $this->applyDecay($fellow, $track, $weightedScore);

        // Clamp score between 0 and 100
        return round(max(self::MIN_SCORE, min(self::MAX_SCORE, $weightedScore)), 2);
    }

    /**
     * Calculate technical skills score based on projects and contributions.
     */
    public function calculateTechnicalScore(User $fellow, Track $track): float
    {
        $activities = $fellow->activities()
            ->where('track_id', $track->id)
            ->approved()
            ->whereIn('type', ['project', 'open_source', 'certification'])
            ->get();

        if ($activities->isEmpty()) {
            return 0.0;
        }

        $totalPoints = $activities->sum('points_earned');
        $maxExpectedPoints = 500; // Baseline for 100% technical

        // Scale the points to a percentage
        $score = min(100, ($totalPoints / $maxExpectedPoints) * 100);

        // Bonus for variety in activity types
        $typeCount = $activities->pluck('type')->unique()->count();
        if ($typeCount >= 3) {
            $score = min(100, $score * 1.1); // 10% bonus for diversity
        }

        return round($score, 2);
    }

    /**
     * Calculate interview performance score.
     */
    public function calculateInterviewScore(User $fellow, Track $track): float
    {
        $interviews = $fellow->interviewSessions()
            ->where('track_id', $track->id)
            ->completed()
            ->orderByDesc('completed_at')
            ->limit(10) // Use last 10 interviews
            ->get();

        if ($interviews->isEmpty()) {
            return 0.0;
        }

        // Weighted average: recent interviews matter more
        $weightedSum = 0;
        $weightTotal = 0;
        $weight = 1.0;

        foreach ($interviews as $interview) {
            $weightedSum += $interview->overall_score * $weight;
            $weightTotal += $weight;
            $weight *= 0.9; // Each older interview has 10% less weight
        }

        $averageScore = $weightTotal > 0 ? $weightedSum / $weightTotal : 0;

        // Bonus for interview consistency (low variance)
        $variance = $this->calculateVariance($interviews->pluck('overall_score')->toArray());
        if ($variance < 10 && $interviews->count() >= 3) {
            $averageScore = min(100, $averageScore * 1.05); // 5% consistency bonus
        }

        return round($averageScore, 2);
    }

    /**
     * Calculate portfolio quality score.
     */
    public function calculatePortfolioScore(User $fellow, Track $track): float
    {
        // Portfolio items: blog posts, projects with demos, open source
        $portfolioActivities = $fellow->activities()
            ->where('track_id', $track->id)
            ->approved()
            ->whereIn('type', ['project', 'blog_post', 'open_source'])
            ->get();

        if ($portfolioActivities->isEmpty()) {
            return 0.0;
        }

        $score = 0;

        // Projects with proof links score higher
        $projectsWithProof = $portfolioActivities
            ->where('type', 'project')
            ->filter(fn ($a) => !empty($a->proof_url));
        $score += min(40, $projectsWithProof->count() * 10);

        // Blog posts
        $blogPosts = $portfolioActivities->where('type', 'blog_post');
        $score += min(30, $blogPosts->count() * 5);

        // Open source contributions
        $openSource = $portfolioActivities->where('type', 'open_source');
        $score += min(30, $openSource->count() * 6);

        // Quality bonus based on average points earned
        $avgPoints = $portfolioActivities->avg('points_earned') ?? 0;
        if ($avgPoints > 15) {
            $score = min(100, $score * 1.1);
        }

        return round(min(100, $score), 2);
    }

    /**
     * Calculate collaboration score.
     */
    public function calculateCollaborationScore(User $fellow, Track $track): float
    {
        $collaborationActivities = $fellow->activities()
            ->where('track_id', $track->id)
            ->approved()
            ->whereIn('type', ['mentoring', 'peer_review', 'workshop'])
            ->get();

        if ($collaborationActivities->isEmpty()) {
            return 0.0;
        }

        $score = 0;

        // Mentoring is highly valued
        $mentoring = $collaborationActivities->where('type', 'mentoring');
        $score += min(50, $mentoring->count() * 15);

        // Peer reviews
        $peerReviews = $collaborationActivities->where('type', 'peer_review');
        $score += min(25, $peerReviews->count() * 5);

        // Workshops (given or attended)
        $workshops = $collaborationActivities->where('type', 'workshop');
        $score += min(25, $workshops->count() * 8);

        return round(min(100, $score), 2);
    }

    /**
     * Calculate continuous learning score.
     */
    public function calculateLearningScore(User $fellow, Track $track): float
    {
        $learningActivities = $fellow->activities()
            ->where('track_id', $track->id)
            ->approved()
            ->whereIn('type', ['certification', 'workshop', 'course'])
            ->get();

        // Also consider recent activity (learning never stops)
        $recentActivities = $fellow->activities()
            ->where('track_id', $track->id)
            ->approved()
            ->where('reviewed_at', '>=', now()->subDays(30))
            ->count();

        $score = 0;

        // Certifications are valuable
        $certifications = $learningActivities->where('type', 'certification');
        $score += min(50, $certifications->count() * 20);

        // Courses and workshops
        $coursesAndWorkshops = $learningActivities->whereIn('type', ['course', 'workshop']);
        $score += min(30, $coursesAndWorkshops->count() * 10);

        // Bonus for consistent recent activity
        if ($recentActivities >= 4) {
            $score = min(100, $score + 20);
        } elseif ($recentActivities >= 2) {
            $score = min(100, $score + 10);
        }

        return round(min(100, $score), 2);
    }

    /**
     * Apply score decay for inactive fellows or incomplete weekly progress.
     */
    protected function applyDecay(User $fellow, Track $track, float $score): float
    {
        if (!AdminSetting::get('score_decay_enabled', true)) {
            return $score;
        }

        // Check weekly progress completion
        $currentWeekProgress = WeeklyProgress::where('fellow_id', $fellow->id)
            ->where('track_id', $track->id)
            ->currentWeek()
            ->first();

        if ($currentWeekProgress && $currentWeekProgress->score_frozen) {
            $decayPercentage = AdminSetting::get('score_decay_weekly_percentage', 2);
            $score = $score * (1 - ($decayPercentage / 100));
        }

        // Check for prolonged inactivity
        $lastActivity = $fellow->activities()
            ->where('track_id', $track->id)
            ->approved()
            ->orderByDesc('reviewed_at')
            ->first();

        if ($lastActivity) {
            $weeksSinceActivity = $lastActivity->reviewed_at->diffInWeeks(now());
            $maxWeeksInactive = AdminSetting::get('score_decay_max_weeks_inactive', 4);
            
            if ($weeksSinceActivity > $maxWeeksInactive) {
                $additionalDecay = min(50, ($weeksSinceActivity - $maxWeeksInactive) * 5);
                $score = $score * (1 - ($additionalDecay / 100));
            }
        }

        return max(self::MIN_SCORE, $score);
    }

    /**
     * Update and save the Career Capital score for a fellow track.
     */
    public function updateScore(User $fellow, Track $track): FellowTrack
    {
        return DB::transaction(function () use ($fellow, $track) {
            $fellowTrack = $fellow->fellowTracks()
                ->where('track_id', $track->id)
                ->lockForUpdate()
                ->first();

            if (!$fellowTrack) {
                throw new \Exception("Fellow is not enrolled in this track.");
            }

            $previousScore = $fellowTrack->score;
            $previousTier = Tier::tryFrom($fellowTrack->tier) ?? Tier::ROOKIE;

            // Calculate new scores
            $newScore = $this->calculateScore($fellow, $track);
            $newTier = Tier::fromScore($newScore);

            // Calculate category scores
            $technicalScore = $this->calculateTechnicalScore($fellow, $track);
            $interviewScore = $this->calculateInterviewScore($fellow, $track);
            $portfolioScore = $this->calculatePortfolioScore($fellow, $track);
            $collaborationScore = $this->calculateCollaborationScore($fellow, $track);
            $learningScore = $this->calculateLearningScore($fellow, $track);

            // Update fellow track
            $fellowTrack->update([
                'score' => $newScore,
                'tier' => $newTier->value,
                'technical_score' => $technicalScore,
                'interview_score' => $interviewScore,
                'portfolio_score' => $portfolioScore,
                'collaboration_score' => $collaborationScore,
                'learning_score' => $learningScore,
                'last_active_at' => now(),
            ]);

            // Check for tier change and notify
            if ($previousTier !== $newTier) {
                $this->handleTierChange($fellow, $track, $previousTier, $newTier, $newScore);
            }

            return $fellowTrack->fresh();
        });
    }

    /**
     * Handle tier promotion/demotion.
     */
    protected function handleTierChange(
        User $fellow,
        Track $track,
        Tier $previousTier,
        Tier $newTier,
        float $newScore
    ): void {
        // Tier promotion
        if ($newTier->value > $previousTier->value) {
            \App\Models\Notification::sendTierPromotion(
                $fellow,
                $previousTier->label(),
                $newTier->label(),
                $newScore
            );
        }

        // Log tier change in audit
        // This will be handled by the AuditService
    }

    /**
     * Recalculate scores for all fellows in a track.
     */
    public function recalculateAllForTrack(Track $track): int
    {
        $count = 0;

        $track->fellows->each(function ($fellow) use ($track, &$count) {
            $this->updateScore($fellow, $track);
            $count++;
        });

        return $count;
    }

    /**
     * Get score breakdown for display.
     */
    public function getScoreBreakdown(User $fellow, Track $track): array
    {
        $rubric = $track->scoring_rubric ?? CareerCapitalCategory::defaultRubric();

        return [
            'total' => $this->calculateScore($fellow, $track),
            'categories' => [
                'technical' => [
                    'score' => $this->calculateTechnicalScore($fellow, $track),
                    'weight' => $rubric['technical']['weight'] ?? 25,
                    'label' => 'Technical Skills',
                    'icon' => 'code',
                    'color' => 'blue',
                ],
                'interview' => [
                    'score' => $this->calculateInterviewScore($fellow, $track),
                    'weight' => $rubric['interview']['weight'] ?? 25,
                    'label' => 'Interview Performance',
                    'icon' => 'video-camera',
                    'color' => 'purple',
                ],
                'portfolio' => [
                    'score' => $this->calculatePortfolioScore($fellow, $track),
                    'weight' => $rubric['portfolio']['weight'] ?? 20,
                    'label' => 'Portfolio Quality',
                    'icon' => 'briefcase',
                    'color' => 'green',
                ],
                'collaboration' => [
                    'score' => $this->calculateCollaborationScore($fellow, $track),
                    'weight' => $rubric['collaboration']['weight'] ?? 15,
                    'label' => 'Collaboration',
                    'icon' => 'users',
                    'color' => 'teal',
                ],
                'learning' => [
                    'score' => $this->calculateLearningScore($fellow, $track),
                    'weight' => $rubric['learning']['weight'] ?? 15,
                    'label' => 'Continuous Learning',
                    'icon' => 'academic-cap',
                    'color' => 'yellow',
                ],
            ],
        ];
    }

    /**
     * Calculate variance for a set of scores.
     */
    protected function calculateVariance(array $scores): float
    {
        if (count($scores) < 2) {
            return 0;
        }

        $mean = array_sum($scores) / count($scores);
        $squaredDiffs = array_map(fn ($score) => pow($score - $mean, 2), $scores);
        
        return array_sum($squaredDiffs) / count($squaredDiffs);
    }

    /**
     * Get points needed for next tier.
     */
    public function getPointsToNextTier(User $fellow, Track $track): array
    {
        $fellowTrack = $fellow->fellowTracks()
            ->where('track_id', $track->id)
            ->first();

        if (!$fellowTrack) {
            return ['tier' => null, 'points_needed' => 0];
        }

        $currentScore = $fellowTrack->score;
        $currentTier = Tier::from($fellowTrack->tier);
        $thresholds = AdminSetting::getTierThresholds();

        // Find next tier threshold
        $nextTier = match($currentTier) {
            Tier::ROOKIE => Tier::INTERN,
            Tier::INTERN => Tier::PROFESSIONAL,
            Tier::PROFESSIONAL => Tier::ELITE,
            Tier::ELITE => null,
        };

        if (!$nextTier) {
            return ['tier' => null, 'points_needed' => 0, 'message' => 'Maximum tier reached!'];
        }

        $nextThreshold = $thresholds[$nextTier->value] ?? $nextTier->defaultRange()['min'];
        $pointsNeeded = max(0, $nextThreshold - $currentScore);

        return [
            'tier' => $nextTier,
            'tier_label' => $nextTier->label(),
            'points_needed' => round($pointsNeeded, 2),
            'current_score' => $currentScore,
            'threshold' => $nextThreshold,
            'progress_percentage' => min(100, ($currentScore / $nextThreshold) * 100),
        ];
    }
}
