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
        Schema::create('auction_listings', function (Blueprint $table) {
            $table->id();
            $table->string('auction_id')->unique()->nullable();
            $table->string('title')->nullable();
            $table->string('type')->nullable();
            $table->decimal('current_bid', 8, 2)->nullable();
            $table->integer('bid_count')->default(0);
            $table->string('time_left')->nullable();
            $table->string('image_url')->nullable();
            $table->string('source_url')->nullable();
            $table->string('channel_code')->default('22');
            $table->string('category_code')->nullable();
            $table->timestamp('scraped_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auction_listings');
    }
};
