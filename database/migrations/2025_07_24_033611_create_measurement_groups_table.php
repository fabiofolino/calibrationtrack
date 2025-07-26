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
        Schema::create('measurement_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calibration_record_id')->constrained()->onDelete('cascade');
            $table->string('group_number');
            $table->string('group_name');
            $table->enum('type', ['tolerance_percent', 'tolerance_plus_minus', 'limits']);
            $table->decimal('plus_value', 10, 6)->nullable();
            $table->decimal('minus_value', 10, 6)->nullable();
            $table->decimal('mask_min', 15, 6)->nullable();
            $table->decimal('mask_max', 15, 6)->nullable();
            $table->string('units')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('show_sequence')->default(false);
            $table->boolean('show_description')->default(false);
            $table->boolean('auto_fill_after')->default(false);
            $table->boolean('show_uncertainty')->default(false);
            $table->boolean('show_standards')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measurement_groups');
    }
};
