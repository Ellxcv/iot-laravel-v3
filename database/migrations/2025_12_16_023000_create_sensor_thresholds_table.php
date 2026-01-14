<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('iot_devices')->onDelete('cascade');
            $table->string('sensor_type'); // 'temperature', 'humidity', 'air_quality'
            $table->decimal('min_value', 8, 2)->nullable();
            $table->decimal('max_value', 8, 2)->nullable();
            $table->boolean('enabled')->default(true);
            $table->integer('cooldown_minutes')->default(30); // Prevent spam
            $table->timestamp('last_alert_at')->nullable();
            $table->timestamps();
            
            $table->unique(['device_id', 'sensor_type']);
        });

        // Insert default thresholds for all existing devices
        DB::statement("
            INSERT INTO sensor_thresholds (device_id, sensor_type, min_value, max_value, enabled, cooldown_minutes, created_at, updated_at)
            SELECT 
                id as device_id,
                'temperature' as sensor_type,
                15.00 as min_value,
                35.00 as max_value,
                1 as enabled,
                30 as cooldown_minutes,
                NOW() as created_at,
                NOW() as updated_at
            FROM iot_devices
        ");

        DB::statement("
            INSERT INTO sensor_thresholds (device_id, sensor_type, min_value, max_value, enabled, cooldown_minutes, created_at, updated_at)
            SELECT 
                id as device_id,
                'humidity' as sensor_type,
                30.00 as min_value,
                80.00 as max_value,
                1 as enabled,
                30 as cooldown_minutes,
                NOW() as created_at,
                NOW() as updated_at
            FROM iot_devices
        ");

        DB::statement("
            INSERT INTO sensor_thresholds (device_id, sensor_type, min_value, max_value, enabled, cooldown_minutes, created_at, updated_at)
            SELECT 
                id as device_id,
                'air_quality' as sensor_type,
                NULL as min_value,
                200.00 as max_value,
                1 as enabled,
                30 as cooldown_minutes,
                NOW() as created_at,
                NOW() as updated_at
            FROM iot_devices
        ");

        DB::statement("
            INSERT INTO sensor_thresholds (device_id, sensor_type, min_value, max_value, enabled, cooldown_minutes, created_at, updated_at)
            SELECT 
                id as device_id,
                'water_level' as sensor_type,
                30.00 as min_value,
                100.00 as max_value,
                1 as enabled,
                30 as cooldown_minutes,
                NOW() as created_at,
                NOW() as updated_at
            FROM iot_devices
        ");

        DB::statement("
            INSERT INTO sensor_thresholds (device_id, sensor_type, min_value, max_value, enabled, cooldown_minutes, created_at, updated_at)
            SELECT 
                id as device_id,
                'weight' as sensor_type,
                30.00 as min_value,
                5000.00 as max_value,
                1 as enabled,
                30 as cooldown_minutes,
                NOW() as created_at,
                NOW() as updated_at
            FROM iot_devices
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_thresholds');
    }
};
