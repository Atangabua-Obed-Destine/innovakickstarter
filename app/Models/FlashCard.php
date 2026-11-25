<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** A spaced-repetition review card for a chapter of a track. */
class FlashCard extends Model
{
    use HasUuids;

    protected $fillable = ['track_id', 'key', 'chapter', 'skill', 'question', 'options', 'answer_index', 'explanation', 'is_active'];

    protected $hidden = ['answer_index', 'explanation'];

    protected function casts(): array
    {
        return ['options' => 'array', 'answer_index' => 'integer', 'chapter' => 'integer', 'is_active' => 'boolean'];
    }
}
