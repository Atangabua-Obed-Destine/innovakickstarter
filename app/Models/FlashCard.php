<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** A spaced-repetition review card for a chapter of a track. Cards linked to a lesson are its quick checks. */
class FlashCard extends Model
{
    use HasUuids;

    protected $fillable = ['track_id', 'curriculum_activity_id', 'after_step', 'key', 'chapter', 'skill', 'question', 'options', 'answer_index', 'explanation', 'is_active'];

    protected $hidden = ['answer_index', 'explanation'];

    protected function casts(): array
    {
        return ['options' => 'array', 'answer_index' => 'integer', 'chapter' => 'integer', 'after_step' => 'integer', 'is_active' => 'boolean'];
    }
}
