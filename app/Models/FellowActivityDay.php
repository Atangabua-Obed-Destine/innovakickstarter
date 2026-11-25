<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** What a fellow actually did on a day: counts per kind (step, submit, review, practice, typing, mentor...). */
class FellowActivityDay extends Model
{
    protected $fillable = ['fellow_id', 'day', 'kinds'];

    protected function casts(): array
    {
        return ['day' => 'date', 'kinds' => 'array'];
    }
}
