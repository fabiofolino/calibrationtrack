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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop the old index and foreign key
            $table->dropIndex(['user_id', 'stripe_status']);
            
            // Drop the user_id column and replace with company_id
            $table->dropColumn('user_id');
            $table->foreignId('company_id')->after('id')->constrained()->onDelete('cascade');
            
            // Add new index
            $table->index(['company_id', 'stripe_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop the company index and foreign key
            $table->dropIndex(['company_id', 'stripe_status']);
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
            
            // Add back user_id
            $table->foreignId('user_id')->after('id');
            $table->index(['user_id', 'stripe_status']);
        });
    }
};
