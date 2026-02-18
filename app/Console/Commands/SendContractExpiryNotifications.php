<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use App\Models\Notification;
use App\Mail\ContractExpiryMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendContractExpiryNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:send-expiry-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send contract expiry notifications to tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending contract expiry notifications...');

        // Get contracts expiring in the next 30 days
        $expiringContracts = Contract::with(['tenant.user', 'rentalSpace'])
            ->where('status', 'active')
            ->where('end_date', '>=', Carbon::now())
            ->where('end_date', '<=', Carbon::now()->addDays(30))
            ->get();

        $count = 0;
        foreach ($expiringContracts as $contract) {
            $daysUntilExpiry = $contract->daysUntilExpiration();
            
            // Send notification at 30, 14, and 7 days before expiry
            if (in_array($daysUntilExpiry, [30, 14, 7, 3, 1])) {
                // Check if notification already sent today
                $notificationSentToday = $contract->last_notification_sent && 
                    $contract->last_notification_sent->isToday();

                if (!$notificationSentToday) {
                    $notification = Notification::create([
                        'user_id' => $contract->tenant->user_id,
                        'type' => 'contract_expiry',
                        'title' => 'Contract Expiring Soon',
                        'message' => "Your contract {$contract->contract_number} for {$contract->rentalSpace->name} will expire in {$daysUntilExpiry} days. Please contact us for renewal.",
                        'data' => ['contract_id' => $contract->id, 'days_until_expiry' => $daysUntilExpiry],
                    ]);

                    // Send email
                    try {
                        if ($contract->tenant->user->email) {
                            Mail::to($contract->tenant->user->email)->send(new ContractExpiryMail($contract, $daysUntilExpiry));
                            $notification->markEmailAsSent();
                            $count++;
                        }
                    } catch (\Exception $e) {
                        $this->error("Failed to send email to {$contract->tenant->user->email}: " . $e->getMessage());
                    }

                    // Update last notification sent date
                    $contract->last_notification_sent = Carbon::now();
                    $contract->save();
                }
            }
        }

        $this->info("Sent {$count} contract expiry notifications");
        $this->info('Contract expiry notifications completed!');

        return Command::SUCCESS;
    }
}
