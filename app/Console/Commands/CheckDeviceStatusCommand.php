<?php

namespace App\Console\Commands;

use App\Models\IoTDevice;
use App\Models\DeviceOfflineSetting;
use App\Application\DTOs\SendNotificationDTO;
use App\Application\UseCases\Notification\SendNotificationUseCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckDeviceStatusCommand extends Command
{
    protected $signature = 'device:check-status';
    protected $description = 'Check device online status and send offline notifications';

    public function __construct(
        private SendNotificationUseCase $sendNotificationUseCase
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Checking device status...');

        // Get all active devices with their user and offline settings
        $devices = IoTDevice::with(['user', 'user.deviceOfflineSetting'])
            ->where('is_active', true)
            ->get();

        $notificationsSent = 0;
        $timersReset = 0;
        $devicesChecked = 0;

        foreach ($devices as $device) {
            $devicesChecked++;

            // Skip if user doesn't have offline settings configured
            if (!$device->user || !$device->user->deviceOfflineSetting) {
                continue;
            }

            $settings = $device->user->deviceOfflineSetting;

            // Skip if notifications are disabled
            if (!$settings->canSendNotification()) {
                continue;
            }

            // NEW FEATURE: Reset notification timer when device comes back online
            // This allows fresh notifications if device goes offline again
            if ($device->isOnline()) {
                // If device is online and has a notification timestamp, reset it
                if ($settings->last_notified_devices && isset($settings->last_notified_devices[$device->device_id])) {
                    $settings->resetDeviceNotificationTimer($device->device_id);
                    $timersReset++;
                    $this->info("  ✓ Reset notification timer for online device: {$device->name}");
                }
                continue; // Skip to next device (no need to check offline)
            }

            // Device is offline - check if we should send notification
            
            // PER-DEVICE spam prevention: Check if enough time passed since last notification FOR THIS DEVICE
            if (!$settings->isDeviceNotificationDue($device->device_id)) {
                continue;
            }

            // Check if device is offline beyond timeout threshold
            if ($this->isDeviceOffline($device, $settings->getTimeoutMinutes())) {
                $this->sendOfflineNotification($device, $settings);
                $notificationsSent++;
            }
        }

        $this->info("Device status check complete. Checked {$devicesChecked} devices, sent {$notificationsSent} offline notifications, reset {$timersReset} timers.");
        
        return 0;
    }

    /**
     * Check if device is offline based on last_seen timestamp
     */
    private function isDeviceOffline(IoTDevice $device, int $timeoutMinutes): bool
    {
        if (!$device->last_seen) {
            return true; // Never sent data = offline
        }

        // Device is offline if last_seen is older than timeout
        return $device->last_seen->lessThan(now()->subMinutes($timeoutMinutes));
    }

    /**
     * Send offline notification for device
     */
    private function sendOfflineNotification(IoTDevice $device, DeviceOfflineSetting $settings): void
    {
        $timeoutMinutes = $settings->getTimeoutMinutes();
        $lastSeenText = $device->last_seen 
            ? $device->last_seen->diffForHumans() 
            : 'never';

        $message = $this->formatOfflineMessage($device, $timeoutMinutes, $lastSeenText);

        try {
            $dto = SendNotificationDTO::fromArray([
                'user_id' => $device->user_id,
                'type' => 'device_status', // Changed from 'device_offline' to valid type
                'message' => $message,
            ]);

            $this->sendNotificationUseCase->execute($dto);
            
            // CRITICAL: Record notification timestamp for THIS SPECIFIC DEVICE
            $settings->recordDeviceNotificationSent($device->device_id);

            Log::info("Device offline notification sent", [
                'device' => $device->name,
                'device_id' => $device->device_id,
                'last_seen' => $lastSeenText,
                'timeout_minutes' => $timeoutMinutes,
            ]);

            $this->info("  → Sent offline alert for: {$device->name}");
        } catch (\Exception $e) {
            Log::error("Failed to send device offline notification: " . $e->getMessage(), [
                'device' => $device->name,
                'error' => $e->getMessage(),
            ]);

            $this->error("  → Failed to send alert for: {$device->name}");
        }
    }

    /**
     * Format offline notification message
     */
    private function formatOfflineMessage(IoTDevice $device, int $timeoutMinutes, string $lastSeenText): string
    {
        return "🔴 DEVICE OFFLINE ALERT!\n\n"
            . "Device: {$device->name}\n"
            . "Device ID: {$device->device_id}\n"
            . "Last Seen: {$lastSeenText}\n"
            . "Offline Timeout: {$timeoutMinutes} minutes\n"
            . "Time: " . now()->format('Y-m-d H:i:s') . "\n\n"
            . "Please check your device connection.";
    }
}
