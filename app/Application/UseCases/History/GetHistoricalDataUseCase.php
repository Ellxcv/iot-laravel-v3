<?php

namespace App\Application\UseCases\History;

use App\Application\DTOs\HistoricalDataQueryDTO;
use App\Domain\Repositories\IoTRepositoryInterface;

class GetHistoricalDataUseCase
{
    public function __construct(
        private IoTRepositoryInterface $iotRepository
    ) {}

    public function execute(HistoricalDataQueryDTO $dto): array
    {
        // Fetch historical data with filters
        $data = $this->iotRepository->getHistoricalData(
            deviceId: $dto->deviceId,
            sensorType: $dto->sensorType,
            startDate: $dto->startDate,
            endDate: $dto->endDate,
            page: $dto->page,
            perPage: $dto->perPage,
            dataType: $dto->dataType,
            actuatorType: $dto->actuatorType
        );

        // Get statistics if filters are applied
        $stats = null;
        if ($dto->hasFilters()) {
            $stats = $this->iotRepository->getHistoricalDataStats(
                deviceId: $dto->deviceId,
                sensorType: $dto->sensorType,
                startDate: $dto->startDate,
                endDate: $dto->endDate
            );
        }

        return [
            'data' => $data['records'],
            'pagination' => [
                'current_page' => $data['current_page'],
                'per_page' => $data['per_page'],
                'total' => $data['total'],
                'last_page' => $data['last_page'],
            ],
            'stats' => $stats,
        ];
    }
}
