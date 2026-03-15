<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalSpace extends Model
{
    use HasFactory;

    protected $fillable = [
        'space_code',
        'space_type',
        'name',
        'size_sqm',
        'description',
        'map_image',
        'base_rental_rate',
        'status',
    ];

    protected $casts = [
        'size_sqm' => 'decimal:2',
        'base_rental_rate' => 'decimal:2',
    ];

    /**
     * Get the contracts for this rental space.
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Get the active contract for this rental space.
     */
    public function activeContract()
    {
        return $this->hasOne(Contract::class)->where('status', 'active');
    }

    /**
     * Get the current tenant.
     */
    public function currentTenant()
    {
        return $this->hasOneThrough(
            Tenant::class,
            Contract::class,
            'rental_space_id',
            'id',
            'id',
            'tenant_id'
        )->where('contracts.status', 'active');
    }

    /**
     * Check if space is available.
     */
    public function isAvailable()
    {
        return !$this->activeContract()->exists();
    }

    /**
     * Scope: Only available rental spaces (without active or pending contracts)
     */
    public function scopeAvailable($query)
    {
        return $query->whereDoesntHave('contracts', function ($q) {
            $q->whereIn('status', ['active', 'pending']);
        });
    }

    /**
     * Scope: Only occupied rental spaces (with active contracts)
     */
    public function scopeOccupied($query)
    {
        return $query->whereHas('contracts', function ($q) {
            $q->where('status', 'active');
        });
    }

    /**
     * Get the space type label.
     */
    public function getSpaceTypeLabel()
    {
        return match($this->space_type) {
            'food_stall' => 'Food Stall',
            'market_hall' => 'Market Hall',
            'banera_warehouse' => 'Bañera Warehouse',
            default => $this->space_type,
        };
    }
}
