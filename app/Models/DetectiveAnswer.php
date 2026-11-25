<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetectiveAnswer extends Model
{
    protected $fillable = ['fellow_id', 'case_id', 'question_key', 'correct', 'xp_awarded', 'answered_on'];

    protected function casts(): array
    {
        return ['correct' => 'boolean', 'answered_on' => 'date'];
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }
}
