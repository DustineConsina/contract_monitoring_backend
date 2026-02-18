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
     * Check if contract is expiring soon (within 30 days).
     */
    public function isExpiringSoon()
    {
        return $this->end_date->diffInDays(Carbon::now()) <= 30 && $this->end_date->isFuture();
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
     */
    public function generatePaymentSchedule()
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $periodStart = $currentDate->copy();
            $periodEnd = $currentDate->copy()->endOfMonth();
            
            if ($periodEnd->gt($endDate)) {
                $periodEnd = $endDate;
            }

            $dueDate = $periodEnd->copy()->addDays(5); // Due 5 days after period end

            Payment::create([
                'payment_number' => 'PAY-' . date('Y') . '-' . str_pad(Payment::count() + 1, 6, '0', STR_PAD_LEFT),
                'contract_id' => $this->id,
                'tenant_id' => $this->tenant_id,
                'billing_period_start' => $periodStart,
                'billing_period_end' => $periodEnd,
                'due_date' => $dueDate,
                'amount_due' => $this->monthly_rental,
                'interest_amount' => 0,
                'total_amount' => $this->monthly_rental,
                'amount_paid' => 0,
                'balance' => $this->monthly_rental,
                'status' => 'pending',
            ]);

            $currentDate->addMonth();
        }
    }
}
