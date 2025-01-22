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
        Schema::create('emergency_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_alarm_id')->constrained()->cascadeOnDelete(); // Link to DeviceAlarm
            $table->string('link')->unique(); // Unique link URL
            $table->timestamp('expires_at')->nullable(); // Expiration timestamp
            $table->timestamps(); // Includes created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_links');
    }
};
