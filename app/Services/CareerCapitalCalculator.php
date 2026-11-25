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
 *   Career Capital = Σ (category score × category weight) / 100
 *
 * Weights: Technical 25, Interview 25, Portfolio 20, Collaboration 15,
 * Learning 15, unless the track rubric gives ['<category>']['weight'].
 *
 * The scoring is deliberately hard (see calculateHardScaledScore): each
 * category compares the points earned (approved activities + completed
 * curriculum work, at the points awarded) with a ceiling of 10x the
 * category's curriculum, and never less than 10,000 points:
 *
 *   category score = 100 × (1 − e^(−0.1 × earned / ceiling))
 *
 * Interview readiness adds mock interview results (see
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
     * Category weights (percent) when the track rubric does not set them.
     */
    public const DEFAULT_WEIGHTS = [
        'technical' => 25,
        'interview' => 25,
        'portfolio' => 20,
        'collaboration' => 15,
        'learning' => 15,
    ];

    /**
     * Smallest curriculum size assumed per category (before the 10x ceiling).
     */
    public const MIN_CURRICULUM_POINTS = [
        'technical' => 100,
        'interview' => 50,
        'portfolio' => 50,
        'collaboration' => 0,
        'learning' => 0,
    ];

    /**
     * Applies a strict difficulty curve to make score progression highly rewarding but difficult.
     * The true theoretical ceiling requires significant "above and beyond" work.
     *
     *   ceiling = max(10,000, 10 × curriculum points)
     *   score   = 100 × (1 − e^(−0.1 × earned / ceiling))
     */
    public function calculateHardScaledScore(float $earnedPoints, float $curriculumMaxPoints): float
    {
        // Require an astronomical 10,000 baseline points minimum
        $absoluteMinCeiling = 10000;

        // True ceiling is 10x the curriculum size
        $ceiling = max($absoluteMinCeiling, $curriculumMaxPoints * 10.0);

        $ratio = $earnedPoints / $ceiling;

        // Unbelievably slow logarithmic decay curve: 100 * (1 - e^(-0.1 * ratio))
        $score = 100 * (1 - exp(-0.1 * $ratio));

        return round(min(100.0, max(0.0, $score)), 3);
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
    public function calculateCategoryScores(User $fellow, Track $track, ?string $exceptPresentationId = null): array
    {
        $scores = [
            'technical' => $this->calculateTechnicalScore($fellow, $track),
            'interview' => $this->calculateInterviewScore($fellow, $track),
            'portfolio' => $this->calculatePortfolioScore($fellow, $track),
            'collaboration' => $this->calculateCollaborationScore($fellow, $track),
            'learning' => $this->calculateLearningScore($fellow, $track),
        ];

        // Approved presentations add what the reviewer chose to each category.
        $boosts = \App\Models\Presentation::boostsFor($fellow->id, $track->id, $exceptPresentationId);
        foreach ($scores as $key => $score) {
            $scores[$key] = round(min(self::MAX_SCORE, $score + ($boosts[$key] ?? 0)), 3);
        }

        return $scores;
    }

    /**
     * What a reviewer needs to preview a presentation's effect: the fellow's
     * category scores without that presentation, the weights, the decay
     * multiplier and the tier thresholds. Total = sum(min(100, category +
     * added) x weight / 100) x decay.
     */
    public function presentationPreviewBasis(User $fellow, Track $track, ?string $presentationId = null): array
    {
        $categories = $this->calculateCategoryScores($fellow, $track, $presentationId);

        return [
            'categories' => $categories,
            'weights' => $this->categoryWeights($track),
            'decay' => round($this->applyDecay($fellow, $track, 100.0) / 100, 4),
            'total' => $this->totalFromCategories($fellow, $track, $categories),
            'current_total' => (float) ($fellow->fellowTracks()->where('track_id', $track->id)->value('score') ?? 0),
            'tiers' => collect(Tier::cases())->map(fn (Tier $t) => ['label' => $t->label(), 'min' => (float) $t->defaultRange()['min']])
                ->sortBy('min')->values()->all(),
        ];
    }

    /**
     * Category weights (percent) for a track: ['<category>']['weight'] from
     * the track rubric, otherwise DEFAULT_WEIGHTS.
     */
    public function categoryWeights(Track $track): array
    {
        $rubric = is_array($track->scoring_rubric) ? $track->scoring_rubric : [];
        $weights = [];

        foreach (self::DEFAULT_WEIGHTS as $key => $default) {
            $value = is_array($rubric[$key] ?? null) ? ($rubric[$key]['weight'] ?? null) : null;
            $weights[$key] = is_numeric($value) ? (float) $value : (float) $default;
        }

        return $weights;
    }

    /**
     * Weighted total of the category scores, with decay applied.
     */
    protected function totalFromCategories(User $fellow, Track $track, array $categories): float
    {
        $weighted = 0.0;
        foreach ($this->categoryWeights($track) as $key => $weight) {
            $weighted += ($categories[$key] ?? 0) * $weight / 100;
        }

        $score = $this->applyDecay($fellow, $track, $weighted);

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
        $score = $this->calculateHardScaledScore($earned, $this->expectedPoints($track, $types, 'technical'));

        // Variety bonus
        if ($activities->pluck('type')->unique()->count() >= 3) {
            $score *= 1.05;
        }

        return round(min(self::MAX_SCORE, $score), 3);
    }

    /**
     * Interview readiness: mock interview results plus interview-related work
     * (hard-scaled like the other categories), added together.
     */
    public function calculateInterviewScore(User $fellow, Track $track): float
    {
        $sessionScore = $this->mockInterviewScore($fellow, $track);

        $types = ActivityType::getValuesByCategory(CareerCapitalCategory::INTERVIEW);
        $activities = $this->approvedActivities($fellow, $track, $types);
        $earned = $activities->sum('points_earned') + $this->curriculumPoints($fellow, $track, $types);
        $activityScore = $this->calculateHardScaledScore($earned, $this->expectedPoints($track, $types, 'interview'));

        return round(min(self::MAX_SCORE, $sessionScore + $activityScore), 3);
    }

    /**
     * Score from the last 10 completed mock interviews (0-100).
     *
     * Reads the 0-100 `score` column. (`overall_score` is a 1-10 mentor
     * rating on human interviews and empty on AI interviews.)
     */
    public function mockInterviewScore(User $fellow, Track $track): float
    {
        $interviews = $fellow->interviewSessions()
            ->where('track_id', $track->id)
            ->completed()
            // Interviews the AI could not score have no score and do not count.
            ->whereNotNull('score')
            ->orderByDesc('completed_at')
            ->limit(10) // Use last 10 interviews
            ->get(['id', 'score', 'completed_at']);

        if ($interviews->isEmpty()) {
            return 0.0;
        }

        $scores = $interviews->pluck('score')->map(fn ($s) => (float) $s)->values()->all();

        // Weighted average: recent interviews matter more
        $weightedSum = 0.0;
        $weightTotal = 0.0;
        $weight = 1.0;

        foreach ($scores as $score) {
            $weightedSum += $score * $weight;
            $weightTotal += $weight;
            $weight *= 0.9; // Each older interview has 10% less weight
        }

        $sessionScore = $weightedSum / $weightTotal;

        // Strict Volume Penalty: An average is meaningless with low volume.
        // They must do at least 20 interviews to unlock their full score.
        $sessionScore *= min(1.0, count($scores) / 20);

        // Bonus for interview consistency (low variance)
        if (count($scores) >= 3 && $this->calculateVariance($scores) < 10) {
            $sessionScore = min(100, $sessionScore * 1.05);
        }

        return min(self::MAX_SCORE, $sessionScore);
    }

    /**
     * Portfolio quality: deployed projects, case studies, content.
     */
    public function calculatePortfolioScore(User $fellow, Track $track): float
    {
        $types = ActivityType::getValuesByCategory(CareerCapitalCategory::PORTFOLIO);
        $activities = $this->approvedActivities($fellow, $track, $types);

        $earned = $activities->sum('points_earned') + $this->curriculumPoints($fellow, $track, $types);
        $score = $this->calculateHardScaledScore($earned, $this->expectedPoints($track, $types, 'portfolio'));

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
        $score = $this->calculateHardScaledScore($earned, $this->expectedPoints($track, $types, 'collaboration'));

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
        $score = $this->calculateHardScaledScore($earned, $this->expectedPoints($track, $types, 'learning'));

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
     * Points awarded for completed curriculum activities of the given types.
     */
    protected function curriculumPoints(User $fellow, Track $track, array $types): float
    {
        return (float) FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->where('status', CurriculumStatus::COMPLETED)
            ->whereHas('curriculumActivity', function ($q) use ($track, $types) {
                $q->where('track_id', $track->id)->whereIn('type', $types);
            })
            ->sum('points_awarded');
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
     * Curriculum size for a category: the curriculum's points, with a floor.
     */
    protected function expectedPoints(Track $track, array $types, string $category): float
    {
        return max($this->curriculumPointsAvailable($track, $types), (float) self::MIN_CURRICULUM_POINTS[$category]);
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
