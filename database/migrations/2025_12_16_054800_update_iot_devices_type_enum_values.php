<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any existing data to match new enum values
        // Map old values to new values if needed
        DB::table('iot_devices')
            ->where('type', 'controller')
            ->update(['type' => 'sensor']); // temporary value
        
        // Alter the type column to new ENUM values matching DeviceType enum
        DB::statement("ALTER TABLE iot_devices MODIFY COLUMN type ENUM('esp32', 'arduino', 'raspberry_pi', 'sensor', 'sensor_node', 'actuator', 'gateway', 'iot', 'camera', 'controller') NOT NULL DEFAULT 'sensor'");
        
        // Optional: Update the temporary values back if needed
        // DB::table('iot_devices')->where('type', 'sensor')->update(['type' => 'controller']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original ENUM values
        DB::statement("ALTER TABLE iot_devices MODIFY COLUMN type ENUM('sensor', 'camera', 'controller') NOT NULL DEFAULT 'sensor'");
    }
};
