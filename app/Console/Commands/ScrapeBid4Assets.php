<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Bid4AssetsScraper;
use Illuminate\Support\Facades\Cache;

class ScrapeBid4Assets extends Command
{
    protected $signature = 'scrape:bid4assets
                            {--channel=22 : Channel code}
                            {--category=  : Category code}
                            {--state=     : Filter by state}';

    protected $description = 'Scrape auction listings from Bid4Assets';

    public function handle(Bid4AssetsScraper $scraper): void
    {
        $this->info('Starting...');

        $total       = $scraper->scrapeAll();
        $lastSuccess = Cache::get('bid4assets_last_success', 'N/A');
        $lastCount   = Cache::get('bid4assets_last_count', 0);

        if ($total > 0) {
            $this->info("✓ Done! Saved: $total listings");
        } else {
            $this->error("✗ Failed! Check logs. Last success: $lastSuccess ($lastCount items)");
        }
    }
}
