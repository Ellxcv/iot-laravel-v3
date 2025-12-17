<?php

namespace App\Domain\Services;

interface NotificationServiceInterface
{
    /**
     * Send notification message via Telegram
     * 
     * @param string $botToken Telegram bot token
     * @param string $chatId Telegram chat ID
     * @param string $message Message to send
     * @return bool Success status
     * @throws \Exception on failure
     */
    public function send(string $botToken, string $chatId, string $message): bool;
}
