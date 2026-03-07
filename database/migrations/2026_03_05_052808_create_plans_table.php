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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('stripe_product_id');
            $table->string('stripe_price_id')->unique();

            $table->decimal('price', 8, 2);
            $table->string('currency')->default('usd');

            $table->string('interval'); // month / year
            $table->integer('interval_count')->default(1);

            $table->integer('trial_days')->default(7);

            $table->boolean('status')->default(true);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
