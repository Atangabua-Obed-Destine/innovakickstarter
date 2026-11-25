<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Keys and tokens for outside services (OpenAI, GitHub, email), entered by
 * admins on the Integrations page. Values are encrypted with APP_KEY and
 * override the matching .env entries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->text('value');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_credentials');
    }
};
