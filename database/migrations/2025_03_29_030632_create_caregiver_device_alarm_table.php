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
        Schema::create('caregiver_device_alarm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_alarm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // caregiver (user)
            $table->enum('status', ['assigned', 'en_route', 'arrived'])->default('assigned');
            $table->timestamps();

            $table->unique(['device_alarm_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caregiver_device_alarm');
    }
};
