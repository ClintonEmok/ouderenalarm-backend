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
        Schema::create('device_alarms', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('device_id')->constrained()->onDelete('cascade'); // Link to device
            $table->timestamp('triggered_at'); // Timestamp of the alarm
            $table->boolean('battery_low_alert')->default(false);
            $table->boolean('over_speed_alert')->default(false);
            $table->boolean('fall_down_alert')->default(false);
            $table->boolean('welfare_alert')->default(false);
            $table->boolean('geo_1_alert')->default(false);
            $table->boolean('geo_2_alert')->default(false);
            $table->boolean('geo_3_alert')->default(false);
            $table->boolean('geo_4_alert')->default(false);
            $table->boolean('power_off_alert')->default(false);
            $table->boolean('power_on_alert')->default(false);
            $table->boolean('motion_alert')->default(false);
            $table->boolean('no_motion_alert')->default(false);
            $table->boolean('sos_alert')->default(false);
            $table->boolean('side_call_button_1')->default(false);
            $table->boolean('side_call_button_2')->default(false);
            $table->boolean('battery_charging_start')->default(false);
            $table->boolean('no_charging')->default(false);
            $table->boolean('sos_ending')->default(false);
            $table->boolean('amber_alert')->default(false);
            $table->boolean('welfare_alert_ending')->default(false);
            $table->boolean('fall_down_ending')->default(false);
            $table->boolean('one_day_upload')->default(false);
            $table->boolean('beacon_absence')->default(false);
            $table->boolean('bark_detection')->default(false);
            $table->boolean('ble_disconnected')->default(false);
            $table->boolean('watch_taken_away')->default(false);
            $table->timestamps(); // Adds created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_alarms');
    }
};
