<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('saved_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('listing_id');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('saved_listings');
    }
};
