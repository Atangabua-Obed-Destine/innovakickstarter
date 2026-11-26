<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A team of fellows working together through a squad chapter. */
class Squad extends Model
{
    protected $fillable = ['track_id', 'milestone_id', 'name', 'repo_url', 'created_by'];

    public function members(): HasMany
    {
        return $this->hasMany(SquadMember::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(TrackMilestone::class, 'milestone_id');
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
