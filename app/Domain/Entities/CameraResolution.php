<?php

namespace App\Domain\Entities;

enum CameraResolution: string
{
    case QVGA = 'QVGA';   // 320x240
    case VGA = 'VGA';     // 640x480
    case SVGA = 'SVGA';   // 800x600
    case XGA = 'XGA';     // 1024x768
    case SXGA = 'SXGA';   // 1280x1024
    case UXGA = 'UXGA';   // 1600x1200

    public function getWidth(): int
    {
        return match($this) {
            self::QVGA => 320,
            self::VGA => 640,
            self::SVGA => 800,
            self::XGA => 1024,
            self::SXGA => 1280,
            self::UXGA => 1600,
        };
    }

    public function getHeight(): int
    {
        return match($this) {
            self::QVGA => 240,
            self::VGA => 480,
            self::SVGA => 600,
            self::XGA => 768,
            self::SXGA => 1024,
            self::UXGA => 1200,
        };
    }

    public function getLabel(): string
    {
        return match($this) {
            self::QVGA => 'QVGA (320x240)',
            self::VGA => 'VGA (640x480)',
            self::SVGA => 'SVGA (800x600)',
            self::XGA => 'XGA (1024x768)',
            self::SXGA => 'SXGA (1280x1024)',
            self::UXGA => 'UXGA (1600x1200)',
        };
    }

    public static function getOptions(): array
    {
        return array_map(
            fn(self $resolution) => [
                'value' => $resolution->value,
                'label' => $resolution->getLabel(),
            ],
            self::cases()
        );
    }
}
