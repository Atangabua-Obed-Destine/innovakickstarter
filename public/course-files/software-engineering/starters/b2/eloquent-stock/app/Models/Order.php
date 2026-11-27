<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = ['code', 'branch_code', 'customer_name', 'payment_method', 'status', 'total_fcfa'];

    // Give every new order a public code like ORD-7Q2K9X1B before it is saved.
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->code ??= 'ORD-'.Str::upper(Str::random(8));
        });
    }

    // TODO: branch(): an order belongs to a branch. Branches are linked by code, not id:
    //       return $this->belongsTo(Branch::class, 'branch_code', 'code');

    // TODO: lines(): an order has many OrderLine rows.
}
