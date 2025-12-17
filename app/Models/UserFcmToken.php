<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFcmToken extends Model
{
    protected $fillable = [
        'user_id',
        'fcm_token',
        'token_hash',
        'device_name',
        'device_type',
        'user_agent',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($token) {
            // Auto-generate hash from token
            $token->token_hash = hash('sha256', $token->fcm_token);
        });
    }

    /**
     * Get the user that owns the token
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if token is stale (not used in X days)
     */
    public function isStale(int $days = 30): bool
    {
        if (!$this->last_used_at) {
            return true;
        }

        return $this->last_used_at->diffInDays(now()) > $days;
    }

    /**
     * Update last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
