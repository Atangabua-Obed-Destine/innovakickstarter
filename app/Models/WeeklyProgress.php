<?php

namespace App\Models;

use App\Enums\Tier;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * WeeklyProgress Model
 * 
 * Tracks the 4-pillar weekly accountability system.
 * Fellows must complete activities in all 4 pillars each week or score freezes.
 * 
 * The Four Pillars:
 * 1. BUILD: Projects, code contributions, portfolio work
 * 2. BRAND: Blog posts, talks, content publishing
 * 3. INTERVIEW: Mock interviews, practice sessions
 * 4. COLLABORATE: Networking, code reviews, mentorship
 * 
 * @property string $id UUID
 * @property int $fellow_id
 * @property string $track_id UUID
 * @property int $week_number (1-52)
 * @property int $year
 * @property bool $build_completed
 * @property bool $brand_completed
 * @property bool $interview_completed
 * @property bool $collaborate_completed
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class WeeklyProgress extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table name.
     */
    protected $table = 'weekly_progress';

    /**
     * The primary key type.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fellow_id',
        'track_id',
        'week_number',
        'year',
        'week_start',
        'week_end',
        'build_completed',
        'brand_completed',
        'interview_completed',
        'collaborate_completed',
        'build_activity_id',
        'brand_activity_id',
        'interview_activity_id',
        'collaborate_activity_id',
        'build_completed_at',
        'brand_completed_at',
        'interview_completed_at',
        'collaborate_completed_at',
        'build_points',
        'brand_points',
        'interview_points',
        'collaborate_points',
        'total_points',
        'all_pillars_completed',
        'score_frozen',
        'score_frozen_at',
        'score_unfrozen_at',
        'reminder_sent',
        'reminder_sent_at',
        'freeze_warning_sent',
        'freeze_warning_sent_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'build_completed' => 'boolean',
            'brand_completed' => 'boolean',
            'interview_completed' => 'boolean',
            'collaborate_completed' => 'boolean',
            'build_completed_at' => 'datetime',
            'brand_completed_at' => 'datetime',
            'interview_completed_at' => 'datetime',
            'collaborate_completed_at' => 'datetime',
            'build_points' => 'integer',
            'brand_points' => 'integer',
            'interview_points' => 'integer',
            'collaborate_points' => 'integer',
            'total_points' => 'integer',
            'all_pillars_completed' => 'boolean',
            'score_frozen' => 'boolean',
            'score_frozen_at' => 'datetime',
            'score_unfrozen_at' => 'datetime',
            'reminder_sent' => 'boolean',
            'reminder_sent_at' => 'datetime',
            'freeze_warning_sent' => 'boolean',
            'freeze_warning_sent_at' => 'datetime',
        ];
    }

    /**
     * The attributes that should have default values.
     */
    protected $attributes = [
        'build_completed' => false,
        'brand_completed' => false,
        'interview_completed' => false,
        'collaborate_completed' => false,
        'build_points' => 0,
        'brand_points' => 0,
        'interview_points' => 0,
        'collaborate_points' => 0,
        'total_points' => 0,
        'all_pillars_completed' => false,
        'score_frozen' => false,
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (WeeklyProgress $progress) {
            // Calculate total points
            $progress->total_points = $progress->build_points + $progress->brand_points +
                                      $progress->interview_points + $progress->collaborate_points;

            // Check if all pillars are complete
            $progress->all_pillars_completed = $progress->build_completed &&
                                     $progress->brand_completed &&
                                     $progress->interview_completed &&
                                     $progress->collaborate_completed;

            // Set completed_at timestamps are already per-pillar

            // Score freezes if week ends incomplete
            if (!$progress->all_pillars_completed && $progress->week_end?->isPast()) {
                $progress->score_frozen = true;
                if (!$progress->score_frozen_at) {
                    $progress->score_frozen_at = now();
                }
            }
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the fellow.
     */
    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    /**
     * Get the track.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get completion percentage.
     */
    public function getCompletionPercentageAttribute(): int
    {
        $completed = 0;
        
        if ($this->build_completed) $completed++;
        if ($this->brand_completed) $completed++;
        if ($this->interview_completed) $completed++;
        if ($this->collaborate_completed) $completed++;

        return ($completed / 4) * 100;
    }

    /**
     * Get pillars remaining count.
     */
    public function getPillarsRemainingAttribute(): int
    {
        return 4 - $this->pillars_completed;
    }

    /**
     * Get completed pillars count.
     */
    public function getPillarsCompletedAttribute(): int
    {
        $completed = 0;
        
        if ($this->build_completed) $completed++;
        if ($this->brand_completed) $completed++;
        if ($this->interview_completed) $completed++;
        if ($this->collaborate_completed) $completed++;

        return $completed;
    }

    /**
     * Get incomplete pillars.
     */
    public function getIncompletePillarsAttribute(): array
    {
        $incomplete = [];

        if (!$this->build_completed) $incomplete[] = 'build';
        if (!$this->brand_completed) $incomplete[] = 'brand';
        if (!$this->interview_completed) $incomplete[] = 'interview';
        if (!$this->collaborate_completed) $incomplete[] = 'collaborate';

        return $incomplete;
    }

    /**
     * Get complete pillars.
     */
    public function getCompletePillarsAttribute(): array
    {
        $complete = [];

        if ($this->build_completed) $complete[] = 'build';
        if ($this->brand_completed) $complete[] = 'brand';
        if ($this->interview_completed) $complete[] = 'interview';
        if ($this->collaborate_completed) $complete[] = 'collaborate';

        return $complete;
    }

    /**
     * Get formatted week label.
     */
    public function getWeekLabelAttribute(): string
    {
        return "Week {$this->week_number}, {$this->year}";
    }

    /**
     * Get formatted date range.
     */
    public function getDateRangeAttribute(): string
    {
        if (!$this->week_start || !$this->week_end) {
            return 'N/A';
        }

        return $this->week_start->format('M j') . ' - ' . 
               $this->week_end->format('M j, Y');
    }

    /**
     * Check if this is the current week.
     */
    public function getIsCurrentWeekAttribute(): bool
    {
        return $this->week_number === now()->isoWeek() &&
               $this->year === now()->year;
    }

    /**
     * Check if week has ended.
     */
    public function getHasEndedAttribute(): bool
    {
        return $this->week_end?->isPast() ?? false;
    }

    /**
     * Get days remaining in week.
     */
    public function getDaysRemainingAttribute(): int
    {
        if (!$this->week_end || $this->week_end->isPast()) {
            return 0;
        }

        return now()->diffInDays($this->week_end);
    }

    /**
     * Check if tier changed.
     */
    public function getTierChangedAttribute(): bool
    {
        return $this->tier_before !== null &&
               $this->tier_after !== null &&
               $this->tier_before !== $this->tier_after;
    }

    /**
     * Get pillar breakdown.
     */
    public function getPillarBreakdownAttribute(): array
    {
        return [
            'build' => [
                'completed' => $this->build_completed,
                'points' => $this->build_points,
                'activity_id' => $this->build_activity_id,
                'icon' => 'code',
                'color' => $this->build_completed ? 'blue' : 'gray',
                'label' => 'Build',
            ],
            'brand' => [
                'completed' => $this->brand_completed,
                'points' => $this->brand_points,
                'activity_id' => $this->brand_activity_id,
                'icon' => 'share',
                'color' => $this->brand_completed ? 'purple' : 'gray',
                'label' => 'Brand',
            ],
            'interview' => [
                'completed' => $this->interview_completed,
                'points' => $this->interview_points,
                'activity_id' => $this->interview_activity_id,
                'icon' => 'video-camera',
                'color' => $this->interview_completed ? 'green' : 'gray',
                'label' => 'Interview',
            ],
            'collaborate' => [
                'completed' => $this->collaborate_completed,
                'points' => $this->collaborate_points,
                'activity_id' => $this->collaborate_activity_id,
                'icon' => 'users',
                'color' => $this->collaborate_completed ? 'teal' : 'gray',
                'label' => 'Collaborate',
            ],
        ];
    }

    // ==========================================
    // METHODS
    // ==========================================

    /**
     * Mark a pillar as completed.
     */
    public function completePillar(string $pillar, array $activityIds = [], int $points = 0): void
    {
        $pillarField = strtolower($pillar) . '_completed';
        $activitiesField = strtolower($pillar) . '_activities';
        $pointsField = strtolower($pillar) . '_points';

        $this->update([
            $pillarField => true,
            $activitiesField => $activityIds,
            $pointsField => $points,
        ]);
    }

    /**
     * Add activity to pillar.
     */
    public function addActivity(string $pillar, string $activityId, int $points = 0): void
    {
        $activitiesField = strtolower($pillar) . '_activities';
        $pointsField = strtolower($pillar) . '_points';

        $activities = $this->$activitiesField ?? [];
        $activities[] = $activityId;

        $this->update([
            $activitiesField => array_unique($activities),
            $pointsField => $this->$pointsField + $points,
        ]);
    }

    /**
     * Freeze score due to incomplete week.
     */
    public function freezeScore(): void
    {
        $this->update([
            'score_frozen' => true,
            'notes' => ($this->notes ?? '') . "\nScore frozen - incomplete pillars: " . 
                       implode(', ', $this->incomplete_pillars),
        ]);
    }

    /**
     * Record score change.
     */
    public function recordScoreChange(float $scoreBefore, float $scoreAfter, ?Tier $tierBefore = null, ?Tier $tierAfter = null): void
    {
        $this->update([
            'score_before' => $scoreBefore,
            'score_after' => $scoreAfter,
            'score_change' => $scoreAfter - $scoreBefore,
            'tier_before' => $tierBefore,
            'tier_after' => $tierAfter,
        ]);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to current week.
     */
    public function scopeCurrentWeek($query)
    {
        return $query->where('week_number', now()->isoWeek())
            ->where('year', now()->year);
    }

    /**
     * Scope to specific week.
     */
    public function scopeForWeek($query, int $week, int $year)
    {
        return $query->where('week_number', $week)
            ->where('year', $year);
    }

    /**
     * Scope to complete weeks.
     */
    public function scopeComplete($query)
    {
        return $query->where('all_pillars_completed', true);
    }

    /**
     * Scope to incomplete weeks.
     */
    public function scopeIncomplete($query)
    {
        return $query->where('all_pillars_completed', false);
    }

    /**
     * Scope to frozen scores.
     */
    public function scopeFrozen($query)
    {
        return $query->where('score_frozen', true);
    }

    /**
     * Scope to this year.
     */
    public function scopeThisYear($query)
    {
        return $query->where('year', now()->year);
    }

    /**
     * Order by most recent.
     */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('year')->orderByDesc('week_number');
    }

    /**
     * Order by oldest.
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('year')->orderBy('week_number');
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Get or create progress for current week.
     */
    public static function getOrCreateForCurrentWeek(User $fellow, Track $track): self
    {
        $weekNumber = now()->isoWeek();
        $year = now()->isoWeekYear();
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        return static::firstOrCreate(
            [
                'fellow_id' => $fellow->id,
                'track_id' => $track->id,
                'week_number' => $weekNumber,
                'year' => $year,
            ],
            [
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
            ]
        );
    }

    /**
     * Get weekly streak count.
     */
    public static function getStreak(User $fellow, Track $track): int
    {
        $progress = static::where('fellow_id', $fellow->id)
            ->where('track_id', $track->id)
            ->where('all_pillars_completed', true)
            ->orderByDesc('year')
            ->orderByDesc('week_number')
            ->get();

        if ($progress->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $expectedWeek = now()->isoWeek();
        $expectedYear = now()->isoWeekYear();

        foreach ($progress as $week) {
            if ($week->week_number === $expectedWeek && $week->year === $expectedYear) {
                $streak++;
                
                // Move to previous week
                $expectedWeek--;
                if ($expectedWeek < 1) {
                    $expectedYear--;
                    $expectedWeek = 52;
                }
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Get pillar icon.
     */
    public static function getPillarIcon(string $pillar): string
    {
        return match(strtolower($pillar)) {
            'build' => 'code',
            'brand' => 'share',
            'interview' => 'video-camera',
            'collaborate' => 'users',
            default => 'check-circle',
        };
    }

    /**
     * Get pillar color.
     */
    public static function getPillarColor(string $pillar): string
    {
        return match(strtolower($pillar)) {
            'build' => 'blue',
            'brand' => 'purple',
            'interview' => 'green',
            'collaborate' => 'teal',
            default => 'gray',
        };
    }
}
