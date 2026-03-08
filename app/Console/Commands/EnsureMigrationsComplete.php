<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsureMigrationsComplete extends Command
{
    protected $signature = 'migrate:ensure';
    protected $description = 'Ensure all critical migrations are run';

    public function handle()
    {
        $this->line('=== Ensuring Migrations Complete ===');

        try {
            // Check if migrations table exists
            if (!Schema::hasTable('migrations')) {
                $this->warn('Migrations table missing, running migrations...');
                Artisan::call('migrate', ['--force' => true]);
                $this->info('✓ Migrations completed');
            } else {
                $this->info('✓ Migrations table exists');
            }

            // Ensure critical tables exist
            $criticalTables = [
                'users',
                'password_reset_tokens',
                'personal_access_tokens',
                'tenants',
                'rental_spaces',
                'contracts',
                'payments',
                'audit_logs',
            ];

            foreach ($criticalTables as $table) {
                if (Schema::hasTable($table)) {
                    $this->info("✓ Table exists: {$table}");
                } else {
                    $this->warn("✗ Missing table: {$table}");
                }
            }

            // If personal_access_tokens is missing, try refresh or create manually
            if (!Schema::hasTable('personal_access_tokens')) {
                $this->warn('personal_access_tokens table missing, creating...');
                Schema::create('personal_access_tokens', function ($table) {
                    $table->id();
                    $table->morphs('tokenable');
                    $table->text('name');
                    $table->string('token', 64)->unique();
                    $table->text('abilities')->nullable();
                    $table->timestamp('last_used_at')->nullable();
                    $table->timestamp('expires_at')->nullable()->index();
                    $table->timestamps();
                });
                $this->info('✓ personal_access_tokens table created');
            }

            $this->info('✓ Migration check complete');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
