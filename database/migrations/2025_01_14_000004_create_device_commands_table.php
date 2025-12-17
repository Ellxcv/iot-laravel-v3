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
        Schema::create('device_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('iot_devices')->onDelete('cascade');
            $table->string('command_type'); // feeder_open, feeder_close, feeder_feed, custom
            $table->text('command_data')->nullable(); // JSON data for command
            $table->enum('status', ['pending', 'sent', 'acknowledged', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
            
            $table->index(['device_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_commands');
    }
};
