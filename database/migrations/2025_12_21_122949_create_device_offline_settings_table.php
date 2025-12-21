<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_offline_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('offline_timeout_minutes')->default(5)->comment('Minutes before device is considered offline');
            $table->boolean('notification_enabled')->default(true)->comment('Enable/disable offline notifications');
            $table->timestamp('last_notified_at')->nullable()->comment('Last time offline notification was sent');
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_offline_settings');
    }
};
