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
        Schema::table('gages', function (Blueprint $table) {
            // Add new required fields
            $table->string('gage_id')->after('id');
            $table->string('location')->after('serial_number');
            $table->string('custodian')->after('location');
            
            // Add new optional fields
            $table->string('model')->nullable()->after('gage_id');
            $table->string('manufacturer')->nullable()->after('model');
            
            // Rename 'name' to 'description'
            $table->renameColumn('name', 'description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gages', function (Blueprint $table) {
            // Remove new fields
            $table->dropColumn(['gage_id', 'model', 'manufacturer', 'location', 'custodian']);
            
            // Rename back to 'name'
            $table->renameColumn('description', 'name');
        });
    }
};
