<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A presentation a fellow gave (project demo, workshop, talk...), reviewed by
 * the I-NNOVA team. When approved, `category_boosts` (category => amount out
 * of 100) is added to those Career Capital categories for its track.
 */
class Presentation extends Model
{
    use HasUuids, SoftDeletes;

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_CHANGES_REQUESTED = 'changes_requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const KINDS = [
        'project_demo' => 'Project demo',
        'workshop' => 'Workshop',
        'talk' => 'Talk',
        'lightning_talk' => 'Lightning talk',
        'panel' => 'Panel discussion',
        'other' => 'Other',
    ];

    /** The Career Capital categories a presentation can add to. */
    public const CATEGORIES = [
        'technical' => 'Technical',
        'interview' => 'Interview & communication',
        'portfolio' => 'Portfolio',
        'collaboration' => 'Collaboration',
        'learning' => 'Learning',
    ];

    protected $fillable = [
        'fellow_id', 'track_id', 'title', 'kind', 'presented_at', 'event_name', 'audience', 'summary', 'learnings',
        'co_presenters', 'links', 'files', 'status', 'submission_count', 'submitted_at',
        'reviewer_id', 'reviewed_at', 'review_feedback', 'category_boosts',
    ];

    protected function casts(): array
    {
        return [
            'presented_at' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'links' => 'array',
            'files' => 'array',
            'category_boosts' => 'array',
        ];
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function isEditableByFellow(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_CHANGES_REQUESTED], true);
    }

    public function kindLabel(): string
    {
        return self::KINDS[$this->kind] ?? ucfirst(str_replace('_', ' ', (string) $this->kind));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_SUBMITTED => 'Awaiting review',
            self::STATUS_CHANGES_REQUESTED => 'Changes requested',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Not accepted',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'bg-green-600/20 text-green-400 border-green-500/30',
            self::STATUS_CHANGES_REQUESTED => 'bg-amber-600/20 text-amber-400 border-amber-500/30',
            self::STATUS_REJECTED => 'bg-red-600/20 text-red-400 border-red-500/30',
            default => 'bg-blue-600/20 text-blue-400 border-blue-500/30',
        };
    }

    /** Total added to each category by approved presentations, for a fellow and track. */
    public static function boostsFor(int $fellowId, string $trackId, ?string $exceptId = null): array
    {
        $totals = array_fill_keys(array_keys(self::CATEGORIES), 0.0);

        self::approved()
            ->where('fellow_id', $fellowId)
            ->where('track_id', $trackId)
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->pluck('category_boosts')
            ->each(function ($boosts) use (&$totals) {
                foreach ((array) $boosts as $category => $amount) {
                    if (isset($totals[$category]) && is_numeric($amount)) {
                        $totals[$category] += (float) $amount;
                    }
                }
            });

        return $totals;
    }
}
