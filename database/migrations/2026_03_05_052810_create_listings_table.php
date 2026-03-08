<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('county')->nullable();
            $table->string('state')->nullable();
            $table->string('property_type')->nullable();
            $table->decimal('starting_bid', 12, 2)->nullable();
            $table->date('auction_date')->nullable();
            $table->string('source_website');
            $table->text('source_url');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
