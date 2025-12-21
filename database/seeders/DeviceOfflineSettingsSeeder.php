<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DeviceOfflineSetting;
use Illuminate\Database\Seeder;

class DeviceOfflineSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users who don't have offline settings yet
        $users = User::doesntHave('deviceOfflineSetting')->get();

        foreach ($users as $user) {
            DeviceOfflineSetting::create([
                'user_id' => $user->id,
                'offline_timeout_minutes' => 5, // Default 5 minutes
                'notification_enabled' => true, // Enabled by default
                'last_notified_at' => null,
            ]);

            $this->command->info("Created offline settings for user: {$user->name} (ID: {$user->id})");
        }

        $this->command->info("Seeder completed. Created settings for {$users->count()} users.");
    }
}
