<?php

namespace App\Console\Commands;

use App\Services\ListingExtractionService;
use Illuminate\Console\Command;

class ExtractListings extends Command
{
    protected $signature = 'extract:listings';

    protected $description = 'Extract listings from auction sources';

    public function handle()
    {
        $this->info('Starting listing extraction...');

        $sources = [
            'https://example-auction-site.com/api',
            'https://auction2.com/api'
        ];

        $service = new ListingExtractionService();

        foreach ($sources as $source) {

            try {

                $service->extractFromSource($source);

                $this->info("Extraction success: $source");
            } catch (\Exception $e) {

                $this->error("Extraction failed: $source");
            }
        }

        $this->info('Extraction finished');

        return Command::SUCCESS;
    }
}
