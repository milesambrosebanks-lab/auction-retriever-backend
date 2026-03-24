<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Bid4AssetsDetailScraper;
use App\Models\AuctionListing;
use Illuminate\Support\Facades\Log;

class SyncAuctionDetails extends Command
{
    protected $signature = 'sync:auction-details
                            {--batch=50 : কতটা একসাথে process করবে}
                            {--all : সব listings process করবে}
                            {--id= : specific auction ID}';

    protected $description = 'Fetch location & date details from individual auction pages';

    public function handle(Bid4AssetsDetailScraper $scraper): void
    {
        // Single auction test
        if ($this->option('id')) {
            $id     = $this->option('id');
            $detail = $scraper->fetchDetail($id);

            if ($detail) {
                $this->info("Detail fetched for #{$id}:");
                $this->table(['Field', 'Value'], collect($detail)->map(fn($v, $k) => [$k, $v ?? 'null'])->toArray());
            // Log::info($detail);

                AuctionListing::where('auction_id', $id)->update($detail);
                $this->info("Saved to database!");
            } else {
                $this->error("Failed to fetch detail for #{$id}");
            }
            return;
        }
        // ── All option ──────────────────────────────────────────────
        if ($this->option('all')) {
            $total = AuctionListing::whereNull('state')->whereNotNull('auction_id')->count();
            $this->info("Total pending: {$total}");
            $batch = $total;
        } else {
            $batch = (int) $this->option('batch');
        }

        $pending = AuctionListing::whereNull('state')->whereNotNull('auction_id')->count();
        $this->info("Pending listings without location: {$pending}");

        if ($pending === 0) {
            $this->info("All listings already have location data!");
            return;
        }

        // $batch = (int) $this->option('batch');
        $this->info("Processing {$batch} listings...");

        $bar     = $this->output->createProgressBar($batch);
        $updated = $scraper->syncAll($batch);
        $bar->finish();

        $this->newLine();
        $this->info("Done! Updated: {$updated} listings.");

        $remaining = AuctionListing::whereNull('state')->count();
        $this->info("Remaining without location: {$remaining}");
    }
}
