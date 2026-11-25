<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportBuddy extends Model
{
    protected $fillable = ['fellow_id', 'buddy_id', 'assigned_by', 'note', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    public function buddy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buddy_id');
    }
}
