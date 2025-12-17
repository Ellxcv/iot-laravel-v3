<?php

namespace App\Domain\Entities\Device;

use DateTime;

class DeviceEntity
{
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $deviceId = '',
        public string $type = 'sensor',
        public string $status = 'offline',
        public bool $isActive = true,
        public ?DateTime $lastSeen = null,
        public ?DateTime $createdAt = null,
        public ?DateTime $updatedAt = null
    ) {}

    /**
     * Check if device is currently online (has sent data within last 5 minutes)
     */
    public function isOnline(): bool
    {
        if (!$this->lastSeen) {
            return false;
        }
        
        // Device is online if last_seen is within 5 minutes
        $fiveMinutesAgo = new DateTime('-5 minutes');
        return $this->lastSeen > $fiveMinutesAgo;
    }

    /**
     * Check if device is active (enabled)
     */
    public function isEnabled(): bool
    {
        return $this->isActive;
    }

    /**
     * Activate the device
     */
    public function activate(): void
    {
        $this->isActive = true;
    }

    /**
     * Deactivate the device
     */
    public function deactivate(): void
    {
        $this->isActive = false;
    }

    /**
     * Update device status to online
     */
    public function markAsOnline(): void
    {
        $this->status = 'online';
        $this->lastSeen = new DateTime();
    }

    /**
     * Update device status to offline
     */
    public function markAsOffline(): void
    {
        $this->status = 'offline';
    }

    /**
     * Validate device type
     */
    public function isValidType(): bool
    {
        return in_array($this->type, ['sensor', 'camera', 'controller']);
    }

    /**
     * Get device type badge color for UI
     */
    public function getTypeBadgeColor(): string
    {
        return match($this->type) {
            'sensor' => 'blue',
            'camera' => 'purple',
            'controller' => 'green',
            default => 'gray'
        };
    }

    /**
     * Get status badge color for UI
     */
    public function getStatusBadgeColor(): string
    {
        return $this->isOnline() ? 'green' : 'gray';
    }
}
