<?php

namespace App\Domain\Repositories;

interface IoTRepositoryInterface
{
    /**
     * Get historical sensor data with filters
     */
    public function getHistoricalData(
        ?int $deviceId,
        ?string $sensorType,
        ?\DateTime $startDate,
        ?\DateTime $endDate,
        int $page,
        int $perPage,
        ?string $dataType = 'sensors',
        ?string $actuatorType = null
    ): array;

    /**
     * Get statistical summary of historical data
     */
    public function getHistoricalDataStats(
        ?int $deviceId,
        ?string $sensorType,
        ?\DateTime $startDate,
        ?\DateTime $endDate,
        ?string $dataType = 'sensors',
        ?string $actuatorType = null
    ): array;

    /**
     * Get all IoT devices
     */
    public function getAllDevices(): array;
}
