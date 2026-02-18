<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the sender of the message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the message.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Mark message as read.
     */
    public function markAsRead()
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }

    /**
     * Scope a query to only include unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query for messages between two users.
     */
    public function scopeBetweenUsers($query, $user1Id, $user2Id)
    {
        return $query->where(function ($q) use ($user1Id, $user2Id) {
            $q->where('sender_id', $user1Id)->where('receiver_id', $user2Id);
        })->orWhere(function ($q) use ($user1Id, $user2Id) {
            $q->where('sender_id', $user2Id)->where('receiver_id', $user1Id);
        });
    }
}
