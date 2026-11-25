<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A weekly Data Detective case. The answer key and solution are never serialised. */
class DetectiveCase extends Model
{
    use HasUuids;

    protected $fillable = ['track_id', 'slug', 'title', 'difficulty', 'xp', 'story', 'data_note', 'hints', 'questions', 'answer_key', 'solution', 'sort', 'is_active'];

    protected $hidden = ['answer_key', 'solution'];

    protected function casts(): array
    {
        return ['hints' => 'array', 'questions' => 'array', 'answer_key' => 'array', 'is_active' => 'boolean', 'difficulty' => 'integer', 'xp' => 'integer'];
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
