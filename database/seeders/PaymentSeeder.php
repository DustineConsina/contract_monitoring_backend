<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Contract;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active contracts
        $contracts = Contract::where('status', 'active')->get();

        if ($contracts->isEmpty()) {
            $this->command->warn('No active contracts found. Please create some contracts first.');
            return;
        }

        $year = date('Y');
        $existingCount = Payment::whereYear('created_at', $year)->count();

        foreach ($contracts as $contract) {
            // Create 3 sample payments for each contract
            for ($i = 1; $i <= 3; $i++) {
                $monthlyRental = $contract->monthly_rental ?? 0;
                $interestRate = 0.03;
                $interest = $monthlyRental * $interestRate;
                $totalWithInterest = $monthlyRental + $interest;
                
                // Vary payment status
                $isPaid = $i === 1; // First payment is paid
                $isOverdue = $i === 3; // Third payment is overdue
                
                $amountPaid = $isPaid ? $totalWithInterest : 0;
                $balance = $totalWithInterest - $amountPaid;
                
                $status = $isPaid ? 'paid' : ($isOverdue ? 'overdue' : 'pending');
                
                // Generate unique payment number
                $paymentCount = Payment::whereYear('created_at', $year)->count() + 1 + $existingCount;
                $paymentNumber = 'PAY-' . $year . '-' . str_pad($paymentCount, 6, '0', STR_PAD_LEFT);
                
                $dueDate = Carbon::now()->addMonths($i)->startOfMonth()->addDays(10);
                if ($isOverdue) {
                    $dueDate = Carbon::now()->subMonths(1);
                }

                Payment::create([
                    'payment_number' => $paymentNumber,
                    'contract_id' => $contract->id,
                    'tenant_id' => $contract->tenant_id,
                    'amount_due' => $monthlyRental,
                    'interest_amount' => $interest,
                    'total_amount' => $totalWithInterest,
                    'amount_paid' => $amountPaid,
                    'balance' => $balance,
                    'due_date' => $dueDate,
                    'billing_period_start' => Carbon::now()->startOfMonth(),
                    'billing_period_end' => Carbon::now()->endOfMonth(),
                    'payment_method' => $isPaid ? 'bank_transfer' : null,
                    'payment_date' => $isPaid ? Carbon::now()->subDays(2) : null,
                    'remarks' => $i === 1 ? 'Payment received' : null,
                    'status' => $status,
                ]);
            }
        }

        $this->command->info('Payment records seeded successfully!');
    }
}
