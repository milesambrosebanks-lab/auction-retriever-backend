<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('extraction_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->timestamp('last_successful_run')->nullable();
            $table->boolean('status')->default(false);
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('extraction_logs');
    }
};
