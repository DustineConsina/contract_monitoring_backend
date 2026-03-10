<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixUserRoleEnum extends Command
{
    protected $signature = 'users:fix-role-enum';
    protected $description = 'Fix the users table role enum to include cashier value. Use this to fix "Data truncated" errors.';

    public function handle()
    {
        $this->info("\n=== Fixing User Role Enum ===\n");

        try {
            // Get the current enum definition from the database
            $result = DB::select("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME='role' AND TABLE_SCHEMA=DATABASE()");
            
            if (empty($result)) {
                $this->error("Could not find role column in users table!");
                return 1;
            }

            $currentType = $result[0]->COLUMN_TYPE;
            $this->line("Current role column type: {$currentType}");

            // Check if 'cashier' is already in the enum
            if (strpos($currentType, 'cashier') !== false) {
                $this->info("✓ 'cashier' is already in the role enum");
                return 0;
            }

            // Add 'cashier' to the enum
            $this->info("Adding 'cashier' to the role enum...");
            
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'staff', 'tenant', 'cashier') DEFAULT 'tenant'");
            
            $this->info("✓ Role enum successfully updated to include 'cashier'");
            $this->line("\nYou can now create the cashier account by running:");
            $this->line("php artisan db:seed --class=AdminUserSeeder");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error fixing role enum: " . $e->getMessage());
            return 1;
        }
    }
}
