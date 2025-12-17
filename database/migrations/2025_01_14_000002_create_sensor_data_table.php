<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('iot_devices')->onDelete('cascade');
            
            // DHT22 Sensor
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->decimal('heat_index', 5, 2)->nullable();
            
            // MQ135 Sensor
            $table->integer('odor_index')->nullable();
            $table->decimal('vpin', 8, 2)->nullable();
            $table->decimal('vgas', 8, 2)->nullable();
            
            // Water Level Sensor
            $table->decimal('water_level', 5, 2)->nullable();
            $table->string('water_zone', 20)->nullable(); // low, normal, high
            
            // Soil Moisture Sensor
            $table->decimal('soil_moisture', 5, 2)->nullable();
            $table->string('soil_zone', 20)->nullable(); // dry, moist, wet
            
            // Load Cell
            $table->decimal('weight', 8, 2)->nullable();
            
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['device_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_data');
    }
};
