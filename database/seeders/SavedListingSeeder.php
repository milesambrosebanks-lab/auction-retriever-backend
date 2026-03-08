<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SavedListing;

class SavedListingSeeder extends Seeder
{
    public function run(): void
    {
        SavedListing::create([
            'user_id' => 1,
            'listing_id' => 1
        ]);

        SavedListing::create([
            'user_id' => 1,
            'listing_id' => 2
        ]);
    }
}