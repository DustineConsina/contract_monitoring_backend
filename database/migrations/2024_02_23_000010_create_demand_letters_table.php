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
        Schema::create('demand_letters', function (Blueprint $table) {
            $table->id();
            $table->string('demand_number')->unique();
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->decimal('outstanding_balance', 12, 2);
            $table->decimal('total_amount_demanded', 12, 2);
            $table->date('issued_date');
            $table->date('due_date');
            $table->enum('status', ['issued', 'sent', 'paid', 'cancelled'])->default('issued');
            $table->datetime('sent_date')->nullable();
            $table->string('email_sent_to')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demand_letters');
    }
};
