<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Internship attestations.
 *
 * One row per issued (or draft) attestation version for an internship.
 * The `snapshot` column freezes everything printed on the document so that
 * later score changes never alter an attestation that has been issued.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_attestations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('Public verification token (QR target)');
            $table->string('reference', 30)->nullable()->unique()->comment('IKS-ATT-YYYY-NNNN, assigned at issue');
            $table->string('verification_code', 12)->nullable()->comment('Short code printed on the document');

            $table->foreignId('internship_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('track_id')->nullable();

            $table->string('status', 20)->default('draft')->comment('draft, issued, revoked, superseded');
            $table->unsignedSmallInteger('version')->default(1);
            $table->foreignId('supersedes_id')->nullable()->constrained('internship_attestations')->nullOnDelete();

            $table->string('mention', 20)->nullable()->comment('excellent, very_good, good, satisfactory');
            $table->string('suggested_mention', 20)->nullable();
            $table->text('remarks_en')->nullable();
            $table->text('remarks_fr')->nullable();

            $table->json('snapshot')->nullable()->comment('Frozen record printed on the document');
            $table->string('content_hash', 64)->nullable()->comment('SHA-256 of reference + snapshot');

            $table->string('signatory_name')->nullable();
            $table->string('signatory_title')->nullable();
            $table->string('pdf_path', 500)->nullable();

            $table->timestamp('issued_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('revocation_reason')->nullable();

            $table->timestamps();

            $table->index(['internship_profile_id', 'status']);
            $table->index(['fellow_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_attestations');
    }
};
