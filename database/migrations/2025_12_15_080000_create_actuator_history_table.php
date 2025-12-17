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
        Schema::create('actuator_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('iot_devices')->onDelete('cascade');
            
            // Actuator duty cycles (0-100%)
            $table->float('fan_duty_pct')->default(0);
            $table->float('heater_duty_pct')->default(0);
            $table->float('humid_duty_pct')->default(0);
            
            // Actuator on/off states
            $table->boolean('humidifier_on')->default(false);
            $table->boolean('heater_on')->default(false);
            
            // Control mode (FUZZY, MANUAL, AUTO)
            $table->string('control_mode')->default('FUZZY');
            
            $table->timestamps();
            
            // Index for efficient querying by device and date
            $table->index(['device_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actuator_history');
    }
};
