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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tenant_code')->unique(); // e.g., TEN-2024-001
            $table->string('business_name');
            $table->string('business_type')->nullable();
            $table->string('tin')->nullable(); // Tax Identification Number
            $table->text('business_address')->nullable();
            $table->string('contact_person');
            $table->string('contact_number');
            $table->string('qr_code')->nullable(); // QR code filename/path
            $table->enum('status', ['active', 'inactive', 'blacklisted'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
