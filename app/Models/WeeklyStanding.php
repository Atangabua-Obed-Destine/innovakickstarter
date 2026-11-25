<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One fellow's result for one week of the weekly bar (Monday to Sunday). */
class WeeklyStanding extends Model
{
    protected $fillable = ['fellow_id', 'track_id', 'week_start', 'active_days', 'practice_days', 'progress_actions', 'progress_waived', 'met',
        'exempt', 'exempt_reason', 'exempted_by', 'consecutive_misses', 'action', 'fee_id'];

    protected function casts(): array
    {
        return ['week_start' => 'date', 'progress_waived' => 'boolean', 'met' => 'boolean', 'exempt' => 'boolean'];
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }
}
