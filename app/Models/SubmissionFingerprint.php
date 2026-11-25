<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fingerprint of a submission's text (hashed 5-word shingles), used to find
 * copying between fellows. One row per submission attempt.
 */
class SubmissionFingerprint extends Model
{
    protected $fillable = [
        'progress_id', 'curriculum_activity_id', 'fellow_id', 'attempt_number',
        'kind', 'hashes', 'shingle_count', 'repo',
    ];

    protected function casts(): array
    {
        return [
            'hashes' => 'array',
            'shingle_count' => 'integer',
        ];
    }

    public function progress(): BelongsTo
    {
        return $this->belongsTo(FellowCurriculumProgress::class, 'progress_id');
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }
}
