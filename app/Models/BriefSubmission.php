<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BriefSubmission extends Model
{
    public const STATUSES = ['submitted' => 'Submitted', 'shortlisted' => 'Shortlisted', 'winner' => 'Winner', 'not_selected' => 'Not selected'];

    protected $fillable = ['brief_id', 'fellow_id', 'url', 'notes', 'status', 'feedback', 'reviewed_by', 'reviewed_at'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function brief(): BelongsTo
    {
        return $this->belongsTo(EmployerBrief::class, 'brief_id');
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    public function badgeClass(): string
    {
        return match ($this->status) {
            'winner' => 'bg-amber-600/20 text-amber-400 border-amber-500/30',
            'shortlisted' => 'bg-green-600/20 text-green-400 border-green-500/30',
            'not_selected' => 'bg-dark-600/20 text-dark-400 border-dark-500/30',
            default => 'bg-blue-600/20 text-blue-400 border-blue-500/30',
        };
    }
}
