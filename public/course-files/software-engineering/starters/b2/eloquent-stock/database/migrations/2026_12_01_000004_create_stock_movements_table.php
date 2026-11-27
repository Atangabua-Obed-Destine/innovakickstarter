<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// TODO: the stock history: one row for every change. Rows are added, never edited.
// Look at 2026_12_01_000001_create_stock_levels_table.php for the patterns you need.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            // TODO: product_id (foreignId, constrained) and branch_code (foreign key to branches.code)
            // TODO: change: a SIGNED integer (+10 for a delivery, -2 for a sale)
            // TODO: reason: text up to 20 (opening_stock, sale, restock, cancel_return)
            // TODO: order_id: foreignId, nullable, constrained, nullOnDelete
            // TODO: balance_after: unsigned integer; note: nullable text
            // TODO: created_at only: $table->timestamp('created_at')->useCurrent();
            // TODO: an index on [branch_code, product_id]
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
