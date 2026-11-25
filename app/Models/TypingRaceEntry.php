<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypingRaceEntry extends Model
{
    protected $fillable = ['race_id', 'fellow_id', 'typed', 'errors', 'wpm', 'accuracy', 'finished_at', 'place'];

    protected function casts(): array
    {
        return ['finished_at' => 'datetime', 'wpm' => 'float', 'accuracy' => 'float'];
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }
}
