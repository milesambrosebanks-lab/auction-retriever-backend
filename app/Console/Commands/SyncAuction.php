<?php

namespace App\Console\Commands;

use App\Services\AuctionComScraper;
use Illuminate\Console\Command;

class SyncAuction extends Command
{
    protected $signature   = 'sync:auction-com {--page=0 : specific page}';
    protected $description = 'Sync listings from auction.com GraphQL API';

    public function handle(AuctionComScraper $scraper): void
    {
        if ($this->option('page') > 0) {
            $result = $scraper->fetchPage((int) $this->option('page'));
            $this->info("Fetched: " . count($result['content'] ?? []) . " listings");
            return;
        }

        $this->info("Starting auction.com sync...");
        $updated = $scraper->syncAll();
        $this->info("Done! Synced: {$updated} listings.");
    }
}
