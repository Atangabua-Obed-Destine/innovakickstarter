<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A fellow's Leitner box and due date for one card. */
class FlashReview extends Model
{
    protected $fillable = ['fellow_id', 'card_id', 'box', 'due_on', 'last_reviewed_on', 'times_right', 'times_wrong'];

    protected function casts(): array
    {
        return ['due_on' => 'date', 'last_reviewed_on' => 'date'];
    }
}
