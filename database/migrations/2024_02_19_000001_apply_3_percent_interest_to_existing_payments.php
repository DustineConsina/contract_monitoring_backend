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
        // Update all payments with no interest to apply 3% interest
        $payments = DB::table('payments')
            ->whereNull('interest_amount')
            ->orWhere('interest_amount', 0)
            ->get();

        foreach ($payments as $payment) {
            $interestAmount = $payment->amount_due * 0.03;
            $totalAmount = $payment->amount_due + $interestAmount;
            $balance = $totalAmount - $payment->amount_paid;

            DB::table('payments')
                ->where('id', $payment->id)
                ->update([
                    'interest_amount' => $interestAmount,
                    'total_amount' => $totalAmount,
                    'balance' => $balance,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert interest amounts back to 0
        DB::table('payments')
            ->update([
                'interest_amount' => 0,
            ]);
    }
};
