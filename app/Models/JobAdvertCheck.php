<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A fellow compared themselves with a real job advert ("You vs a job advert"). */
class JobAdvertCheck extends Model
{
    protected $fillable = ['fellow_id', 'track_id', 'title', 'company', 'match', 'result'];

    protected function casts(): array
    {
        return ['result' => 'array'];
    }
}
