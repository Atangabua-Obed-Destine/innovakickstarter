<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A Base Camp area (computer, maths, english, spreadsheets): check, lessons and module test. Content holds the answers: never send it to a page as-is. */
class FoundationArea extends Model
{
    protected $fillable = ['key', 'title', 'icon', 'summary', 'why', 'content', 'sort', 'is_active'];

    protected $hidden = ['content'];

    protected function casts(): array
    {
        return ['content' => 'array', 'is_active' => 'boolean'];
    }
}
