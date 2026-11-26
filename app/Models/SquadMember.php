<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SquadMember extends Model
{
    protected $fillable = ['squad_id', 'fellow_id', 'pathway'];

    public function squad(): BelongsTo
    {
        return $this->belongsTo(Squad::class);
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }
}
