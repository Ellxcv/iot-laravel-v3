<?php

namespace App\Domain\Entities;

enum DeviceStatus: string
{
    case ONLINE = 'online';
    case OFFLINE = 'offline';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case MAINTENANCE = 'maintenance';
    case ERROR = 'error';

    public function label(): string
    {
        return match($this) {
            self::ONLINE => 'Online',
            self::OFFLINE => 'Offline',
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::MAINTENANCE => 'Maintenance',
            self::ERROR => 'Error',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::ONLINE => 'bg-green-500/20 text-green-100',
            self::OFFLINE => 'bg-red-500/20 text-red-100',
            self::ACTIVE => 'bg-blue-500/20 text-blue-100',
            self::INACTIVE => 'bg-gray-500/20 text-gray-100',
            self::MAINTENANCE => 'bg-yellow-500/20 text-yellow-100',
            self::ERROR => 'bg-orange-500/20 text-orange-100',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
