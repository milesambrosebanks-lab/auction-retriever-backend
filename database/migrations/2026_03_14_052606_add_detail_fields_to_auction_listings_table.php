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
        Schema::table('auction_listings', function (Blueprint $table) {
            // আগে কোন columns আছে check করে শুধু missing গুলো add করুন
            if (!Schema::hasColumn('auction_listings', 'city')) {
                $table->string('city')->nullable()->after('type');
            }
            if (!Schema::hasColumn('auction_listings', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('auction_listings', 'zip')) {
                $table->string('zip')->nullable()->after('state');
            }
            if (!Schema::hasColumn('auction_listings', 'county')) {
                $table->string('county')->nullable()->after('zip');
            }
            if (!Schema::hasColumn('auction_listings', 'parcel_number')) {
                $table->string('parcel_number')->nullable()->after('county');
            }
            if (!Schema::hasColumn('auction_listings', 'auction_date')) {
                $table->timestamp('auction_date')->nullable()->after('parcel_number');
            }
            if (!Schema::hasColumn('auction_listings', 'auction_started_at')) {
                $table->timestamp('auction_started_at')->nullable()->after('auction_date');
            }
            if (!Schema::hasColumn('auction_listings', 'bid_amount')) {
                $table->decimal('bid_amount', 10, 2)->nullable()->after('current_bid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auction_listings', function (Blueprint $table) {
            //
        });
    }
};
