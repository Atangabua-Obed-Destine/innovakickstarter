<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A short real-world challenge from an employer, posted by the I-NNOVA team. */
class EmployerBrief extends Model
{
    use HasUuids;

    public const STATUSES = ['draft' => 'Draft', 'open' => 'Open', 'closed' => 'Closed'];

    protected $fillable = ['track_id', 'company_name', 'recruiter_id', 'title', 'summary', 'deliverable', 'resources_url', 'deadline_on', 'reward', 'status', 'created_by'];

    protected function casts(): array
    {
        return ['deadline_on' => 'date'];
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(BriefSubmission::class, 'brief_id');
    }

    /** Open, not past its deadline (the deadline day itself counts). */
    public function acceptsSubmissions(): bool
    {
        return $this->status === 'open' && $this->deadline_on->endOfDay()->isFuture();
    }

    /** Briefs a fellow can see: open (or closed with their submission) for their tracks or all tracks. */
    public function scopeVisibleTo($query, User $fellow)
    {
        $trackIds = $fellow->fellowTracks()->pluck('track_id');

        return $query->where('status', '!=', 'draft')
            ->where(fn ($q) => $q->whereNull('track_id')->orWhereIn('track_id', $trackIds));
    }
}
