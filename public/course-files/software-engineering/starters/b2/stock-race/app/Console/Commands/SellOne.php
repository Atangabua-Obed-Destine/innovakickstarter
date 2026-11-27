<?php

namespace App\Console\Commands;

use App\Exceptions\NotEnoughStock;
use App\Services\PlaceOrder;
use Illuminate\Console\Command;

// One "cashier" selling one product. The stock race test starts several at the same moment.
class SellOne extends Command
{
    protected $signature = 'nkwen:sell {branch} {product} {quantity=1}
        {--start-at= : Unix time in seconds to wait for, like runners waiting for the whistle}';

    protected $description = 'Sell one product at one branch (used by the stock race test)';

    public function handle(PlaceOrder $placeOrder): int
    {
        $startAt = (float) $this->option('start-at');
        while ($startAt > 0 && microtime(true) < $startAt) {
            usleep(2000);
        }

        try {
            $order = $placeOrder->handle($this->argument('branch'), 'Race test customer', 'cash', [
                ['product_id' => (int) $this->argument('product'), 'quantity' => (int) $this->argument('quantity')],
            ]);
            $this->line("SOLD: order {$order->id}");

            return 0;
        } catch (NotEnoughStock $e) {
            $this->line("REFUSED: only {$e->available} left");

            return 2;
        }
    }
}
