<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Bid4AssetsScraper;

class ScrapeBid4Assets extends Command
{
    protected $signature = 'scrape:bid4assets
                            {--channel=22 : Channel code}
                            {--category=  : Category code}
                            {--state=     : Filter by state}';

    protected $description = 'Scrape auction listings from Bid4Assets';

    public function handle(Bid4AssetsScraper $scraper): void
    {
        $this->info('Bid4Assets scraper starting...');

        $total = $scraper->scrapeAll(
            channelCode:  $this->option('channel'),
            categoryCode: $this->option('category') ?? '',
            locatedState: $this->option('state') ?? '',
        );

        $this->info("Done! Total saved/updated: {$total} listings.");
    }
}
