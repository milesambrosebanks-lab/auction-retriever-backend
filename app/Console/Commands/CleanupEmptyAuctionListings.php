<?php

namespace App\Console\Commands;

use App\Models\AuctionListing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupEmptyAuctionListings extends Command
{
    protected $signature = 'auction-listings:cleanup-empty';

    protected $description = 'Delete auction listings where current_bid and bid_amount are both null';

    public function handle(): int
    {
        $query = AuctionListing::query()
            ->whereNull('current_bid')
            ->whereNull('bid_amount');

        $count = $query->count();

        if ($count === 0) {
            $this->info('No empty auction listings found.');
            return self::SUCCESS;
        }

        $deleted = $query->delete();

        $this->info("Deleted {$deleted} empty auction listings.");
        Log::info("Deleted {$deleted} empty auction listings.");

        return self::SUCCESS;
    }
}
