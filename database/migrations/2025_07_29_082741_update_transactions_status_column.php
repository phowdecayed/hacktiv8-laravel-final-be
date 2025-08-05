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
        Schema::table('transactions', function (Blueprint $table) {
            // Drop the existing index on status column
            $table->dropIndex(['status']);
            // Drop the existing status column
            $table->dropColumn('status');
        });

        Schema::table('transactions', function (Blueprint $table) {
            // Re-add the status column with the correct enum definition
            $table->enum('status', ['pending', 'processing', 'shipped', 'completed', 'cancelled', 'refunded'])->default('pending')->after('total_amount');
            // Re-add the index on status column
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop the new status column and its index
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });

        Schema::table('transactions', function (Blueprint $table) {
            // Re-add the original status column (assuming the same enum values) and its index
            $table->enum('status', ['pending', 'processing', 'shipped', 'completed', 'cancelled', 'refunded'])->default('pending')->after('total_amount');
            $table->index('status');
        });
    }
};
