<?php

namespace App\Http\Controllers;

use App\Application\DTOs\CreateDeviceDTO;
use App\Application\DTOs\UpdateDeviceDTO;
use App\Application\UseCases\Device\ListDevicesUseCase;
use App\Application\UseCases\Device\CreateDeviceUseCase;
use App\Application\UseCases\Device\UpdateDeviceUseCase;
use App\Application\UseCases\Device\DeleteDeviceUseCase;
use App\Domain\Entities\DeviceType;
use App\Domain\Entities\DeviceStatus;
use App\Models\IoTDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminDeviceController extends Controller
{
    /**
     * Display a listing of devices
     */
    public function index(Request $request, ListDevicesUseCase $useCase)
    {
        $filters = [
            'status' => $request->query('status'),
            'type' => $request->query('type'),
            'user_id' => $request->query('user_id'),
            'search' => $request->query('search'),
        ];

        $devices = $useCase->execute(perPage: 15, filters: array_filter($filters));
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('admin.devices.index', compact('devices', 'users'));
    }

    /**
     * Show the form for creating a new device
     */
    public function create()
    {
        $users = User::orderBy('name')->get(['id', 'name']);
        return view('admin.devices.create', compact('users'));
    }

    /**
     * Store a newly created device
     */
    public function store(Request $request, CreateDeviceUseCase $useCase)
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:255|unique:iot_devices,device_id',
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(DeviceType::values())],
            'description' => 'nullable|string|max:1000',
            'user_id' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(DeviceStatus::values())],
        ]);

        $dto = CreateDeviceDTO::fromRequest($request);
        $device = $useCase->execute($dto);

        return redirect()->route('admin.devices.index')
            ->with('success', 'Device created successfully.');
    }

    /**
     * Display the specified device
     */
    public function show(IoTDevice $device)
    {
        $device->load(['user', 'sensorData' => function ($query) {
            $query->latest()->take(10);
        }]);

        return view('admin.devices.show', compact('device'));
    }

    /**
     * Show the form for editing the specified device
     */
    public function edit(IoTDevice $device)
    {
        $users = User::orderBy('name')->get(['id', 'name']);
        return view('admin.devices.edit', compact('device', 'users'));
    }

    /**
     * Update the specified device
     */
    public function update(Request $request, IoTDevice $device, UpdateDeviceUseCase $useCase)
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:255', Rule::unique('iot_devices', 'device_id')->ignore($device->id)],
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(DeviceType::values())],
            'description' => 'nullable|string|max:1000',
            'user_id' => 'nullable|exists:users,id',
            'status' => ['required', Rule::in(DeviceStatus::values())],
        ]);

        $dto = UpdateDeviceDTO::fromRequest($request, $device->id);
        $updatedDevice = $useCase->execute($dto);

        return redirect()->route('admin.devices.index')
            ->with('success', 'Device updated successfully.');
    }

    /**
     * Remove the specified device
     */
    public function destroy(IoTDevice $device, DeleteDeviceUseCase $useCase)
    {
        try {
            $useCase->execute($device->id);
            return redirect()->route('admin.devices.index')
                ->with('success', 'Device deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
