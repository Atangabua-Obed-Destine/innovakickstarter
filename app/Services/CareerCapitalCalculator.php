<?php

namespace App\Services;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Enums\CareerCapitalCategory;
use App\Enums\CurriculumStatus;
use App\Enums\Tier;
use App\Models\Activity;
use App\Models\AdminSetting;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\Notification;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\User;
use App\Models\WeeklyProgress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Career Capital Calculator Service
 *
 * Calculates a fellow's Career Capital score (0-100) in a track.
 *
 *   Career Capital = Σ (category score × category weight) / Σ weights
 *
 * Each category score compares the verified points a fellow has earned in
 * that category (approved activities + reviewed curriculum work, weighted by
 * the review's rubric score) with the points the track's curriculum offers
 * in that category:
 *
 *   category score = 100 × (1 − e^(−k × earned / curriculum points))
 *
 * With the default steepness k = 1, completing a category's curriculum at
 * full quality gives 63%, at 75% quality 53%, and twice the curriculum 86%.
 * So finishing the curriculum well lands a fellow in Professional, and
 * Elite takes extra verified work on top. k is admin-configurable.
 *
 * Interview readiness also counts mock interview results (see
 * calculateInterviewScore). Decay for frozen weeks and inactivity is
 * applied to the total.
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
     * Reference points used when a track's curriculum offers fewer in a
     * category (or has no curriculum), so a couple of freestyle activities
     * cannot max out a category. Roughly 7-8 activities at default points
     * (e.g. 8 projects × 25) reach the same 63% as completing a curriculum.
     */
    public const MIN_EXPECTED_POINTS = [
        'technical' => 200,
        'interview' => 100,
        'portfolio' => 150,
        'collaboration' => 100,
        'learning' => 100,
    ];

    /**
     * Turn earned points into a 0-100 category score.
     */
    public function scaleScore(float $earnedPoints, float $expectedPoints): float
    {
        if ($earnedPoints <= 0 || $expectedPoints <= 0) {
            return 0.0;
        }

        $steepness = (float) AdminSetting::get('score_curve_steepness', 1.0);
        if ($steepness <= 0) {
            $steepness = 1.0;
        }

        $score = 100 * (1 - exp(-$steepness * $earnedPoints / $expectedPoints));

        return round(min(self::MAX_SCORE, max(self::MIN_SCORE, $score)), 3);
    }

    /**
     * Points a reviewed curriculum submission contributes: the awarded points
     * scaled by the rubric score (0-100). Work approved without a rubric score
     * gets full credit.
     */
    public function qualityWeightedPoints(float $points, mixed $scoreAwarded): float
    {
        if ($scoreAwarded === null || (float) $scoreAwarded <= 0) {
            return $points;
        }

        return $points * min(100.0, (float) $scoreAwarded) / 100;
    }

    /**
     * Calculate the complete Career Capital score for a fellow in a track.
     */
    public function calculateScore(User $fellow, Track $track): float
    {
        $enrolled = $fellow->fellowTracks()->where('track_id', $track->id)->exists();

        if (!$enrolled) {
            return 0.0;
        }

        return $this->totalFromCategories($fellow, $track, $this->calculateCategoryScores($fellow, $track));
    }

    /**
     * All five category scores, keyed by category value.
     */
    public function calculateCategoryScores(User $fellow, Track $track): array
    {
        return [
            'technical' => $this->calculateTechnicalScore($fellow, $track),
            'interview' => $this->calculateInterviewScore($fellow, $track),
            'portfolio' => $this->calculatePortfolioScore($fellow, $track),
            'collaboration' => $this->calculateCollaborationScore($fellow, $track),
            'learning' => $this->calculateLearningScore($fellow, $track),
        ];
    }

    /**
     * Category weights (percent) for a track. A track rubric keyed by the five
     * categories wins; otherwise the weights from the admin settings page.
     */
    public function categoryWeights(Track $track): array
    {
        $rubric = is_array($track->scoring_rubric) ? $track->scoring_rubric : [];
        $settings = AdminSetting::getCategoryWeights();
        $weights = [];

        foreach (CareerCapitalCategory::cases() as $category) {
            $key = $category->value;
            $value = $rubric[$key] ?? null;

            if (is_array($value)) {
                $value = $value['weight'] ?? null;
            }

            $weights[$key] = is_numeric($value)
                ? (float) $value
                : (float) ($settings[$key] ?? $category->defaultWeight());
        }

        return $weights;
    }

    /**
     * Weighted total of the category scores, with decay applied.
     */
    protected function totalFromCategories(User $fellow, Track $track, array $categories): float
    {
        $weights = $this->categoryWeights($track);
        $totalWeight = array_sum($weights);

        if ($totalWeight <= 0) {
            return 0.0;
        }

        $weighted = 0.0;
        foreach ($weights as $key => $weight) {
            $weighted += ($categories[$key] ?? 0) * $weight;
        }

        $score = $this->applyDecay($fellow, $track, $weighted / $totalWeight);

        return round(max(self::MIN_SCORE, min(self::MAX_SCORE, $score)), 3);
    }

    /**
     * Technical skills: projects, contributions and technical curriculum work.
     */
    public function calculateTechnicalScore(User $fellow, Track $track): float
    {
        $types = ActivityType::getValuesByCategory(CareerCapitalCategory::TECHNICAL);
        $activities = $this->approvedActivities($fellow, $track, $types);

        $earned = $activities->sum('points_earned') + $this->curriculumPoints($fellow, $track, $types);
        $score = $this->scaleScore($earned, $this->expectedPoints($track, $types, 'technical'));

        // Variety bonus
        if ($activities->pluck('type')->unique()->count() >= 3) {
            $score *= 1.05;
        }

        return round(min(self::MAX_SCORE, $score), 3);
    }

    /**
     * Interview readiness.
     *
     * A blend of two sources (score_interview_session_share %, default 50/50):
     *  - mock interview results: recency-weighted average of the last 10 scored
     *    interviews, scaled by volume (full credit from score_interview_full_volume
     *    interviews); practice sessions do not count;
     *  - interview-related work (certifications, research, logged mock interviews,
     *    interview curriculum activities), scaled like other categories.
     */
    public function calculateInterviewScore(User $fellow, Track $track): float
    {
        $sessionScore = $this->mockInterviewScore($fellow, $track);

        $types = ActivityType::getValuesByCategory(CareerCapitalCategory::INTERVIEW);
        $activities = $this->approvedActivities($fellow, $track, $types);
        $earned = $activities->sum('points_earned') + $this->curriculumPoints($fellow, $track, $types);
        $activityScore = $this->scaleScore($earned, $this->expectedPoints($track, $types, 'interview'));

        $share = min(100, max(0, (float) AdminSetting::get('score_interview_session_share', 50))) / 100;
        $score = $share * $sessionScore + (1 - $share) * $activityScore;

        return round(min(self::MAX_SCORE, $score), 3);
    }

    /**
     * Score from completed, scored, non-practice mock interviews (0-100).
     */
    public function mockInterviewScore(User $fellow, Track $track): float
    {
        $interviews = $fellow->interviewSessions()
            ->where('track_id', $track->id)
            ->completed()
            ->whereNotNull('score')
            ->where(fn ($q) => $q->where('is_practice', false)->orWhereNull('is_practice'))
            ->orderByDesc('completed_at')
            ->get(['id', 'score', 'completed_at']);

        if ($interviews->isEmpty()) {
            return 0.0;
        }

        // Recency-weighted average of the last 10: each older interview counts 10% less.
        $recent = $interviews->take(10)->pluck('score')->map(fn ($s) => (float) $s)->values()->all();
        $weightedSum = 0.0;
        $weightTotal = 0.0;
        $weight = 1.0;

        foreach ($recent as $score) {
            $weightedSum += $score * $weight;
            $weightTotal += $weight;
            $weight *= 0.9;
        }

        $average = $weightedSum / $weightTotal;

        // An average means little on a few interviews: full credit needs the configured volume.
        $fullVolume = max(1, (int) AdminSetting::get('score_interview_full_volume', 10));
        $score = $average * min(1.0, $interviews->count() / $fullVolume);

        // Consistency bonus
        if (count($recent) >= 3 && $this->calculateVariance($recent) < 10) {
            $score *= 1.05;
        }

        return round(min(self::MAX_SCORE, $score), 3);
    }

    /**
     * Portfolio quality: deployed projects, case studies, content.
     */
    public function calculatePortfolioScore(User $fellow, Track $track): float
    {
        $types = ActivityType::getValuesByCategory(CareerCapitalCategory::PORTFOLIO);
        $activities = $this->approvedActivities($fellow, $track, $types);

        $earned = $activities->sum('points_earned') + $this->curriculumPoints($fellow, $track, $types);
        $score = $this->scaleScore($earned, $this->expectedPoints($track, $types, 'portfolio'));

        // Projects with proof links
        if ($activities->where('type', 'project')->filter(fn ($a) => !empty($a->proof_url))->isNotEmpty()) {
            $score *= 1.05;
        }

        // Substantial pieces of work
        if (($activities->avg('points_earned') ?? 0) > 15) {
            $score *= 1.05;
        }

        return round(min(self::MAX_SCORE, $score), 3);
    }

    /**
     * Collaboration: peer reviews, pair work, mentoring.
     */
    public function calculateCollaborationScore(User $fellow, Track $track): float
    {
        $types = ActivityType::getValuesByCategory(CareerCapitalCategory::COLLABORATION);
        $activities = $this->approvedActivities($fellow, $track, $types);

        $earned = $activities->sum('points_earned') + $this->curriculumPoints($fellow, $track, $types);
        $score = $this->scaleScore($earned, $this->expectedPoints($track, $types, 'collaboration'));

        // Mentoring is highly valued
        if ($activities->where('type', 'mentoring')->isNotEmpty()) {
            $score *= 1.10;
        }

        return round(min(self::MAX_SCORE, $score), 3);
    }

    /**
     * Continuous learning: courses, certifications, workshops.
     */
    public function calculateLearningScore(User $fellow, Track $track): float
    {
        $types = ActivityType::getValuesByCategory(CareerCapitalCategory::LEARNING);
        $activities = $this->approvedActivities($fellow, $track, $types);

        $earned = $activities->sum('points_earned') + $this->curriculumPoints($fellow, $track, $types);
        $score = $this->scaleScore($earned, $this->expectedPoints($track, $types, 'learning'));

        // Consistent recent activity (any type) in the last 30 days
        $recentActivities = $fellow->activities()
            ->where('track_id', $track->id)
            ->approved()
            ->where('reviewed_at', '>=', now()->subDays(30))
            ->count();

        if ($recentActivities >= 4) {
            $score *= 1.10;
        } elseif ($recentActivities >= 2) {
            $score *= 1.05;
        }

        return round(min(self::MAX_SCORE, $score), 3);
    }

    /**
     * Approved freestyle activities of the given types.
     */
    protected function approvedActivities(User $fellow, Track $track, array $types): Collection
    {
        return $fellow->activities()
            ->where('track_id', $track->id)
            ->approved()
            ->whereIn('type', $types)
            ->get();
    }

    /**
     * Quality-weighted points from completed curriculum activities of the given types.
     */
    protected function curriculumPoints(User $fellow, Track $track, array $types): float
    {
        return (float) FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->where('status', CurriculumStatus::COMPLETED)
            ->whereHas('curriculumActivity', function ($q) use ($track, $types) {
                $q->where('track_id', $track->id)->whereIn('type', $types);
            })
            ->get(['id', 'points_awarded', 'score_awarded'])
            ->sum(fn ($p) => $this->qualityWeightedPoints((float) $p->points_awarded, $p->score_awarded));
    }

    /**
     * Points the track's active curriculum offers for the given types.
     */
    protected function curriculumPointsAvailable(Track $track, array $types): float
    {
        return (float) TrackCurriculumActivity::where('track_id', $track->id)
            ->where('is_active', true)
            ->whereIn('type', $types)
            ->sum('points');
    }

    /**
     * Reference points for a category: the curriculum's points, with a floor.
     */
    protected function expectedPoints(Track $track, array $types, string $category): float
    {
        return max($this->curriculumPointsAvailable($track, $types), (float) self::MIN_EXPECTED_POINTS[$category]);
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

        if ($lastActivity && $lastActivity->reviewed_at) {
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
     *
     * @param bool $notify       Send a notification on tier promotion.
     * @param bool $markActivity Treat the update as fellow activity (last_active_at).
     *                           Bulk recalculations pass false.
     */
    public function updateScore(User $fellow, Track $track, bool $notify = true, bool $markActivity = true): FellowTrack
    {
        return DB::transaction(function () use ($fellow, $track, $notify, $markActivity) {
            $fellowTrack = $fellow->fellowTracks()
                ->where('track_id', $track->id)
                ->lockForUpdate()
                ->first();

            if (!$fellowTrack) {
                throw new \Exception("Fellow is not enrolled in this track.");
            }

            $previousTier = Tier::tryFrom((string) $fellowTrack->tier) ?? Tier::ROOKIE;

            $categories = $this->calculateCategoryScores($fellow, $track);
            $newScore = $this->totalFromCategories($fellow, $track, $categories);
            $newTier = Tier::fromScore($newScore);

            // Raw points earned (not quality-weighted), for display
            $totalPointsEarned = FellowCurriculumProgress::where('fellow_id', $fellow->id)
                ->where('status', CurriculumStatus::COMPLETED)
                ->whereHas('curriculumActivity', fn ($q) => $q->where('track_id', $track->id))
                ->sum('points_awarded')
                + Activity::where('fellow_id', $fellow->id)
                    ->where('track_id', $track->id)
                    ->where('status', ActivityStatus::APPROVED)
                    ->sum('points_earned');

            $attributes = [
                'score' => $newScore,
                'tier' => $newTier->value,
                'technical_score' => $categories['technical'],
                'interview_score' => $categories['interview'],
                'portfolio_score' => $categories['portfolio'],
                'collaboration_score' => $categories['collaboration'],
                'learning_score' => $categories['learning'],
                'total_points_earned' => $totalPointsEarned,
            ];

            if ($markActivity) {
                $attributes['last_active_at'] = now();
            }

            $fellowTrack->update($attributes);

            if ($notify && $newTier->rank() > $previousTier->rank()) {
                Notification::sendTierPromotion($fellow, $previousTier->label(), $newTier->label(), $newScore);
            }

            return $fellowTrack->fresh();
        });
    }

    /**
     * Recalculate scores for all fellows in a track.
     */
    public function recalculateAllForTrack(Track $track): int
    {
        $count = 0;

        $track->fellows->each(function ($fellow) use ($track, &$count) {
            $this->updateScore($fellow, $track, markActivity: false);
            $count++;
        });

        return $count;
    }

    /**
     * Get score breakdown for display.
     */
    public function getScoreBreakdown(User $fellow, Track $track): array
    {
        $categories = $this->calculateCategoryScores($fellow, $track);
        $weights = $this->categoryWeights($track);

        $meta = [
            'technical' => ['label' => 'Technical Skills', 'icon' => 'code', 'color' => 'blue'],
            'interview' => ['label' => 'Interview Performance', 'icon' => 'video-camera', 'color' => 'purple'],
            'portfolio' => ['label' => 'Portfolio Quality', 'icon' => 'briefcase', 'color' => 'green'],
            'collaboration' => ['label' => 'Collaboration', 'icon' => 'users', 'color' => 'teal'],
            'learning' => ['label' => 'Continuous Learning', 'icon' => 'academic-cap', 'color' => 'yellow'],
        ];

        $breakdown = [];
        foreach ($meta as $key => $info) {
            $breakdown[$key] = ['score' => $categories[$key], 'weight' => $weights[$key]] + $info;
        }

        return [
            'total' => $this->totalFromCategories($fellow, $track, $categories),
            'categories' => $breakdown,
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

        $currentScore = (float) $fellowTrack->score;
        $currentTier = Tier::tryFrom((string) $fellowTrack->tier) ?? Tier::fromScore($currentScore);
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

        $nextThreshold = (float) ($thresholds[$nextTier->value] ?? $nextTier->defaultRange()['min']);
        $pointsNeeded = max(0, $nextThreshold - $currentScore);

        return [
            'tier' => $nextTier,
            'tier_label' => $nextTier->label(),
            'points_needed' => round($pointsNeeded, 3),
            'current_score' => $currentScore,
            'threshold' => $nextThreshold,
            'progress_percentage' => $nextThreshold > 0 ? min(100, ($currentScore / $nextThreshold) * 100) : 100,
        ];
    }
}
