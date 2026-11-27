<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// TODO: one row per order (your design from "Relationships & Normalization").
// Look at 2026_12_01_000001_create_stock_levels_table.php for the patterns you need.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // TODO: code: text up to 12 characters, unique (the public id, like ORD-7Q2K9X1B)
            // TODO: branch_code: 3 characters, a foreign key to branches.code
            // TODO: customer_name (up to 80), payment_method (up to 20), status (up to 20)
            // TODO: total_fcfa: unsigned integer, default 0
            // TODO: timestamps(), and an index on [branch_code, created_at]
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
