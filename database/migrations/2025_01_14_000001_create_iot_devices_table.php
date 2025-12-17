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
        Schema::create('iot_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->string('name');
            $table->enum('type', ['sensor', 'camera', 'controller'])->default('sensor');
            $table->enum('control_mode', ['manual', 'automatic'])->default('automatic');
            $table->enum('status', ['online', 'offline'])->default('offline');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('device_id');
            $table->index('is_active');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iot_devices');
    }
};
