<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $attributes = ['status' => 'pending']; // same default as the migration

    protected $fillable = ['order_id', 'reference', 'provider', 'phone', 'amount_fcfa', 'status', 'provider_transaction_id', 'completed_at'];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
