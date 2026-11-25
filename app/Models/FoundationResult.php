<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoundationResult extends Model
{
    protected $fillable = ['fellow_id', 'area', 'kind', 'item_key', 'score', 'total', 'percent', 'passed', 'details'];

    protected function casts(): array
    {
        return ['passed' => 'boolean', 'details' => 'array', 'percent' => 'float'];
    }
}
