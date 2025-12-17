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
        Schema::table('actuator_states', function (Blueprint $table) {
            // Add new fields for ESP32 format
            $table->float('fan_duty_pct')->default(0)->after('device_id');
            $table->float('heater_duty_pct')->default(0)->after('fan_duty_pct');
            $table->float('humid_duty_pct')->default(0)->after('heater_duty_pct');
            $table->boolean('humidifier_on')->default(false)->after('humid_duty_pct');
            $table->boolean('heater_on')->default(false)->after('humidifier_on');
            $table->string('control_mode')->default('FUZZY')->after('heater_on');
            
            // Drop old columns if they exist
            if (Schema::hasColumn('actuator_states', 'fan_duty')) {
                $table->dropColumn(['fan_duty', 'humidifier_mode', 'humidifier_state', 'humidifier_duty', 'heater_mode', 'heater_state', 'heater_duty']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actuator_states', function (Blueprint $table) {
            // Remove ESP32 fields
            $table->dropColumn([
                'fan_duty_pct',
                'heater_duty_pct',
                'humid_duty_pct',
                'humidifier_on',
                'heater_on',
                'control_mode',
            ]);
            
            // Note: Old columns won't be restored in rollback
            // Manual intervention required if rolling back
        });
    }
};
