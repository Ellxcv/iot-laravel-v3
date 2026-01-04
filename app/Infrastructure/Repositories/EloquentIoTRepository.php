<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\IoTRepositoryInterface;
use App\Models\SensorData;
use App\Models\ActuatorHistory;
use App\Models\IoTDevice;
use Illuminate\Support\Facades\DB;

class EloquentIoTRepository implements IoTRepositoryInterface
{
    public function getHistoricalData(
        ?int $deviceId,
        ?string $sensorType,
        ?\DateTime $startDate,
        ?\DateTime $endDate,
        int $page,
        int $perPage,
        ?string $dataType = 'sensors',
        ?string $actuatorType = null
    ): array {
        // If querying actuators, use ActuatorHistory table
        if ($dataType === 'actuators') {
            return $this->getActuatorHistoricalData($deviceId, $actuatorType, $startDate, $endDate, $page, $perPage);
        }
        
        // Map sensor types to ESP32 column names
        $columnMap = [
            'temperature' => 'temperature',
            'humidity' => 'humidity',
            'air_quality' => 'co2_ppm',
            'co2' => 'co2_ppm',
            'co2_ppm' => 'co2_ppm',
            'odor' => 'odor_index',
            'odor_index' => 'odor_index',
            'water_level' => 'water_level',
            'water' => 'water_level',
            'soil_moisture' => 'soil_pct',
            'soil' => 'soil_pct',
            'soil_pct' => 'soil_pct',
            'weight' => 'weight',
            'weight_grams' => 'weight',
        ];
        
        $column = $sensorType ? ($columnMap[$sensorType] ?? 'temperature') : 'temperature';
        
        $query = SensorData::query()
            ->with('device:id,name,type')
            ->select('sensor_data.*');

        // Apply filters
        if ($deviceId !== null) {
            $query->where('device_id', $deviceId);
        }

        if ($startDate !== null) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->where('created_at', '<=', $endDate);
        }

        // Order by latest first
        $query->orderBy('created_at', 'desc');

        // Paginate
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        
        // Transform records to include 'value' field from specific column
        $transformedRecords = collect($paginator->items())->map(function ($record) use ($column, $sensorType) {
            $record->sensor_type = $sensorType;
            $record->value = $record->$column ?? 0;
            $record->unit = $this->getUnitForSensor($sensorType);
            return $record;
        })->toArray();

        return [
            'records' => $transformedRecords,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    public function getHistoricalDataStats(
        ?int $deviceId,
        ?string $sensorType,
        ?\DateTime $startDate,
        ?\DateTime $endDate,
        ?string $dataType = 'sensors',
        ?string $actuatorType = null
    ): array {
        // If querying actuators, use ActuatorHistory statistics
        if ($dataType === 'actuators') {
            return $this->getActuatorHistoricalDataStats($deviceId, $actuatorType, $startDate, $endDate);
        }
        
        // Map sensor types to ESP32 column names
        $columnMap = [
            'temperature' => 'temperature',
            'humidity' => 'humidity',
            'air_quality' => 'co2_ppm',
            'co2' => 'co2_ppm',
            'co2_ppm' => 'co2_ppm',
            'odor' => 'odor_index',
            'odor_index' => 'odor_index',
            'water_level' => 'water_level',
            'water' => 'water_level',
            'soil_moisture' => 'soil_pct',
            'soil' => 'soil_pct',
            'soil_pct' => 'soil_pct',
            'weight' => 'weight',
            'weight_grams' => 'weight',
        ];
        
        $column = $sensorType ? ($columnMap[$sensorType] ?? 'temperature') : 'temperature';
        
        $query = SensorData::query();

        // Apply same filters
        if ($deviceId !== null) {
            $query->where('device_id', $deviceId);
        }

        if ($startDate !== null) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->where('created_at', '<=', $endDate);
        }

        // Get aggregates for specific column
        $stats = $query->selectRaw("
            COUNT(*) as count,
            AVG($column) as avg_value,
            MIN($column) as min_value,
            MAX($column) as max_value
        ")->first();

        return [
            'count' => $stats->count ?? 0,
            'avg' => $stats->avg_value ? round($stats->avg_value, 2) : null,
            'min' => $stats->min_value ? round($stats->min_value, 2) : null,
            'max' => $stats->max_value ? round($stats->max_value, 2) : null,
        ];
    }
    
    /**
     * Get unit for sensor type
     */
    private function getUnitForSensor(?string $sensorType): string
    {
        $units = [
            'temperature' => '°C',
            'humidity' => '%',
            'air_quality' => 'ppm',
            'co2' => 'ppm',
            'co2_ppm' => 'ppm',
            'odor' => 'index',
            'odor_index' => 'index',
            'water_level' => '%',
            'water' => '%',
            'soil_moisture' => '%',
            'soil' => '%',
            'soil_pct' => '%',
            'weight' => 'g',
            'weight_grams' => 'g',
        ];
        
        return $units[$sensorType] ?? '-';
    }
    
    /**
     * Get actuator historical data
     */
    private function getActuatorHistoricalData(
        ?int $deviceId,
        ?string $actuatorType,
        ?\DateTime $startDate,
        ?\DateTime $endDate,
        int $page,
        int $perPage
    ): array {
        $query = ActuatorHistory::query()
            ->with('device:id,name,type')
            ->select('actuator_history.*');

        // Apply filters
        if ($deviceId !== null) {
            $query->where('device_id', $deviceId);
        }

        if ($startDate !== null) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->where('created_at', '<=', $endDate);
        }

        // Order by latest first
        $query->orderBy('created_at', 'desc');

        // Paginate
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        
        // Transform actuator records to match expected format
        $transformedRecords = collect($paginator->items())->map(function ($record) {
            // Create multiple rows for each actuator type
            return [
                [
                    'device' => $record->device,
                    'actuator_type' => 'fan',
                    'value' => $record->fan_duty_pct,
                    'unit' => '%',
                    'control_mode' => $record->control_mode,
                    'created_at' => $record->created_at,
                ],
                [
                    'device' => $record->device,
                    'actuator_type' => 'heater',
                    'value' => $record->heater_duty_pct,
                    'unit' => '%',
                    'status' => $record->heater_on ? 'ON' : 'OFF',
                    'control_mode' => $record->control_mode,
                    'created_at' => $record->created_at,
                ],
                [
                    'device' => $record->device,
                    'actuator_type' => 'humidifier',
                    'value' => $record->humid_duty_pct,
                    'unit' => '%',
                    'status' => $record->humidifier_on ? 'ON' : 'OFF',
                    'control_mode' => $record->control_mode,
                    'created_at' => $record->created_at,
                ],
            ];
        })->flatten(1);
        
        // Filter by actuator type if specified
        if ($actuatorType !== null) {
            $transformedRecords = $transformedRecords->filter(function ($record) use ($actuatorType) {
                return $record['actuator_type'] === $actuatorType;
            })->values();
        }
        
        $transformedRecords = $transformedRecords->toArray();

        // Calculate totals
        $totalRecordsPerPage = $actuatorType ? count($transformedRecords) : ($paginator->count() * 3);
        $totalRecordsOverall = $actuatorType 
            ? ceil($paginator->total() / 3) * ($paginator->total() > 0 ? 1 : 0) 
            : $paginator->total() * 3;

        return [
            'records' => $transformedRecords,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $totalRecordsOverall,
            'last_page' => $paginator->lastPage(),
        ];
    }

    /**
     * Get actuator historical data statistics
     */
    private function getActuatorHistoricalDataStats(
        ?int $deviceId,
        ?string $actuatorType,
        ?\DateTime $startDate,
        ?\DateTime $endDate
    ): array {
        // Map actuator types to column names
        $columnMap = [
            'fan' => 'fan_duty_pct',
            'heater' => 'heater_duty_pct',
            'humidifier' => 'humid_duty_pct',
        ];
        
        // If no specific actuator type, we can't calculate meaningful stats
        if ($actuatorType === null || !isset($columnMap[$actuatorType])) {
            return [
                'count' => 0,
                'avg' => null,
                'min' => null,
                'max' => null,
            ];
        }
        
        $column = $columnMap[$actuatorType];
        
        $query = ActuatorHistory::query();
        
        // Apply filters
        if ($deviceId !== null) {
            $query->where('device_id', $deviceId);
        }
        
        if ($startDate !== null) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate !== null) {
            $query->where('created_at', '<=', $endDate);
        }
        
        // Get aggregates for specific actuator column
        $stats = $query->selectRaw("
            COUNT(*) as count,
            AVG($column) as avg_value,
            MIN($column) as min_value,
            MAX($column) as max_value
        ")->first();
        
        return [
            'count' => $stats->count ?? 0,
            'avg' => $stats->avg_value ? round($stats->avg_value, 2) : null,
            'min' => $stats->min_value ? round($stats->min_value, 2) : null,
            'max' => $stats->max_value ? round($stats->max_value, 2) : null,
        ];
    }

    public function getAllDevices(): array
    {
        return IoTDevice::orderBy('name')->get()->toArray();
    }

    public function getDevicesByUserId(int $userId): array
    {
        return IoTDevice::where('user_id', $userId)
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
