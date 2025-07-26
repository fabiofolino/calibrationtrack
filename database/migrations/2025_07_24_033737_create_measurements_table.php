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
        Schema::create('measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('measurement_group_id')->constrained()->onDelete('cascade');
            $table->integer('sequence')->default(1);
            $table->decimal('nominal', 15, 6);
            $table->decimal('upper_limit', 15, 6);
            $table->decimal('lower_limit', 15, 6);
            
            // As Found measurements
            $table->decimal('as_found_value', 15, 6)->nullable();
            $table->decimal('as_found_error', 15, 6)->nullable();
            $table->boolean('as_found_pass')->nullable();
            
            // As Left measurements  
            $table->decimal('as_left_value', 15, 6)->nullable();
            $table->decimal('as_left_error', 15, 6)->nullable();
            $table->boolean('as_left_pass')->nullable();
            
            $table->string('description')->nullable();
            $table->decimal('uncertainty', 15, 6)->nullable();
            $table->string('standards_used')->nullable();
            $table->timestamps();
            
            $table->index(['measurement_group_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
};
