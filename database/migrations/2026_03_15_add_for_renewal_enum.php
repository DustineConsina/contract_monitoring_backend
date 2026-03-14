<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE contracts MODIFY status ENUM('active', 'expired', 'terminated', 'pending', 'for_renewal') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE contracts MODIFY status ENUM('active', 'expired', 'terminated', 'pending') DEFAULT 'pending'");
    }
};
