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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique(); // e.g., PAY-2024-001
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->date('billing_period_start');
            $table->date('billing_period_end');
            $table->date('due_date');
            $table->decimal('amount_due', 10, 2);
            $table->decimal('interest_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2); // amount_due + interest
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2);
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable(); // cash, check, bank_transfer
            $table->string('reference_number')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
