<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FellowActivityPeerReview extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'fellow_activity_peer_reviews';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'progress_id',
        'reviewer_id',
        'status', // pending, completed, bypassed
        'score',
        'notes',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'score' => 'integer',
        ];
    }

    public function progress(): BelongsTo
    {
        return $this->belongsTo(FellowCurriculumProgress::class, 'progress_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
