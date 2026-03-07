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
        Schema::create('profiles', function (Blueprint $table) {


            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');


            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            $table->string('phone')->nullable();

            $table->string('address')->nullable();
            $table->string('district')->nullable();
            $table->string('year_of_residence')->nullable();

            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();

            $table->string('nid')->nullable();

            $table->string('city')->nullable();
            $table->string('cityzenship')->nullable();
            $table->string('subdistrict')->nullable();

            $table->string('higest_education')->nullable();
            $table->string('institution')->nullable();
            $table->string('year_of_completion')->nullable();

            $table->string('previous_political_position')->nullable();
            $table->string('political_experience')->nullable();
            $table->longText('political_platform_summary')->nullable();
            $table->longText('key_policy_political')->nullable();

            $table->string('current_occupation')->nullable();
            $table->string('year_of_experience')->nullable();
            $table->string('relavent_skills')->nullable();

            $table->longText('political_statment')->nullable();
            $table->json('Area_of_focus')->nullable();

            $table->enum('type', ['member', 'senator', 'representative'])->default('member');
            $table->enum('agreement', ['yes', 'no'])->nullable();

            $table->string('message')->nullable();
            $table->string('content')->nullable();
            $table->timestamps();



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
