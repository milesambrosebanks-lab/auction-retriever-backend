<?php

namespace App\Console\Commands;

use App\Models\AuctionListing;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class CleanupEmptyAuctionListings extends Command
{
    protected $signature = 'auction-listings:cleanup-empty';

    protected $description = 'Delete empty auction listings and normalize invalid listing types';

    public function handle(): int
    {
        $validTypes = ['Single-Family', 'Commercial', 'Land'];

        $query = AuctionListing::query()
            ->whereNull('current_bid')
            ->whereNull('bid_amount');

        $count = $query->count();

        if ($count === 0) {
            $this->info('No empty auction listings found.');
            // return self::SUCCESS;
        } else {
            $deleted = $query->delete();

            $this->info("Deleted {$deleted} empty auction listings.");
            Log::info("Deleted {$deleted} empty auction listings.");
        }


        $updatedTypes = 0;

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
