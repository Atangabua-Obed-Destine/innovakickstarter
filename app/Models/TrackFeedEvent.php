<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** An achievement shown in a track's community feed. */
class TrackFeedEvent extends Model
{
    public const ICONS = [
        'chapter' => '🏁',
        'project' => '🛠️',
        'brand_post' => '📣',
        'presentation' => '🎤',
        'level_up' => '🚀',
        'practice_streak' => '🔥',
        'certificate' => '🎓',
        'detective' => '🕵️',
        'brief' => '💼',
        'orientation' => '🪪',
    ];

    protected $fillable = ['track_id', 'fellow_id', 'type', 'title', 'body', 'url', 'subject_key'];

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    public function kudos(): HasMany
    {
        return $this->hasMany(TrackFeedKudo::class, 'event_id');
    }

    public function icon(): string
    {
        return self::ICONS[$this->type] ?? '⭐';
    }
}
