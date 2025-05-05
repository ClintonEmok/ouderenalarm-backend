<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('cities');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('addresses'); // ⚠️ drops existing table

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
            $table->string('street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->enum('type', ['billing', 'shipping'])->default('shipping');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
