<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CameraImage extends Model
{
    protected $fillable = [
        'device_id',
        'filename',
        'path',
        'size',
        'width',
        'height',
        'format',
        'thumbnail_path',
        'captured_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(IoTDevice::class, 'device_id', 'device_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail_path
            ? asset('storage/' . $this->thumbnail_path)
            : $this->url;
    }
}
