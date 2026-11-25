<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypingSession extends Model
{
    protected $fillable = ['fellow_id', 'mode', 'lesson_key', 'wpm', 'accuracy', 'chars', 'seconds'];

    protected function casts(): array
    {
        return ['wpm' => 'float', 'accuracy' => 'float'];
    }
}
