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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('to_email');
            $table->string('from_email')->nullable();
            $table->string('subject')->nullable();
            $table->longText('message')->nullable();
            $table->longText('others')->nullable();
            $table->string('status')->default('sent');
            $table->timestamps(); // stores created_at automatically
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
