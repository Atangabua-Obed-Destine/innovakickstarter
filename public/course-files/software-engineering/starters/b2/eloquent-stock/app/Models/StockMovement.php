<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    const UPDATED_AT = null; // history rows are never edited, so there is no updated_at column

    protected $fillable = ['product_id', 'branch_code', 'change', 'reason', 'order_id', 'balance_after', 'note'];

    // TODO: product(), branch() and order(): each movement belongs to one of each (order may be null).
}
