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
        // Update all existing contracts to have 3% interest rate
        DB::table('contracts')->update(['interest_rate' => 3.00]);
        
        // Update the default value for new contracts
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('interest_rate', 5, 2)->default(3.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert all contracts to 0% interest rate
        DB::table('contracts')->update(['interest_rate' => 0]);
        
        // Revert the default value
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('interest_rate', 5, 2)->default(0)->change();
        });
    }
};
