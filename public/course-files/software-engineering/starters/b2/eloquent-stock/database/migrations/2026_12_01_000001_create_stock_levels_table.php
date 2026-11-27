<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// One row per product per branch: "how many of this product does this branch have now?"
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();                     // must point at a real product
            $table->string('branch_code', 3);
            $table->foreign('branch_code')->references('code')->on('branches'); // must point at a real branch
            $table->unsignedInteger('quantity')->default(0);                   // unsigned: can never go below 0
            $table->timestamps();
            $table->unique(['product_id', 'branch_code']);                     // one row per product per branch
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_levels');
    }
};
