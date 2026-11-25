<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A fellow's orientation for a track: sections seen, rules quiz, and their commitment (with a letter to their future self). */
class TrackOrientation extends Model
{
    protected $fillable = ['fellow_id', 'track_id', 'sections_done', 'quiz_passed_at', 'quiz_attempts', 'commitment', 'letter_opened_at', 'completed_at'];

    protected function casts(): array
    {
        return [
            'sections_done' => 'array',
            'commitment' => 'array',
            'quiz_passed_at' => 'datetime',
            'letter_opened_at' => 'datetime',
            'completed_at' => 'datetime',
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
}
