<?php

namespace App\Infrastructure\Services;

use App\Domain\Services\NotificationServiceInterface;
use Illuminate\Support\Facades\Http;
use Exception;

class TelegramNotificationService implements NotificationServiceInterface
{
    private const API_BASE_URL = 'https://api.telegram.org/bot';

    public function send(string $botToken, string $chatId, string $message): bool
    {
        try {
            // Use provided token or fall back to config
            $token = !empty($botToken) ? $botToken : config('services.telegram.bot_token');
            
            if (empty($token)) {
                throw new Exception('Telegram bot token not configured');
            }
            
            $url = self::API_BASE_URL . $token . '/sendMessage';
            
            $response = Http::post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['ok'] ?? false;
            }

            throw new Exception('Telegram API request failed: ' . $response->body());
        } catch (Exception $e) {
            throw new Exception('Failed to send Telegram notification: ' . $e->getMessage());
        }
    }
}
