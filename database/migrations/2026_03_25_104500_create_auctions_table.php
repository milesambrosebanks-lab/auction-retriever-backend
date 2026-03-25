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
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->string('auction_id')->unique();
            $table->string('type')->nullable();
            $table->decimal('current_bid', 12, 2)->nullable();
            $table->text('source_url')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('county')->nullable();
            $table->timestamp('scraped_at')->nullable();
            $table->timestamps();

            $table->index('city');
            $table->index('state');
            $table->index('scraped_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
