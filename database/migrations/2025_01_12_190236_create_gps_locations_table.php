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
        Schema::create('gps_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->decimal('latitude', 10, 6);  // For precision to six decimal places (micro-degrees)
            $table->decimal('longitude', 10, 6);
            $table->unsignedSmallInteger('speed')->nullable();  // Speed in km/h
            $table->unsignedSmallInteger('direction')->nullable();  // Direction in degrees (0 - 359)
            $table->integer('altitude')->nullable();  // Altitude in meters
            $table->decimal('horizontal_accuracy', 4, 1)->nullable();  // Accuracy range 0.0 to 99.9
            $table->unsignedInteger('mileage')->nullable();  // Mileage in meters
            $table->unsignedTinyInteger('satellites')->nullable();  // Number of satellites
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gps_locations');
    }
};
