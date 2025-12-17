<?php

namespace App\Infrastructure\Services;

use App\Domain\Services\NotificationServiceInterface;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FirebaseNotificationService implements NotificationServiceInterface
{
    private const FCM_API_URL = 'https://fcm.googleapis.com/v1/projects/iot-laravel-6c139/messages:send';
    private const SCOPES = ['https://www.googleapis.com/auth/firebase.messaging'];

    /**
     * Get OAuth2 access token from Service Account
     */
    private function getAccessToken(): string
    {
        try {
            $serviceAccountPath = base_path('firebase-service-account.json');
            
            if (!file_exists($serviceAccountPath)) {
                throw new Exception('Firebase service account file not found');
            }

            $credentials = new ServiceAccountCredentials(
                self::SCOPES,
                $serviceAccountPath
            );

            $token = $credentials->fetchAuthToken();
            
            if (!isset($token['access_token'])) {
                throw new Exception('Failed to get access token');
            }

            return $token['access_token'];
        } catch (Exception $e) {
            Log::error('FCM OAuth2 error: ' . $e->getMessage());
            throw new Exception('Failed to authenticate with Firebase: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to single device (legacy interface compatibility)
     */
    public function send(string $serverKey, string $deviceToken, string $message): bool
    {
        return $this->sendToDevice($deviceToken, 'IoT Alert', $message);
    }

    /**
     * Send notification to single device using FCM HTTP v1 API
     */
    public function sendToDevice(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $accessToken = $this->getAccessToken();

            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_merge([
                        'click_action' => config('app.url') . '/dashboard',
                        'timestamp' => now()->toIso8601String(),
                    ], $data),
                    'webpush' => [
                        'headers' => [
                            'TTL' => '86400', // 24 hours
                        ],
                        'notification' => [
                            'icon' => config('app.url') . '/favicon.ico',
                            'badge' => config('app.url') . '/favicon.ico',
                            'requireInteraction' => false,
                            'vibrate' => [200, 100, 200],
                        ],
                    ],
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post(self::FCM_API_URL, $payload);

            if ($response->successful()) {
                Log::info('FCM notification sent successfully', ['token' => substr($token, 0, 20) . '...']);
                return true;
            }

            $error = $response->json();
            Log::error('FCM API error', ['error' => $error, 'status' => $response->status()]);
            
            // If token is invalid, we should remove it from database
            if (isset($error['error']['status']) && $error['error']['status'] === 'NOT_FOUND') {
                return false; // Token invalid
            }

            return false;
        } catch (Exception $e) {
            Log::error('FCM send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple devices
     */
    public function sendToMultipleDevices(array $tokens, string $title, string $body, array $data = []): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'invalid_tokens' => [],
        ];

        foreach ($tokens as $token) {
            $sent = $this->sendToDevice($token, $title, $body, $data);
            
            if ($sent) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['invalid_tokens'][] = $token;
            }
        }

        Log::info('Multi-device FCM results', $results);

        return $results;
    }
}
