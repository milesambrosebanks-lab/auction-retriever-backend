<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auction_listings', function (Blueprint $table) {
            $table->string('source')->nullable()->after('source_url');
        });
    }

    public function down(): void
    {
        Schema::table('auction_listings', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
