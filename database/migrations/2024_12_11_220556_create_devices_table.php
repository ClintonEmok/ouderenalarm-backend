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
        Schema::create('devices', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Foreign key to users table
            $table->string('imei')->unique(); // Unique IMEI identifier
            $table->string('nickname')->nullable(); // Optional nickname for the device
            $table->string('ip_address')->nullable(); // Last known IP address
            $table->integer('port')->nullable(); // Port for communication
            $table->string('phone_number')->nullable(); // Nullable phone number
            $table->string('status')->default('active'); // Status of the device
            $table->timestamps(); // Timestamps (created_at, updated_at)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
