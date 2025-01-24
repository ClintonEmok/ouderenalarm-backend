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
        Schema::create('general_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');  // Link to the device
            $table->timestamp('status_time');  // Converted timestamp
            $table->unsignedTinyInteger('battery_level');  // Battery level (0-100%)

            // Status 1 breakdown
            $table->boolean('gps')->default(false);
            $table->boolean('wifi_source')->default(false);
            $table->boolean('cell_tower')->default(false);
            $table->boolean('ble_location')->default(false);
            $table->boolean('in_charging')->default(false);
            $table->boolean('fully_charged')->default(false);
            $table->boolean('reboot')->default(false);
            $table->boolean('historical_data')->default(false);
            $table->boolean('agps_data_valid')->default(false);
            $table->boolean('motion')->default(false);
            $table->boolean('smart_locating')->default(false);
            $table->boolean('beacon_location')->default(false);
            $table->boolean('ble_connected')->default(false);
            $table->boolean('fall_down_allow')->default(false);
            $table->boolean('home_wifi_location')->default(false);
            $table->boolean('indoor_outdoor_location')->default(false);

            // Status 2 breakdown
            $table->enum('mobile_network_type', ['No service', '2G', '3G', '4G'])->nullable();
            $table->unsignedTinyInteger('work_mode')->nullable();  // bits 16-18
            $table->unsignedTinyInteger('cell_signal_strength')->nullable();  // bits 19-23
            $table->unsignedTinyInteger('battery_description')->nullable();  // bits 24-31

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_statuses');
    }
};
