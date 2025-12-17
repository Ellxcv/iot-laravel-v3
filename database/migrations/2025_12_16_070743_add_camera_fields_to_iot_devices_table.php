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
        Schema::table('iot_devices', function (Blueprint $table) {
            $table->string('ip')->nullable()->after('device_id');
            $table->float('fps')->nullable()->after('ip');
            $table->timestamp('last_image_at')->nullable()->after('last_seen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iot_devices', function (Blueprint $table) {
            $table->dropColumn(['ip', 'fps', 'last_image_at']);
        });
    }
};
