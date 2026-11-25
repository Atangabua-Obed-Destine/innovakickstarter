<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorMessage extends Model
{
    protected $fillable = ['fellow_id', 'track_id', 'context_type', 'context_id', 'role', 'content', 'model'];
}
