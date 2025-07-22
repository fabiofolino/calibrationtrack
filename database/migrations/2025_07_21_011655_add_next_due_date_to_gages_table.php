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
            $table->date('next_due_date')->nullable()->after('frequency_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gages', function (Blueprint $table) {
            $table->dropColumn('next_due_date');
        });
    }
};
