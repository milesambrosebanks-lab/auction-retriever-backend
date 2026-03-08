<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Listing;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        Listing::create([
            'title' => '3 Bed House in Miami',
            'country' => 'Miami-Dade',
            'state' => 'Florida',
            'property_type' => 'Residential',
            'starting_bid' => 120000,
            'auction_date' => '2026-04-10',
            'source_website' => 'auctionexample.com',
            'source_url' => 'https://auctionexample.com/property/101'
        ]);

        Listing::create([
            'title' => 'Commercial Lot in Dallas',
            'country' => 'Dallas',
            'state' => 'Texas',
            'property_type' => 'Commercial',
            'starting_bid' => 250000,
            'auction_date' => '2026-04-20',
            'source_website' => 'auctionexample.com',
            'source_url' => 'https://auctionexample.com/property/102'
        ]);

        Listing::create([
            'title' => 'Farm Land Auction',
            'country' => 'Orange',
            'state' => 'California',
            'property_type' => 'Land',
            'starting_bid' => 80000,
            'auction_date' => '2026-04-25',
            'source_website' => 'auctionexample.com',
            'source_url' => 'https://auctionexample.com/property/103'
        ]);
    }
}