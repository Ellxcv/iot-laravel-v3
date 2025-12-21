<?php

namespace App\Http\Controllers;

use App\Application\DTOs\Device\AddDeviceDTO;
use App\Application\DTOs\Device\UpdateDeviceStatusDTO;
use App\Application\UseCases\Device\AddDeviceUseCase;
use App\Application\UseCases\Device\DeleteDeviceUseCase;
use App\Application\UseCases\Device\GetAllDevicesUseCase;
use App\Application\UseCases\Device\UpdateDeviceStatusUseCase;
use Exception;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function __construct(
        private GetAllDevicesUseCase $getAllDevicesUseCase,
        private AddDeviceUseCase $addDeviceUseCase,
        private UpdateDeviceStatusUseCase $updateDeviceStatusUseCase,
        private DeleteDeviceUseCase $deleteDeviceUseCase
    ) {}

    /**
     * Display device management page
     */
    public function index()
    {
        // Check if user is admin
        $isAdmin = auth()->user()->isAdmin();
        
        // Get devices based on user role
        if ($isAdmin) {
            // Admin sees all devices
            $devices = $this->getAllDevicesUseCase->execute();
        } else {
            // Regular user sees only their devices
            $devices = $this->getAllDevicesUseCase->execute(auth()->id());
        }
        
        return view('devices.index', compact('devices'));
    }

    /**
     * Store new device
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'device_id' => 'required|string|unique:iot_devices,device_id',
            'type' => 'required|in:sensor,camera,controller',
            'is_active' => 'boolean',
        ]);

        try {
            $dto = AddDeviceDTO::fromArray([
                'name' => $validated['name'],
                'device_id' => $validated['device_id'],
                'type' => $validated['type'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            $this->addDeviceUseCase->execute($dto);

            return redirect()
                ->route('devices.index')
                ->with('success', 'Device added successfully!');

        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to add device: ' . $e->getMessage());
        }
    }

    /**
     * Update device status (AJAX)
     */
    public function updateStatus(Request $request, int $id)
    {
        try {
            $dto = new UpdateDeviceStatusDTO(
                id: $id,
                isActive: $request->boolean('is_active')
            );

            $this->updateDeviceStatusUseCase->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Device status updated successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete device
     */
    public function destroy(int $id)
    {
        try {
            $this->deleteDeviceUseCase->execute($id);

            return redirect()
                ->route('devices.index')
                ->with('success', 'Device deleted successfully!');

        } catch (Exception $e) {
            return redirect()
                ->route('devices.index')
                ->with('error', 'Failed to delete device: ' . $e->getMessage());
        }
    }

    /**
     * Update device offline notification settings
     */
    public function updateOfflineSettings(Request $request)
    {
        $validated = $request->validate([
            'offline_timeout_minutes' => 'required|integer|min:1|max:1440',
            'notification_enabled' => 'nullable|boolean',
        ]);

        try {
            $user = auth()->user();
            
            // Get or create offline settings for user
            $offlineSetting = \App\Models\DeviceOfflineSetting::firstOrNew(['user_id' => $user->id]);
            
            $offlineSetting->offline_timeout_minutes = $validated['offline_timeout_minutes'];
            $offlineSetting->notification_enabled = $request->boolean('notification_enabled');
            $offlineSetting->save();

            return redirect()
                ->route('devices.index')
                ->with('success', '✅ Offline notification settings saved successfully!');

        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to save settings: ' . $e->getMessage());
        }
    }
}
