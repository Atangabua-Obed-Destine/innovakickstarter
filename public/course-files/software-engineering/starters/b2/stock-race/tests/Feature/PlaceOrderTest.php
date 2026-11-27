<?php

namespace Tests\Feature;

use App\Exceptions\NotEnoughStock;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Services\PlaceOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Self-checks for "Transactions & Stock Integrity" (one cashier at a time).
class PlaceOrderTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function stockOf(int $productId, string $branch): int
    {
        return (int) StockLevel::where('product_id', $productId)->where('branch_code', $branch)->value('quantity');
    }

    public function test_a_good_order_saves_lines_takes_stock_and_writes_history(): void
    {
        $riceBefore = $this->stockOf(6, 'NKW');
        $order = app(PlaceOrder::class)->handle('NKW', 'Mama Ngozi Provisions', 'mtn_momo', [
            ['product_id' => 6, 'quantity' => 2],
            ['product_id' => 15, 'quantity' => 1],
        ]);

        $this->assertSame(2, $order->lines()->count());
        $this->assertSame((int) $order->lines()->sum('line_total_fcfa'), (int) $order->fresh()->total_fcfa, 'total_fcfa should be the sum of the line totals.');
        $this->assertSame($riceBefore - 2, $this->stockOf(6, 'NKW'), 'Selling 2 bags should take 2 from NKW stock.');
        $this->assertSame(2, StockMovement::where('order_id', $order->id)->where('reason', 'sale')->count(), 'Write one sale movement per line.');
        $this->assertSame(-2, (int) StockMovement::where('order_id', $order->id)->where('product_id', 6)->value('change'), 'The sale movement for 2 bags should have change -2.');
        $this->assertSame('awaiting_payment', $order->status);
    }

    public function test_a_short_line_is_refused_with_the_numbers(): void
    {
        try {
            app(PlaceOrder::class)->handle('ML4', 'Che Divine', 'cash', [['product_id' => 1, 'quantity' => 5]]);
            $this->fail('Selling bread at ML4 should be refused: ML4 has no bread in the seed data.');
        } catch (NotEnoughStock $e) {
            $this->assertSame(1, $e->productId);
            $this->assertSame(5, $e->requested);
            $this->assertSame($this->stockOf(1, 'ML4'), $e->available);
        }
    }

    public function test_all_or_nothing_when_the_second_line_is_short(): void
    {
        $riceBefore = $this->stockOf(6, 'NKW');
        $ordersBefore = Order::count();
        $linesBefore = OrderLine::count();
        $movementsBefore = StockMovement::count();

        try {
            app(PlaceOrder::class)->handle('NKW', 'Ambe Hotel', 'cash', [
                ['product_id' => 6, 'quantity' => 1],    // fine
                ['product_id' => 11, 'quantity' => 1],   // NKW has no detergent in the seed data
            ]);
            $this->fail('The order should be refused: NKW has no detergent.');
        } catch (NotEnoughStock $e) {
            // expected
        }

        $this->assertSame($riceBefore, $this->stockOf(6, 'NKW'), 'The rice line was refused with the order, so its stock must not change. Use a transaction.');
        $this->assertSame($ordersBefore, Order::count(), 'A refused order must not be saved. Use a transaction.');
        $this->assertSame($linesBefore, OrderLine::count(), 'A refused order must not leave order lines behind.');
        $this->assertSame($movementsBefore, StockMovement::count(), 'A refused order must not leave stock movements behind.');
    }
}
