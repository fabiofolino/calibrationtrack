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
        Schema::create('gage_tolerance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gage_id')->constrained()->onDelete('cascade');
            $table->foreignId('calibration_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['out_of_tolerance', 'under_review', 'resolved'])->default('out_of_tolerance');
            $table->text('risk_assessment');
            $table->text('corrective_actions');
            $table->timestamp('identified_at');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gage_tolerance_records');
    }
};
