<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model.
     */
    public function auditable()
    {
        return $this->morphTo('model');
    }

    /**
     * Create an audit log entry.
     * Prevents duplicate entries from being created within 2 seconds.
     */
    public static function log($action, $modelType, $modelId, $description, $oldValues = null, $newValues = null)
    {
        $userId = auth()->id();
        
        // Check if an identical entry was created in the last 2 seconds
        $recentDuplicate = static::where('user_id', $userId)
            ->where('action', $action)
            ->where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->where('description', $description)
            ->where('created_at', '>=', now()->subSeconds(2))
            ->exists();
        
        // Skip if duplicate was found
        if ($recentDuplicate) {
            return null;
        }
        
        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
