<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// TODO: one row per product in an order.
// Look at 2026_12_01_000001_create_stock_levels_table.php for the patterns you need.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_lines', function (Blueprint $table) {
            $table->id();
            // TODO: order_id: foreignId, constrained, cascadeOnDelete
            // TODO: product_id: foreignId, constrained
            // TODO: quantity, unit_price_fcfa, line_total_fcfa: unsigned integers
            // TODO: timestamps(), and unique [order_id, product_id]
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_lines');
    }
};
