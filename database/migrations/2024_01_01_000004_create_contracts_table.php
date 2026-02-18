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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique(); // e.g., CON-2024-001
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('rental_space_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_months'); // Contract duration in months
            $table->decimal('monthly_rental', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('interest_rate', 5, 2)->default(0); // Monthly interest rate for late payments
            $table->text('terms_conditions')->nullable();
            $table->string('contract_file')->nullable(); // Scanned contract document
            $table->enum('status', ['active', 'expired', 'terminated', 'pending'])->default('pending');
            $table->date('last_notification_sent')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
