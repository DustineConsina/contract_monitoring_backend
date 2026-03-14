<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'for_renewal' status to contracts table
        Schema::table('contracts', function (Blueprint $table) {
            // For MySQL, we need to drop and recreate the enum with the new value
            // This is a workaround as Laravel doesn't support enum modification
        });

        // Using raw SQL to modify the enum
        DB::statement("ALTER TABLE contracts MODIFY status ENUM('active', 'expired', 'terminated', 'pending', 'for_renewal') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum to original values
        DB::statement("ALTER TABLE contracts MODIFY status ENUM('active', 'expired', 'terminated', 'pending') DEFAULT 'pending'");
    }
};
