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
        Schema::create('camera_images', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->index();
            $table->string('filename');
            $table->string('path');
            $table->integer('size');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('format')->default('jpeg');
            $table->text('thumbnail_path')->nullable();
            $table->timestamp('captured_at')->index();
            $table->timestamps();
            
            $table->foreign('device_id')
                ->references('device_id')
                ->on('iot_devices')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camera_images');
    }
};
