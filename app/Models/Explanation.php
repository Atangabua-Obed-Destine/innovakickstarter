<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Explanation extends Model
{
    protected $fillable = ['fellow_id', 'curriculum_activity_id', 'text', 'score', 'feedback', 'correct', 'xp_awarded', 'answered_on', 'model'];

    protected function casts(): array
    {
        return ['feedback' => 'array', 'correct' => 'boolean', 'answered_on' => 'date'];
    }
}
