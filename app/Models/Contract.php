<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'tenant_id',
        'rental_space_id',
        'start_date',
        'end_date',
        'duration_months',
        'monthly_rental',
        'deposit_amount',
        'interest_rate',
        'terms_conditions',
        'contract_file',
        'status',
        'last_notification_sent',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'last_notification_sent' => 'date',
        'monthly_rental' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the contract.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the rental space for the contract.
     */
    public function rentalSpace()
    {
        return $this->belongsTo(RentalSpace::class);
    }

    /**
     * Get the payments for the contract.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the demand letters for the contract.
     */
    public function demandLetters()
    {
        return $this->hasMany(DemandLetter::class);
    }

    /**
     * Get pending payments.
     */
    public function pendingPayments()
    {
        return $this->hasMany(Payment::class)->whereIn('status', ['pending', 'overdue']);
    }

    /**
     * Get overdue payments.
     */
    public function overduePayments()
    {
        return $this->hasMany(Payment::class)->where('status', 'overdue');
    }

    /**
     * Check if contract is active.
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if contract is expired.
     */
    public function isExpired()
    {
        return $this->status === 'expired' || Carbon::now()->isAfter($this->end_date);
    }

    /**
     * Check if contract is for renewal (2 months before expiry).
     */
    public function isForRenewal()
    {
        return $this->status === 'for_renewal';
    }

    /**
     * Check if contract is expiring soon (within 30 days).
     */
    public function isExpiringSoon()
    {
        return $this->end_date->diffInDays(Carbon::now()) <= 30 && $this->end_date->isFuture();
    }

    /**
     * Check if contract needs renewal (2 months before expiry).
     */
    public function needsRenewal()
    {
        $twoMonthsFromNow = Carbon::now()->addMonths(2);
        return $this->status === 'active' && 
               $this->end_date->lte($twoMonthsFromNow) && 
               $this->end_date->gt(Carbon::now());
    }

    /**
     * Get days until expiration.
     */
    public function daysUntilExpiration()
    {
        return Carbon::now()->diffInDays($this->end_date, false);
    }

    /**
     * Calculate total amount paid.
     */
    public function totalPaid()
    {
        return $this->payments()->sum('amount_paid');
    }

    /**
     * Calculate total balance.
     */
    public function totalBalance()
    {
        return $this->payments()->sum('balance');
    }

    /**
     * Generate payment schedules for the contract.
     * Billing periods based on contract start date anniversary.
     */
    public function generatePaymentSchedule()
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $monthCount = 0;

        while ($monthCount < 60) { // Limit to 60 months
            // Period runs from contract anniversary to next anniversary
            $periodStart = $startDate->copy()->addMonths($monthCount);
            $periodEnd = $startDate->copy()->addMonths($monthCount + 1);
            
            // Stop if period goes beyond contract end date
            if ($periodStart->gt($endDate)) {
                break;
            }
            
            if ($periodEnd->gt($endDate)) {
                $periodEnd = $endDate->copy();
            }

            // Check if payment already exists for this period
            $existingPayment = Payment::where('contract_id', $this->id)
                ->whereDate('billing_period_start', $periodStart)
                ->first();
            
            if ($existingPayment) {
                $monthCount++;
                continue; // Skip if already exists
            }

            // Due date is on the contract anniversary (one month from period start)
            $dueDate = $periodEnd->copy();

            // Get the next sequential payment number
            $lastPayment = Payment::orderBy('id', 'desc')->first();
            $nextNumber = ($lastPayment ? intval(substr($lastPayment->payment_number, -6)) : 0) + 1;
            $paymentNumber = 'PAY-' . date('Y') . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // Create payment without due_date (will set separately)
            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'contract_id' => $this->id,
                'tenant_id' => $this->tenant_id,
                'billing_period_start' => $periodStart,
                'billing_period_end' => $periodEnd,
                'amount_due' => $this->monthly_rental,
                'interest_amount' => $this->monthly_rental * 0.03,
                'total_amount' => $this->monthly_rental * 1.03,
                'amount_paid' => 0,
                'balance' => $this->monthly_rental * 1.03,
                'status' => 'pending',
            ]);
            
            // Set due_date directly (since it's protected from mass assignment)
            $payment->due_date = $dueDate;
            $payment->save();

            $monthCount++;
        }
    }
}
