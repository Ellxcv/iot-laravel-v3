<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camera_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->string('name');
            $table->string('stream_url');
            $table->string('type')->default('esp32cam');
            $table->enum('status', ['online', 'offline'])->default('offline');
            $table->string('resolution')->nullable();
            $table->integer('fps')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camera_devices');
    }
};
