<?php

namespace App\Http\Controllers;

use App\Application\DTOs\SendNotificationDTO;
use App\Application\DTOs\UpdateNotificationSettingsDTO;
use App\Application\UseCases\Notification\GetNotificationSettingsUseCase;
use App\Application\UseCases\Notification\SendNotificationUseCase;
use App\Application\UseCases\Notification\UpdateNotificationSettingsUseCase;
use App\Domain\Repositories\NotificationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class NotificationController extends Controller
{
    public function __construct(
        private GetNotificationSettingsUseCase $getNotificationSettingsUseCase,
        private UpdateNotificationSettingsUseCase $updateNotificationSettingsUseCase,
        private SendNotificationUseCase $sendNotificationUseCase,
        private NotificationRepositoryInterface $notificationRepository
    ) {}

    /**
     * Show notification settings page
     */
    public function index(): View
    {
        $userId = Auth::id();
        $settings = $this->getNotificationSettingsUseCase->execute($userId);
        $logs = $this->notificationRepository->getLogsByUserId($userId, 20);

        return view('notification.index', compact('settings', 'logs'));
    }

    /**
     * Update notification settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bot_token' => 'nullable|string',
            'chat_id' => 'nullable|string',
            'enabled' => 'boolean',
            'fcm_device_token' => 'nullable|string',
            'firebase_enabled' => 'boolean',
        ]);

        try {
            $dto = UpdateNotificationSettingsDTO::fromArray([
                'user_id' => Auth::id(),
                'bot_token' => $validated['bot_token'] ?? null,
                'chat_id' => $validated['chat_id'] ?? null,
                'enabled' => $validated['enabled'] ?? false,
                'fcm_device_token' => $validated['fcm_device_token'] ?? null,
                'firebase_enabled' => $validated['firebase_enabled'] ?? false,
            ]);

            $settings = $this->updateNotificationSettingsUseCase->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Notification settings updated successfully',
                'settings' => [
                    'telegram_enabled' => $settings->enabled,
                    'firebase_enabled' => $settings->firebaseEnabled,
                    'configured' => $settings->isConfigured() || $settings->canSendFirebaseNotifications(),
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Send test notification
     */
    public function sendTest(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:500',
            ]);

            $dto = SendNotificationDTO::fromArray([
                'user_id' => Auth::id(),
                'type' => 'manual_test',
                'message' => $validated['message'],
            ]);

            $log = $this->sendNotificationUseCase->execute($dto);

            return response()->json([
                'success' => $log->isSent(),
                'message' => $log->isSent() 
                    ? 'Test notification sent successfully!' 
                    : 'Failed to send notification: ' . $log->errorMessage,
                'log' => [
                    'status' => $log->status,
                    'sent_at' => $log->sentAt?->format('Y-m-d H:i:s'),
                ],
            ], $log->isSent() ? 200 : 400);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            // Log the full error for debugging
            \Illuminate\Support\Facades\Log::error('Notification test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get notification logs (AJAX)
     */
    public function getLogs(): JsonResponse
    {
        $userId = Auth::id();
        $logs = $this->notificationRepository->getLogsByUserId($userId, 50);

        return response()->json([
            'success' => true,
            'logs' => array_map(function ($log) {
                return [
                    'id' => $log->getId(),
                    'type' => $log->type,
                    'message' => $log->message,
                    'status' => $log->status,
                    'sent_at' => $log->sentAt?->format('Y-m-d H:i:s'),
                    'error_message' => $log->errorMessage,
                ];
            }, $logs),
        ]);
    }
}
