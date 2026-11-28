<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A track's mock-interview blueprint: role, interviewer persona, rubric, hire
 * thresholds, rounds, chapter loops and the question bank (imported with
 * `php artisan interviews:import`). Private content: never shown whole to fellows.
 */
class InterviewBlueprint extends Model
{
    protected $fillable = ['track_id', 'version', 'data', 'imported_at'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'version' => 'integer',
            'imported_at' => 'datetime',
        ];
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
