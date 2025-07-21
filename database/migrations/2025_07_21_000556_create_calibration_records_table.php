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
        Schema::create('calibration_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gage_id')->constrained()->onDelete('cascade');
            $table->decimal('measured_value', 10, 4);
            $table->decimal('calibrated_value', 10, 4);
            $table->timestamp('calibrated_at');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calibration_records');
    }
};
