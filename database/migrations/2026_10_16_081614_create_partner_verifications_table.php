<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('partner_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_profile_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('id_number', 32);
            $table->string('id_photo_path');
            $table->string('selfie_photo_path');
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_verifications');
    }
};
