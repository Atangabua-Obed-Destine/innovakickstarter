<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $curriculum_activity_id
 * @property int $user_id
 * @property string|null $parent_id
 * @property string $content
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ActivityComment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'curriculum_activity_id',
        'user_id',
        'parent_id',
        'content',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(TrackCurriculumActivity::class, 'curriculum_activity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ActivityComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ActivityComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }
}
