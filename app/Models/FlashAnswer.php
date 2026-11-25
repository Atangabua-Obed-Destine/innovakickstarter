<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashAnswer extends Model
{
    protected $fillable = ['fellow_id', 'card_id', 'correct', 'xp_awarded', 'answered_on'];

    protected function casts(): array
    {
        return ['correct' => 'boolean', 'answered_on' => 'date'];
    }
}
