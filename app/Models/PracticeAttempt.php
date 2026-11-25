<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticeAttempt extends Model
{
    protected $fillable = ['challenge_id', 'fellow_id', 'correct', 'xp_awarded', 'was_daily', 'practiced_on'];

    protected function casts(): array
    {
        return ['correct' => 'boolean', 'was_daily' => 'boolean', 'practiced_on' => 'date', 'xp_awarded' => 'integer'];
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(PracticeChallenge::class, 'challenge_id');
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }
}
