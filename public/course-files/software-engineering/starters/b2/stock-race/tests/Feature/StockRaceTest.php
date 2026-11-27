<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

// Self-check for "Transactions & Stock Integrity": six cashiers, three bags, all at the same moment.
// No RefreshDatabase here: the six cashiers are separate PHP processes, so they can only see
// rows we have really saved (committed). We clean up our own rows at the end.
class StockRaceTest extends TestCase
{
    private const CASHIERS = 6;

    private const BAGS = 3;

    private ?int $productId = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'])) {
            $this->markTestSkipped('The race test needs MySQL. SQLite locks the whole file, so it cannot show this race. Point phpunit.xml at nkwen_api_test.');
        }

        Artisan::call('migrate', ['--force' => true]);
        Branch::firstOrCreate(['code' => 'NKW'], [
            'name' => 'Nkwen', 'city' => 'Bamenda', 'region' => 'North-West',
            'manager' => 'Mrs. Enow Patience', 'opened_year' => 2019, 'status' => 'open',
        ]);
        $product = Product::create([
            'sku' => 'RACE-'.uniqid(), 'name' => 'Race Test Rice 50kg',
            'category' => 'Grains', 'unit' => 'bag', 'price_fcfa' => 36005,
        ]);
        $this->productId = $product->id;
        StockLevel::create(['product_id' => $product->id, 'branch_code' => 'NKW', 'quantity' => self::BAGS]);
    }

    protected function tearDown(): void
    {
        if ($this->productId) {
            $orderIds = OrderLine::where('product_id', $this->productId)->pluck('order_id');
            StockMovement::where('product_id', $this->productId)->delete();
            Order::whereIn('id', $orderIds)->delete(); // order_lines go too (cascade)
            StockLevel::where('product_id', $this->productId)->delete();
            Product::whereKey($this->productId)->delete();
        }
        parent::tearDown();
    }

    public function test_six_cashiers_cannot_sell_more_bags_than_the_shop_has(): void
    {
        $connection = config('database.default');
        $env = [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => $connection,
            'DB_DATABASE' => config("database.connections.{$connection}.database"),
            'NKWEN_SLOW_TILL_MS' => '300', // each till takes 0.3 s between checking and saving
        ];

        $whistle = microtime(true) + 4; // every cashier starts at the same moment
        $cashiers = [];
        for ($i = 0; $i < self::CASHIERS; $i++) {
            $p = new Process([PHP_BINARY, 'artisan', 'nkwen:sell', 'NKW', (string) $this->productId, '1', '--start-at='.$whistle], base_path(), $env);
            $p->setTimeout(120);
            $p->start();
            $cashiers[] = $p;
        }

        $sold = 0;
        $refused = 0;
        $crashed = [];
        foreach ($cashiers as $p) {
            $p->wait();
            match ($p->getExitCode()) {
                0 => $sold++,
                2 => $refused++,
                default => $crashed[] = trim($p->getOutput()."\n".$p->getErrorOutput()),
            };
        }

        $this->assertSame([], $crashed, "Some cashiers crashed:\n".implode("\n---\n", $crashed));

        $left = (int) StockLevel::where('product_id', $this->productId)->value('quantity');
        $unitsOnOrders = (int) OrderLine::where('product_id', $this->productId)->sum('quantity');

        $this->assertSame(self::BAGS, $sold,
            "{$sold} cashiers were told SOLD, but the shop had only ".self::BAGS." bags. This is the race condition: "
            .'everyone read the stock before anyone saved. Lock the stock row inside a transaction.');
        $this->assertSame(self::CASHIERS - self::BAGS, $refused, 'The other cashiers should be refused (not enough stock).');
        $this->assertSame(0, $left, 'All the bags were sold, so the stock should be 0.');
        $this->assertSame(self::BAGS, $unitsOnOrders, "The orders contain {$unitsOnOrders} bags; the shop only had ".self::BAGS.'.');
    }
}
