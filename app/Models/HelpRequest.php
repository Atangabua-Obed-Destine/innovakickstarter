<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** "Ask a human": a fellow's question to the I-NNOVA team, optionally about a lesson step. */
class HelpRequest extends Model
{
    protected $fillable = ['fellow_id', 'track_id', 'curriculum_activity_id', 'step_key', 'question', 'status', 'answer', 'answered_by', 'answered_at'];

    protected function casts(): array
    {
        return ['answered_at' => 'datetime'];
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(TrackCurriculumActivity::class, 'curriculum_activity_id');
    }

    public function answerer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}
