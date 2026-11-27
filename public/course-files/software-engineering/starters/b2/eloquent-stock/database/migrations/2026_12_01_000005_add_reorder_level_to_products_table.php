<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// A new migration that changes an existing table. Never edit an old migration that has already run.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // At or below this many in a branch it is time to RESTOCK; up to twice this many is LOW (Restock Alerts, Chapter 1).
            $table->unsignedInteger('reorder_level')->default(0)->after('price_fcfa');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('reorder_level');
        });
    }
};
