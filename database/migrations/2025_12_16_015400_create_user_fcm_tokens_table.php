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
        Schema::create('user_fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('fcm_token');
            $table->string('token_hash', 64)->nullable(); // Hash of token for uniqueness
            $table->string('device_name')->nullable(); // "Chrome on Windows"
            $table->enum('device_type', ['desktop', 'mobile', 'tablet'])->default('desktop');
            $table->text('user_agent')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('last_used_at');
            $table->unique(['user_id', 'token_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_fcm_tokens');
    }
};
