<?php

namespace App\Services;

use App\Exceptions\NotEnoughStock;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;

// STARTER VERSION: it works for one cashier, but it has TWO bugs that only show up
// when something goes wrong or when two cashiers sell at the same moment.
// 1. Run the tests and read the failures.
// 2. Fix this class (TODOs below). Do not remove the slowTill() call.
class PlaceOrder
{
    /**
     * Sell the items at one branch: save the order and its lines, take the stock, write the history.
     *
     * @param  array<int, array{product_id:int, quantity:int}>  $items
     */
    public function handle(string $branchCode, string $customerName, string $paymentMethod, array $items): Order
    {
        // TODO 1: all or nothing. If one line is short, nothing at all may be saved.
        // TODO 2: two cashiers must never sell the same last bag. Lock the stock rows you read.
        //         Lock them in the same order every time (sort by product_id).

        $order = Order::create([
            'branch_code' => $branchCode,
            'customer_name' => $customerName,
            'payment_method' => $paymentMethod,
            'status' => $paymentMethod === 'cash' ? 'pay_at_counter' : 'awaiting_payment',
        ]);

        $total = 0;
        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $quantity = (int) $item['quantity'];

            $level = StockLevel::where('branch_code', $branchCode)
                ->where('product_id', $product->id)
                ->first();
            $available = $level?->quantity ?? 0;

            if ($quantity > $available) {
                throw new NotEnoughStock($product->id, $quantity, $available);
            }

            $this->slowTill();

            $level->quantity = $available - $quantity;
            $level->save();

            $order->lines()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price_fcfa' => $product->price_fcfa,
                'line_total_fcfa' => $product->price_fcfa * $quantity,
            ]);

            StockMovement::create([
                'product_id' => $product->id,
                'branch_code' => $branchCode,
                'change' => -$quantity,
                'reason' => 'sale',
                'order_id' => $order->id,
                'balance_after' => $level->quantity,
            ]);
            $total += $product->price_fcfa * $quantity;
        }

        $order->update(['total_fcfa' => $total]);

        return $order->load('lines.product');
    }

    // A real till is slow (receipt printer, bad network). Keep this call between the check and the write.
    private function slowTill(): void
    {
        $ms = (int) config('nkwen.slow_till_ms', 0);
        if ($ms > 0) {
            usleep($ms * 1000);
        }
    }
}
