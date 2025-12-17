<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceCommand extends Model
{
    protected $table = 'device_commands';

    protected $fillable = [
        'device_id',
        'command_type',
        'command_data',
        'status',
        'sent_at',
        'acknowledged_at',
    ];

    protected $casts = [
        'command_data' => 'array',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(IoTDevice::class, 'device_id');
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsAcknowledged(): void
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
        ]);
    }
}
