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
        Schema::create('tracking_settings', function (Blueprint $table) {
            $table->id();
            $table->string('meta_pixel_id')->nullable();
            $table->string('ga_measurement_id')->nullable();
            $table->string('clarity_id')->nullable();
            $table->boolean('meta_enabled')->default(false);
            $table->boolean('ga_enabled')->default(false);
            $table->boolean('clarity_enabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_settings');
    }
};
