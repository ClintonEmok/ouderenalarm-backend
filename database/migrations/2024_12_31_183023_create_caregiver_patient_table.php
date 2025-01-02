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
        Schema::create('caregiver_patients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('caregiver_id');
            $table->unsignedBigInteger('patient_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('caregiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');

            // Ensure uniqueness of the relationship
            $table->unique(['caregiver_id', 'patient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caregiver_patients');
    }
};
