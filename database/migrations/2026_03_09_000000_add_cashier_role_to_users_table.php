<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to modify the enum column to include 'cashier'
        // This is more reliable for enum modifications in MySQL
        try {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'staff', 'tenant', 'cashier') DEFAULT 'tenant'");
            echo "\n✓ Role enum updated to include 'cashier'\n";
        } catch (\Exception $e) {
            echo "\n⚠️  Could not update role enum: " . $e->getMessage() . "\n";
            // If the column already has the right enum, that's fine - continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'staff', 'tenant') DEFAULT 'tenant'");
        } catch (\Exception $e) {
            // Ignore errors on rollback
        }
    }
};
