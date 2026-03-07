<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\DemandLetter;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDemandLetters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:generate-demand-letters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send demand letters for overdue payments (5 days after due date)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for overdue payments that need demand letters...');

        // Find payments that are overdue and haven't had a demand letter yet
        $overduePayments = Payment::where('status', 'overdue')
            ->where('balance', '>', 0)
            ->with(['contract', 'tenant'])
            ->get()
            ->filter(function ($payment) {
                // Only generate if no active demand letter already exists
                return !DemandLetter::where('payment_id', $payment->id)
                    ->whereIn('status', ['issued', 'sent'])
                    ->exists();
            });

        if ($overduePayments->isEmpty()) {
            $this->info('No overdue payments requiring demand letters.');
            return 0;
        }

        $demandLettersCreated = 0;

        foreach ($overduePayments as $payment) {
            // Generate unique demand number
            $demandNumber = 'DL-' . date('Ymd') . '-' . $payment->id . '-' . str_pad(DemandLetter::count() + 1, 4, '0', STR_PAD_LEFT);

            // Create demand letter
            $demandLetter = DemandLetter::create([
                'demand_number' => $demandNumber,
                'contract_id' => $payment->contract_id,
                'tenant_id' => $payment->tenant_id,
                'payment_id' => $payment->id,
                'outstanding_balance' => $payment->balance,
                'total_amount_demanded' => $payment->total_amount,
                'issued_date' => Carbon::now(),
                'due_date' => Carbon::now()->addDays(5), // 5 days to settle from demand letter issue
                'status' => 'issued',
                'email_sent_to' => $payment->tenant->email ?? null,
            ]);

            $this->info("Demand letter {$demandNumber} created for {$payment->tenant->contact_person}. PDF is downloadable.");

            $demandLettersCreated++;
        }

        $this->info("Demand letter generation complete. Total created: {$demandLettersCreated}");
        return 0;
    }

    /**
     * Send demand letter email to tenant.
     */
    private function sendDemandLetterEmail($payment, $demandLetter)
    {
        $tenant = $payment->tenant;
        $contract = $payment->contract;

        $emailContent = "
Dear {$tenant->name},

This is to formally notify you that your rental payment for the period {$payment->billing_period_start} to {$payment->billing_period_end} is now overdue.

DETAILS:
- Contract Number: {$contract->contract_number}
- Property: {$contract->rentalSpace->name}
- Outstanding Balance: {$demandLetter->outstanding_balance}
- Total Amount Due: {$demandLetter->total_amount_demanded}
- Due Date: {$demandLetter->due_date->format('F d, Y')}

Please settle your outstanding balance within 7 days from the date of this letter. Failure to comply may result in further action.

If you have already made payment, please disregard this notice.

Regards,
Philippine Fisheries Development Authority (PFDA)
";

        // For now, log the email. In production, use Mail::send()
        \Log::info("Demand Letter Email to {$tenant->email}: {$emailContent}");
    }
}
