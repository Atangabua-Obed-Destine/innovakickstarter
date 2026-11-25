<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * API contract for backend projects: a list of HTTP checks IKS runs against the
 * fellow's deployed API, whatever language it is written in (Laravel, Django, Express...).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('track_curriculum_activities', function (Blueprint $table) {
            $table->json('api_contract')->nullable()->after('pathways');
        });
    }

    public function down(): void
    {
        Schema::table('track_curriculum_activities', fn (Blueprint $t) => $t->dropColumn('api_contract'));
    }
};
