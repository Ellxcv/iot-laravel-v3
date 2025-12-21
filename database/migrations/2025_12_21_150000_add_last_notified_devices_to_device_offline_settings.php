<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_offline_settings', function (Blueprint $table) {
            // Add JSON column to track per-device notification timestamps
            $table->json('last_notified_devices')->nullable()->after('last_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('device_offline_settings', function (Blueprint $table) {
            $table->dropColumn('last_notified_devices');
        });
    }
};
