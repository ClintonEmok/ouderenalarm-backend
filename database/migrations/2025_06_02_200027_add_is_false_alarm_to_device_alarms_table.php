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
        Schema::table('device_alarms', function (Blueprint $table) {
            $table->boolean('is_false_alarm')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_alarms', function (Blueprint $table) {
            $table->dropColumn('is_false_alarm');   $table->dropColumn('is_false_alarm');
        });
    }
};
