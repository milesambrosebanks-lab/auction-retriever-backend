<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\AuctionListing;
use App\Models\ScrapeLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class Bid4AssetsScraper
{
    protected string $baseUrl   = 'https://www.bid4assets.com';
    protected string $endpoint  = '/channel/auctions/get';
    protected int    $pageSize  = 50;
    protected int    $maxRetry  = 2; // client requirement: minimum 2 retries
    protected string $source    = 'bid4assets';

    protected Client    $client;
    protected CookieJar $jar;
    protected string    $token = '';
    protected ?ScrapeLog $currentLog = null;

    public function __construct()
    {
        $this->bootClient();
    }

    protected function bootClient(): void
    {
        $this->jar = new CookieJar();

        $this->client = new Client([
            'base_uri'        => $this->baseUrl,
            'cookies'         => $this->jar,
            'allow_redirects' => true,
            'timeout'         => 30,
            'verify'          => false,
            'headers'         => [
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/122.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept-Encoding' => 'gzip, deflate, br',
            ],
        ]);
    }

    protected function initSession(): bool
    {
        try {
            $response = $this->client->get('/real-estate-auctions', [
                'headers' => [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ],
            ]);

            $html    = (string) $response->getBody();
            $crawler = new Crawler($html);
            $node    = $crawler->filter('input[name="__RequestVerificationToken"]');

            if ($node->count() === 0) {
                Log::error('Bid4Assets: Token not found — site structure may have changed');
                return false;
            }

            $this->token = $node->first()->attr('value') ?? '';
            Log::info("Bid4Assets: Session ready — " . count($this->jar->toArray()) . " cookies");
            return true;
        } catch (\Exception $e) {
            Log::error('Bid4Assets: initSession failed — ' . $e->getMessage());
            return false;
        }
    }

    protected function isValidResponse(string $html): bool
    {
        if (str_contains($html, 'Showing 0 Item(s)')) return false;
        if (!str_contains($html, 'cat-tb')) return false;
        return true;
    }

    // ── Main entry point ────────────────────────────────────────────────────
    public function scrapeAll(
        string $channelCode   = '22',
        string $categoryCode  = '',
        string $sortColumn    = 'Featured',
        string $sortDirection = 'DESC',
        string $locatedState  = ''
    ): int {

        $this->currentLog = ScrapeLog::create([
            'source'     => $this->source,
            'status'     => 'running',
            'attempt'    => 1,
            'message'    => 'Extraction started — initializing session...',
            'started_at' => now(),
        ]);

        $attempt    = 1;
        $totalSaved = 0;

        while ($attempt <= $this->maxRetry + 1) {

            $this->currentLog->update([
                'attempt' => $attempt,
                'message' => "Attempt $attempt of " . ($this->maxRetry + 1) . " — connecting to source...",
            ]);

            try {
                $result = $this->runScrape(
                    $channelCode,
                    $categoryCode,
                    $sortColumn,
                    $sortDirection,
                    $locatedState
                );

                if ($result > 0) {
                    // ── Success ──
                    $totalSaved = $result;

                    $this->currentLog->update([
                        'status'        => 'success',
                        'total_scraped' => $totalSaved,
                        'finished_at'   => now(),
                        'message'       => "Extraction completed successfully. $totalSaved listings saved on attempt $attempt.",
                        'error_message' => null,
                    ]);

                    Cache::put("scrape_last_success_{$this->source}", now()->toDateTimeString(), 86400 * 7);
                    Cache::put("scrape_last_count_{$this->source}", $totalSaved, 86400 * 7);

                    Log::info("Bid4Assets: SUCCESS — $totalSaved items on attempt $attempt");
                    break;
                } else {
                    throw new \Exception("Source returned 0 items — site may be blocking requests or structure changed");
                }
            } catch (\Exception $e) {

                $errorMsg = $e->getMessage();
                Log::warning("Bid4Assets: Attempt $attempt failed — $errorMsg");

                if ($attempt > $this->maxRetry) {
                    // ── Final failure ──
                    $this->currentLog->update([
                        'status'        => 'failed',
                        'total_scraped' => 0,
                        'finished_at'   => now(),
                        'message'       => "Extraction failed after $attempt attempt(s). No data saved.",
                        'error_message' => $errorMsg,
                    ]);

                    Cache::put("scrape_last_failed_{$this->source}", now()->toDateTimeString(), 86400 * 7);
                    Log::error("Bid4Assets: FAILED after {$this->maxRetry} retries — $errorMsg");
                    break;
                }

                // ── Retry ──
                $waitSeconds = $attempt * 5;

                $this->currentLog->update([
                    'message'       => "Attempt $attempt failed — retrying in {$waitSeconds}s... (Error: $errorMsg)",
                    'error_message' => $errorMsg,
                ]);

                Log::info("Bid4Assets: Waiting {$waitSeconds}s before retry $attempt...");
                sleep($waitSeconds);

                $this->bootClient();
                $attempt++;
            }
        }

        return $totalSaved;
    }

    // ── Actual scrape logic ──────────────────────────────────────────────────
    protected function runScrape(
        string $channelCode,
        string $categoryCode,
        string $sortColumn,
        string $sortDirection,
        string $locatedState
    ): int {
        if (!$this->initSession()) {
            throw new \Exception("Session initialization failed");
        }

        $page       = 1;
        $totalSaved = 0;
        $retryPage  = 0;

        while (true) {
            $html = $this->fetchPage($channelCode, $categoryCode, $page, $sortColumn, $sortDirection, $locatedState);

            if (empty($html) || !$this->isValidResponse($html)) {
                if ($retryPage >= 2) {
                    throw new \Exception("Invalid response on page $page after $retryPage retries");
                }
                $retryPage++;
                sleep(3);
                $this->bootClient();
                $this->initSession();
                continue;
            }

            $retryPage = 0;
            $items     = $this->parseHtml($html);
            $count     = count($items);

            Log::info("Bid4Assets: Page $page — $count items");

            if ($count === 0) break;

            foreach ($items as $item) {
                $this->saveItem($item, $channelCode, $categoryCode);
                $totalSaved++;
            }

            // Log আপডেট করুন — progress track
            $this->currentLog->update(['total_scraped' => $totalSaved]);

            if ($count < $this->pageSize) break;

            $page++;
            sleep(rand(1, 3));
        }

        return $totalSaved;
    }

    protected function fetchPage(
        string $channelCode,
        string $categoryCode,
        int    $page,
        string $sortColumn,
        string $sortDirection,
        string $locatedState
    ): ?string {
        try {
            $formData = [
                'channelCode'        => $channelCode,
                'categoryCode'       => $categoryCode,
                'currentPage'        => (string) $page,
                'pageSize'           => (string) $this->pageSize,
                'lev3'               => '',
                'sortOrderColumn'    => $sortColumn,
                'sortOrderDirection' => $sortDirection,
                'specialtyChannel'   => '',
                'locatedState'       => $locatedState,
            ];

            if ($this->token) {
                $formData['__RequestVerificationToken'] = $this->token;
            }

            $response = $this->client->post($this->endpoint, [
                'headers' => [
                    'Accept'           => 'text/html, */*; q=0.01',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Content-Type'     => 'application/x-www-form-urlencoded; charset=UTF-8',
                    'Referer'          => $this->baseUrl . '/real-estate-auctions',
                ],
                'form_params' => $formData,
            ]);

            return (string) $response->getBody();
        } catch (\Exception $e) {
            Log::error("Bid4Assets fetchPage error: " . $e->getMessage());
            return null;
        }
    }

    protected function parseHtml(string $html): array
    {
        $items   = [];
        $crawler = new Crawler($html);

        $crawler->filter('table.cat-tb tbody tr')->each(function (Crawler $row) use (&$items) {

            if ($row->filter('select')->count() > 0) return;
            if ($row->filter('.auction-title')->count() > 0) return;
            if ($row->filter('td')->count() < 6) return;

            try {
                $titleNode  = $row->filter('td.w270 span[linktype="channelauction"]');
                $title      = $titleNode->count() > 0 ? trim($titleNode->text()) : '';

                $linkNode   = $row->filter('td.w270 a');
                $href       = $linkNode->count() > 0 ? $linkNode->attr('href') : '';
                $sourceUrl  = $href ? $this->baseUrl . $href : '';

                preg_match('/\/auction\/(\d+)/i', $href, $matches);
                $auctionId  = $matches[1] ?? '';

                $type       = trim($row->filter('td.w140')->count() > 0
                    ? $row->filter('td.w140')->text() : '');

                $w100       = $row->filter('td.w100');
                $currentBid = $w100->count() > 0 ? trim($w100->eq(0)->text()) : '';
                $bidCount   = $w100->count() > 1 ? (int) trim($w100->eq(1)->text()) : 0;
                $timeLeft   = $w100->count() > 2 ? trim($w100->eq(2)->text()) : '';

                $imgNode    = $row->filter('td.w40 span.channel-thumbnail-desktop img');
                $image      = '';
                if ($imgNode->count() > 0) {
                    $onload = $imgNode->attr('onload') ?? '';
                    if (preg_match("/imgload\(this,'([^']+)'\)/", $onload, $imgMatch)) {
                        $image = $imgMatch[1];
                    } else {
                        $image = $imgNode->attr('src') ?? '';
                    }
                }

                if ($auctionId || $title) {
                    $items[] = [
                        'auction_id'  => $auctionId,
                        'title'       => $title,
                        'type'        => $type,
                        'current_bid' => $currentBid,
                        'bid_count'   => $bidCount,
                        'time_left'   => $timeLeft,
                        'image_url'   => $image,
                        'source_url'  => $sourceUrl,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("Parse row error: " . $e->getMessage());
            }
        });

        return $items;
    }

    protected function saveItem(array $item, string $channelCode, string $categoryCode): void
    {
        AuctionListing::updateOrCreate(
            ['auction_id' => $item['auction_id'] ?: $item['source_url']],
            [
                'title'         => $item['title'],
                'type'          => $item['type'],
                'current_bid'   => $item['current_bid'],
                'bid_count'     => $item['bid_count'],
                'time_left'     => $item['time_left'],
                'image_url'     => $item['image_url'],
                'source_url'    => $item['source_url'],
                'channel_code'  => $channelCode,
                'category_code' => $categoryCode,
                'scraped_at'    => now(),
            ]
        );
    }
}
