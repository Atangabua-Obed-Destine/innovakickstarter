<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLine extends Model
{
    protected $fillable = ['order_id', 'product_id', 'quantity', 'unit_price_fcfa', 'line_total_fcfa'];

    // TODO: order(): a line belongs to an Order.
    // TODO: product(): a line belongs to a Product.
}
