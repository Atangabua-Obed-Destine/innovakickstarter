<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// One row each time we ask a customer to pay an order by mobile money.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->uuid('reference')->unique();                  // our id for this request, like MoMo's X-Reference-Id
            $table->string('provider', 20);                       // mtn_momo or orange_money
            $table->string('phone', 15);
            $table->unsignedInteger('amount_fcfa');
            $table->string('status', 20)->default('pending');     // pending, successful, failed
            $table->string('provider_transaction_id')->nullable(); // MoMo's financialTransactionId
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
