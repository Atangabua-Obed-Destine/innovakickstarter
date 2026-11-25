<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackFeedKudo extends Model
{
    protected $table = 'track_feed_kudos';

    protected $fillable = ['event_id', 'fellow_id'];
}
