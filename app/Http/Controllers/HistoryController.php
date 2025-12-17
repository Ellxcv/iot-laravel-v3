<?php

namespace App\Http\Controllers;

use App\Application\DTOs\HistoricalDataQueryDTO;
use App\Application\UseCases\History\GetHistoricalDataUseCase;
use App\Domain\Repositories\IoTRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function __construct(
        private GetHistoricalDataUseCase $getHistoricalDataUseCase,
        private IoTRepositoryInterface $iotRepository
    ) {}

    /**
     * Show history view
     */
    public function index(): View
    {
        $devices = $this->iotRepository->getAllDevices();
        
        return view('history.index', compact('devices'));
    }

    /**
     * Get historical data (AJAX endpoint)
     */
    public function getData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'nullable|integer|exists:iot_devices,id',
            'sensor_type' => 'nullable|string|in:temperature,humidity,water_level,soil_moisture,odor_index',
            'data_type' => 'nullable|string|in:sensors,actuators',
            'actuator_type' => 'nullable|string|in:fan,heater,humidifier',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:10|max:100000',
        ]);

        try {
            $dto = HistoricalDataQueryDTO::fromArray($validated);
            $result = $this->getHistoricalDataUseCase->execute($dto);

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'pagination' => $result['pagination'],
                'stats' => $result['stats'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch historical data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
