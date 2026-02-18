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
        Schema::create('rental_spaces', function (Blueprint $table) {
            $table->id();
            $table->string('space_code')->unique(); // e.g., FS-001, MH-001, BW-001
            $table->enum('space_type', ['food_stall', 'market_hall', 'banera_warehouse']);
            $table->string('name'); // e.g., "Food Stall 1", "Market Bay 1"
            $table->decimal('size_sqm', 8, 2); // Size in square meters
            $table->text('description')->nullable();
            $table->text('map_image')->nullable(); // Image path for space map
            $table->decimal('base_rental_rate', 10, 2); // Base rental rate per month
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_spaces');
    }
};
