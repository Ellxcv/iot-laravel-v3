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
        Schema::table('sensor_data', function (Blueprint $table) {
            // MQ-135 additional fields
            $table->float('co2_ppm')->nullable()->after('odor_index');
            $table->float('mq_baseline')->nullable()->after('co2_ppm');
            $table->float('mq_adc')->nullable()->after('mq_baseline');
            $table->float('mq_vpin')->nullable()->after('mq_adc');
            $table->float('mq_vgas')->nullable()->after('mq_vpin');
            
            // Water level additional fields
            $table->float('wl_adc')->nullable()->after('water_level');
            $table->float('wl_volt')->nullable()->after('wl_adc');
            $table->string('wl_zone')->nullable()->after('wl_volt');
            
            // Soil moisture additional fields
            $table->float('soil_adc')->nullable()->after('soil_moisture');
            $table->float('soil_volt')->nullable()->after('soil_adc');
            $table->float('soil_pct')->nullable()->after('soil_volt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->dropColumn([
                'co2_ppm',
                'mq_baseline',
                'mq_adc',
                'mq_vpin',
                'mq_vgas',
                'wl_adc',
                'wl_volt',
                'wl_zone',
                'soil_adc',
                'soil_volt',
                'soil_pct',
            ]);
        });
    }
};
