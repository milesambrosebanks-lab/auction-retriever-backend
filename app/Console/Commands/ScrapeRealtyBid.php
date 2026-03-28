<?php

namespace App\Console\Commands;

use App\Models\AuctionListing;
use App\Models\ScrapeLog;
use Illuminate\Console\Command;
use App\Services\RealtyBidScraper;
use Illuminate\Support\Facades\Log;

class ScrapeRealtyBid extends Command
{
    protected $signature = 'scrape:realtybid';
    protected $description = 'Scrape RealtyBid listings';
    protected $maxAttempts = 3;

    public function handle()
    {
        $this->info("🚀 Starting RealtyBid scrape...");

        // Create ScrapeLog
        $scrapeLog = ScrapeLog::create([
            'source' => 'realtybid',
            'status' => 'running',
            'message' => 'Extraction started — initializing session...',
            'total_scraped' => 0,
            'attempt' => 1,
            'started_at' => now(),
        ]);

        try {
            $scraper = app(RealtyBidScraper::class);
            $page = 1;
            $limit = 50;
            $allData = [];

            while (true) {
                $this->info("📄 Fetching page: $page");

                $attempt = 0;
                $success = false;
                $data = [];

                while ($attempt < $this->maxAttempts && !$success) {
                    try {
                        $attempt++;
                        $data = $scraper->scrape($limit, $page);

                        if (!is_array($data)) {
                            throw new \Exception("Scraper returned invalid data on page $page");
                        }
                        

                        $success = true;
                    } catch (\Exception $e) {
                        Log::error("Retry $attempt/{$this->maxAttempts} failed on page $page: " . $e->getMessage());

                        $scrapeLog->update([
                            'attempt' => $attempt,
                            'error_message' => $e->getMessage(),
                            'message' => "Retry $attempt failed: " . $e->getMessage(),
                        ]);

                        if ($attempt >= $this->maxAttempts) {
                            $scrapeLog->update([
                                'status' => 'failed',
                                'finished_at' => now(),
                                'total_scraped' => count($allData),
                            ]);
                            $this->error("❌ Page $page failed after $attempt attempts. Stopping scrape.");
                            break 2; // exit both loops
                        }

                        sleep(2); // wait before retry
                    }
                }

                // DB Save
                foreach ($data ?? [] as $item) {
                    AuctionListing::updateOrCreate(
                        ['auction_id' => $item['ITEM_ID']],
                        [
                            'title' => $item['ADDRESS1'] ?? null,
                            'city' => $item['CITY'] ?? null,
                            'state' => $item['STATE'] ?? null,
                            'current_bid' => $item['CURRENT_BID'] ?? 0,
                            'auction_start_date' => $item['AUCTION_START_DATE'] ?? null,
                            'auction_end_date' => $item['AUCTION_END_DATE'] ?? null,
                            'property_type' => $item['PROP_TYPE_DESC'] ?? null,
                            'source_url' => 'https://www.realtybid.com',
                            'scraped_at' => now(),
                        ]
                    );
                }

                $allData = array_merge($allData, $data ?? []);

                $scrapeLog->update([
                    'attempt' => $attempt,
                    'total_scraped' => count($allData),
                    'message' => "Page $page scraped successfully. Total so far: " . count($allData),
                ]);

                $this->info("✅ Page $page done (" . count($data ?? []) . " items)");

                if (count($data ?? []) < $limit) {
                    $this->info("🏁 Last page reached.");
                    break;
                }

                $page++;
                sleep(2); // avoid rate-limit
            }

            // Update ScrapeLog success
            $scrapeLog->update([
                'status' => 'success',
                'finished_at' => now(),
                'total_scraped' => count($allData),
            ]);

            $this->info("🎉 RealtyBid scraping completed! Total: " . count($allData));
        } catch (\Exception $e) {
            $scrapeLog->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $e->getMessage(),
                'message' => $e->getMessage(),
                'total_scraped' => count($allData ?? []),
            ]);

            Log::error('Scrape failed: ' . $e->getMessage());
            $this->error("❌ Error: " . $e->getMessage());
        }
    }
}
