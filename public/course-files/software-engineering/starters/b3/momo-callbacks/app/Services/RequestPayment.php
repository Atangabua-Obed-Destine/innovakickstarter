<?php

namespace App\Services;

use App\Exceptions\OrderNotAwaitingPayment;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;

class RequestPayment
{
    // Ask the customer to approve a mobile-money payment for this order.
    public function handle(Order $order, string $phone): Payment
    {
        if (! in_array($order->payment_method, ['mtn_momo', 'orange_money']) || $order->status !== 'awaiting_payment') {
            throw new OrderNotAwaitingPayment($order->status);
        }

        // Asking twice (for example after a network error) gives back the same pending request.
        $pending = Payment::where('order_id', $order->id)->where('status', 'pending')->first();
        if ($pending) {
            return $pending;
        }

        // In real life we would now call MTN's "request to pay" API with
        // X-Reference-Id = this reference and our callback URL. Here we only simulate it.
        return Payment::create([
            'order_id' => $order->id,
            'reference' => (string) Str::uuid(), // like MoMo's X-Reference-Id: a UUID we choose
            'provider' => $order->payment_method,
            'phone' => $phone,
            'amount_fcfa' => $order->total_fcfa,
        ]);
    }
}
