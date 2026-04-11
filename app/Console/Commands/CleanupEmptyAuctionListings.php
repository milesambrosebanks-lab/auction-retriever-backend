<?php

namespace App\Console\Commands;

use App\Models\AuctionListing;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class CleanupEmptyAuctionListings extends Command
{
    protected $signature = 'auction-listings:cleanup';

    protected $description = 'Delete incomplete auction listings and normalize invalid listing types';

    public function handle(): int
    {
        $validTypes = ['Residential', 'Commercial', 'Land'];

        $query = AuctionListing::query()
            ->whereNull('current_bid')
            ->whereNull('bid_amount');

        $count = $query->count();

        if ($count === 0) {
            $this->info('No empty auction listings found.');
            Log::info('No empty auction listings found.');
        } else {
            $deleted = $query->delete();

            $this->info("Deleted {$deleted} empty auction listings.");
            Log::info("Deleted {$deleted} empty auction listings.");
        }

        $deletedWithoutStart = AuctionListing::query()
            ->whereNull('auction_started_at')
            ->delete();

        $this->info("Deleted {$deletedWithoutStart} auction listings without auction_started_at.");
        Log::info("Deleted {$deletedWithoutStart} auction listings without auction_started_at.");

        $updatedTypes = 0;

        $financedUpdated = AuctionListing::query()
            ->where('type', 'Financed')
            ->update([
                'type' => 'Commercial',
            ]);

        if ($financedUpdated > 0) {
            $updatedTypes += $financedUpdated;
        }

        AuctionListing::query()
            ->where(function ($query) use ($validTypes) {
                $query->whereNull('type')
                    ->orWhere('type', 'All')
                    ->orWhereNotIn('type', $validTypes);
            })
            ->chunkById(100, function ($listings) use ($validTypes, &$updatedTypes) {
                foreach ($listings as $listing) {
                    $listing->update([
                        'type' => Arr::random($validTypes),
                    ]);

                    $updatedTypes++;
                }
            });

        $this->info("Updated {$updatedTypes} auction listing types.");
        Log::info("Updated {$updatedTypes} auction listing types.");

        return self::SUCCESS;
    }
}
