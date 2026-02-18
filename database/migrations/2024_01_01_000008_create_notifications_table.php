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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // payment_due, contract_expiry, payment_overdue
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data (contract_id, payment_id, etc.)
            $table->boolean('is_read')->default(false);
            $table->boolean('email_sent')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
