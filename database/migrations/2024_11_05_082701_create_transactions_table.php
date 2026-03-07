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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('trx_id')->unique();
            $table->string('title')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            // $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->string('type')->nullable();
            $table->string('gateway')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
