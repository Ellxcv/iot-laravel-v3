<?php

namespace App\Application\DTOs;

class HistoricalDataQueryDTO
{
    public function __construct(
        public readonly ?int $deviceId = null,
        public readonly ?string $sensorType = null,
        public readonly ?string $dataType = 'sensors',
        public readonly ?string $actuatorType = null,
        public readonly ?\DateTime $startDate = null,
        public readonly ?\DateTime $endDate = null,
        public readonly int $page = 1,
        public readonly int $perPage = 50,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            deviceId: isset($data['device_id']) ? (int)$data['device_id'] : null,
            sensorType: $data['sensor_type'] ?? null,
            dataType: $data['data_type'] ?? 'sensors',
            actuatorType: $data['actuator_type'] ?? null,
            startDate: isset($data['start_date']) ? new \DateTime($data['start_date']) : null,
            endDate: isset($data['end_date']) ? new \DateTime($data['end_date']) : null,
            page: isset($data['page']) ? (int)$data['page'] : 1,
            perPage: isset($data['per_page']) ? (int)$data['per_page'] : 50,
        );
    }

    public function hasFilters(): bool
    {
        return $this->deviceId !== null 
            || $this->sensorType !== null 
            || $this->startDate !== null 
            || $this->endDate !== null;
    }
}
