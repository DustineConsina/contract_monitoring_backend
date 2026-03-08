<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsureMigrationsComplete extends Command
{
    protected $signature = 'migrate:ensure';
    protected $description = 'Ensure all critical tables exist';

    public function handle()
    {
        $this->line('=== Ensuring Critical Tables Exist ===');

        try {
            // Explicitly create personal_access_tokens table if it doesn't exist
            if (!Schema::hasTable('personal_access_tokens')) {
                $this->warn('Creating personal_access_tokens table...');
                DB::statement('
                    CREATE TABLE IF NOT EXISTS personal_access_tokens (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        tokenable_type VARCHAR(255) NOT NULL,
                        tokenable_id BIGINT UNSIGNED NOT NULL,
                        name VARCHAR(255) NOT NULL,
                        token VARCHAR(64) NOT NULL UNIQUE,
                        abilities LONGTEXT,
                        last_used_at TIMESTAMP NULL,
                        expires_at TIMESTAMP NULL,
                        created_at TIMESTAMP NULL,
                        updated_at TIMESTAMP NULL,
                        INDEX tokenable (tokenable_type, tokenable_id),
                        INDEX expires_at (expires_at)
                    ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
                ');
                $this->info('✓ personal_access_tokens table created');
            } else {
                $this->info('✓ personal_access_tokens table exists');
            }

            // Check other critical tables
            $criticalTables = [
                'users',
                'password_reset_tokens',
                'tenants',
                'rental_spaces',
                'contracts',
                'payments',
                'chat_messages',
                'audit_logs',
            ];

            foreach ($criticalTables as $table) {
                if (Schema::hasTable($table)) {
                    $this->info("✓ {$table}");
                } else {
                    $this->warn("✗ Missing: {$table}");
                }
            }

            $this->info('✓ Table verification complete');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('EnsureMigrationsComplete error', ['error' => $e->getMessage()]);
            return 1;
        }
    }
}

