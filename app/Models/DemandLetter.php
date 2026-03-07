<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DemandLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'demand_number',
        'contract_id',
        'tenant_id',
        'payment_id',
        'outstanding_balance',
        'total_amount_demanded',
        'issued_date',
        'due_date',
        'status',
        'sent_date',
        'email_sent_to',
        'remarks',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'due_date' => 'date',
        'sent_date' => 'date',
        'outstanding_balance' => 'decimal:2',
        'total_amount_demanded' => 'decimal:2',
    ];

    /**
     * Get the contract associated with the demand letter.
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Get the tenant associated with the demand letter.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the payment associated with the demand letter.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Check if demand letter is active.
     */
    public function isActive()
    {
        return $this->status === 'issued' || $this->status === 'sent';
    }

    /**
     * Check if demand letter is overdue.
     */
    public function isDueDatePassed()
    {
        return Carbon::now()->isAfter($this->due_date);
    }
}
