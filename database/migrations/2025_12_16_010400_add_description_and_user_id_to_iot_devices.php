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
            $table->text('description')->nullable()->after('name');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iot_devices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['description', 'user_id']);
        });
    }
};
