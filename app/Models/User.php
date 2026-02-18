<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the tenant profile for the user.
     */
    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }

    /**
     * Get the sent messages.
     */
    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    /**
     * Get the received messages.
     */
    public function receivedMessages()
    {
        return $this->hasMany(ChatMessage::class, 'receiver_id');
    }

    /**
     * Get the notifications.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the audit logs.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is staff
     */
    public function isStaff()
    {
        return $this->role === 'staff';
    }

    /**
     * Check if user is tenant
     */
    public function isTenant()
    {
        return $this->role === 'tenant';
    }
}
