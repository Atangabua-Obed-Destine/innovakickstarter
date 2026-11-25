<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A fellow's "was this lesson clear?" answer for a curriculum activity. */
class LessonFeedback extends Model
{
    use HasUuids;

    protected $table = 'lesson_feedback';

    protected $fillable = ['curriculum_activity_id', 'fellow_id', 'is_clear', 'comment'];

    protected function casts(): array
    {
        return ['is_clear' => 'boolean'];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(TrackCurriculumActivity::class, 'curriculum_activity_id');
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }
}
