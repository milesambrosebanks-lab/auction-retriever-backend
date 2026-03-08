<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExtractionLog;

class ExtractionLogSeeder extends Seeder
{
    public function run(): void
    {
        ExtractionLog::create([
            'source' => 'https://auctionexample.com/api',
            'status' => true,
            'last_successful_run' => now(),
            'message' => 'Extraction successful'
        ]);

        ExtractionLog::create([
            'source' => 'https://auction2.com/api',
            'status' => false,
            'last_successful_run' => now()->subHours(2),
            'message' => 'Connection timeout'
        ]);
    }
}