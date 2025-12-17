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
        Schema::create('actuator_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('iot_devices')->onDelete('cascade');
            
            // Fan
            $table->integer('fan_duty')->default(0); // 0-100%
            
            // Humidifier
            $table->enum('humidifier_mode', ['manual', 'auto'])->default('auto');
            $table->enum('humidifier_state', ['on', 'off'])->default('off');
            $table->integer('humidifier_duty')->default(0); // 0-100%
            
            // Heater
            $table->enum('heater_mode', ['manual', 'auto'])->default('auto');
            $table->enum('heater_state', ['on', 'off'])->default('off');
            $table->integer('heater_duty')->default(0); // 0-100%
            
            $table->timestamps();
            
            // Only one state record per device
            $table->unique('device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actuator_states');
    }
};
