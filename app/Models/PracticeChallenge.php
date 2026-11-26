<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A short self-checking Practice Lab challenge. The expected answer is never sent to the page. */
class PracticeChallenge extends Model
{
    use HasUuids;

    public const KINDS = ['sql' => 'SQL', 'python' => 'Python', 'javascript' => 'JavaScript', 'quiz' => 'Quick question'];

    protected $fillable = [
        'track_id', 'slug', 'title', 'skill', 'kind', 'difficulty', 'xp', 'prompt', 'starter_code', 'setup', 'hint',
        'explanation', 'ordered', 'needs_pandas', 'options', 'answer_index', 'expected_hash', 'sort', 'is_active',
    ];

    protected $hidden = ['answer_index', 'expected_hash', 'explanation'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'ordered' => 'boolean',
            'needs_pandas' => 'boolean',
            'is_active' => 'boolean',
            'difficulty' => 'integer',
            'xp' => 'integer',
            'answer_index' => 'integer',
        ];
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PracticeAttempt::class, 'challenge_id');
    }

    public function kindLabel(): string
    {
        return self::KINDS[$this->kind] ?? ucfirst($this->kind);
    }
}
