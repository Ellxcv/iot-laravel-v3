<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\CameraDevice;

interface CameraRepositoryInterface
{
    public function findByDeviceId(string $deviceId): ?CameraDevice;
    
    public function findAll(): array;
    
    public function save(CameraDevice $camera): CameraDevice;
    
    public function deviceIdExists(string $deviceId): bool;
}
