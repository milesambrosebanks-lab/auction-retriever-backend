<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AuctionListing;
use App\Models\ScrapeLog;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class FetchAuctionListings extends Command
{
    protected $signature = 'scrape:auction {--limit=50} {--max=500}';
    protected $description = 'Scrape auction listings using Puppeteer (browser automation)';

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $max = (int) $this->option('max');

        // Create scrape log
        $scrapeLog = ScrapeLog::create([
            'source' => 'auction_com',
            'status' => 'running',
            'attempt' => 1,
            'message' => 'Starting Auction.com scrape...',
            'started_at' => now(),
        ]);

        $this->info("🚀 Starting scrape with Browser Automation...");

        $totalScraped = 0;

        for ($offset = 0; $offset < $max; $offset += $limit) {

            $this->info("📄 Fetching listings offset: $offset (limit: $limit)");

            try {
                // Call Node.js Puppeteer scraper
                $response = $this->scrapeWithPuppeteer($limit, $offset);
                Log::info("test" . $response);

                if (!$response || isset($response['error'])) {
                    $this->warn("❌ No data found or invalid response at offset $offset: " . ($response['error'] ?? 'no response'));
                    break;
                }

                $listings = $response['listings']['data']['seek_listings_from_filters']['content'] ?? [];
                $this->info("✅ Got " . count($listings) . " listings");

                if (empty($listings)) {
                    $this->warn("❌ Empty results, stopping.");
                    break;
                }

                $details = $response['details']['data']['online_segments_batched'] ?? [];
                $detailsMap = collect($details)->keyBy('listing_id');

                // Process listings
                foreach ($listings as $item) {
                    $detail = $detailsMap[$item['listing_id']] ?? null;
                    [$city, $state, $zip, $county] = $this->parseAddress($item['formatted_address'][1] ?? null);

                    // Build title: address line 2 + " - " + structure_type_code
                    $addressLine2 = $item['formatted_address'][1] ?? '';
                    $structureTypeCode = $item['primary_property']['summary']['structure_type_code'] ?? '';
                    $title = trim($addressLine2 . ($structureTypeCode ? ' - ' . $structureTypeCode : ''));

                    // Get auction data
                    $auctionData = $item['auction'] ?? [];
                    $bidAmount = $auctionData['starting_bid'] ?? $detail['starting_bid_amount'] ?? null;

                    // Convert ISO datetime to MySQL format
                    $auctionDate = null;
                    $auctionStartedAt = null;
                    if ($auctionData['start_date']) {
                        try {
                            $auctionDate = \Carbon\Carbon::parse($auctionData['start_date'])->format('Y-m-d H:i:s');
                            $auctionStartedAt = $auctionDate;
                        } catch (\Exception $e) {
                            // Skip invalid dates
                        }
                    }

                    $timeLeft = null;
                    if ($auctionData['end_date']) {
                        try {
                            $timeLeft = \Carbon\Carbon::parse($auctionData['end_date'])->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            // Skip invalid dates
                        }
                    }

                    $listingId = $item['listing_id'];
                    $this->line("  📍 {$listingId} | {$title} | $city, $state $zip");

                    AuctionListing::updateOrCreate(
                        ['auction_id' => $item['listing_id']],
                        [
                            'title' => $title,
                            'type' => $item['primary_property']['summary']['structure_type_group'] ?? $item['listing_status'] ?? null,
                            'current_bid' => $bidAmount,
                            'bid_amount' => $bidAmount,
                            'auction_date' => $auctionDate,
                            'time_left' => $timeLeft,
                            'auction_started_at' => $auctionStartedAt,
                            'image_url' => $item['primary_photo'] ?? null,
                            'source_url' => 'https://www.auction.com' . ($item['listing_page_path'] ?? ''),
                            'city' => $city,
                            'state' => $state,
                            'zip' => $zip,
                            'county' => $county,
                            'address' => implode(', ', $item['formatted_address'] ?? []),
                            'scraped_at' => now(),
                        ]
                    );

                    $totalScraped++;
                }

                // Update scrape log with progress
                $scrapeLog->update([
                    'total_scraped' => $totalScraped,
                    'message' => "Processed $totalScraped listings so far...",
                ]);

                // Anti-bot delay
                sleep(rand(3, 6));
            } catch (\Exception $e) {
                $errorDetails = "Offset: $offset | " . $e->getMessage();
                $this->error("❌ Error at offset $offset: " . $e->getMessage());
                Log::error("Scrape error: " . $e->getMessage());

                // Update scrape log with failure
                $scrapeLog->update([
                    'status' => 'failed',
                    'error_message' => $errorDetails,
                    'message' => 'Failed at offset ' . $offset . ': ' . \Str::limit($e->getMessage(), 100),
                    'finished_at' => now(),
                ]);
                Log::info('Failed from js request'.$e->getMessage());
                return;
            }
        }

        // Update scrape log with success
        $scrapeLog->update([
            'status' => 'success',
            'message' => "Successfully scraped $totalScraped listings from Auction.com",
            'finished_at' => now(),
        ]);

        $this->info("✅ Scraping completed!");
    }

    /**
     * Call Node.js Puppeteer scraper
     */
    private function scrapeWithPuppeteer($limit, $offset)
    {
        $scraperPath = base_path('scraper.js');

        $process = new Process(['node', $scraperPath, $limit, $offset]);
        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception("Puppeteer failed: " . $process->getErrorOutput());
        }

        $output = $process->getOutput();
        $data = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON from scraper: " . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Get auction details (simplified for now)
     */
    private function getAuctionDetails(array $ids)
    {
        // For now, return empty details
        // You can implement this similarly using Puppeteer if needed
        return ['data' => ['online_segments_batched' => []]];
    }

    private function parseAddress($line)
    {
        if (!$line) return [null, null, null, null];

        $parts = explode(',', $line);

        $city = trim($parts[0] ?? '');
        $stateZip = trim($parts[1] ?? '');
        $county = trim($parts[2] ?? '');

        $state = null;
        $zip = null;

        if ($stateZip) {
            $tmp = explode(' ', $stateZip);
            $state = $tmp[0] ?? null;
            $zip = $tmp[1] ?? null;
        }

        return [$city, $state, $zip, $county];
    }
}
