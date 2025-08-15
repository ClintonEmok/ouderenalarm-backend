<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsFalseAlarmToAlarmNotesTable extends Migration
{
    public function up(): void
    {
        Schema::table('alarm_notes', function (Blueprint $table) {
            $table->boolean('is_false_alarm')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('alarm_notes', function (Blueprint $table) {
            $table->dropColumn('is_false_alarm');
        });
    }
}
