<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypingRace extends Model
{
    use HasUuids;

    protected $fillable = ['league', 'text', 'status', 'starts_at', 'finished_at', 'ghost_wpm'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'finished_at' => 'datetime', 'ghost_wpm' => 'float'];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TypingRaceEntry::class, 'race_id');
    }
}
