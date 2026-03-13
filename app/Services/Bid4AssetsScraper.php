<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\AuctionListing;
use Illuminate\Support\Facades\Log;

class Bid4AssetsScraper
{
    protected string $baseUrl  = 'https://www.bid4assets.com';
    protected string $endpoint = '/channel/auctions/get';
    protected int    $pageSize = 50;

    protected string $csrfToken = '';
    protected string $cookies   = '';

    protected array $baseHeaders = [
        'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
        'Accept-Language' => 'en-US,en;q=0.9',
        'Origin'          => 'https://www.bid4assets.com',
        'Referer'         => 'https://www.bid4assets.com/real-estate-auctions',
    ];

    // ── Step 1: site visit করে token + cookie নিন ──────────────────────────
    protected function initSession(): bool
    {
        try {
            $response = Http::withHeaders(array_merge($this->baseHeaders, [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ]))->withOptions(['allow_redirects' => true])
              ->get($this->baseUrl . '/real-estate-auctions');

            if (!$response->successful()) {
                Log::error('Bid4Assets: Failed to init session. Status: ' . $response->status());
                return false;
            }

            // Cookie সংগ্রহ করুন
            $rawCookies = $response->cookies(); // Guzzle CookieJar
            $cookieStr  = '';
            foreach ($rawCookies as $cookie) {
                $cookieStr .= $cookie->getName() . '=' . $cookie->getValue() . '; ';
            }
            $this->cookies = rtrim($cookieStr, '; ');

            // CSRF Token সংগ্রহ করুন
            $html    = $response->body();
            $crawler = new Crawler($html);

            $tokenNode = $crawler->filter('input[name="__RequestVerificationToken"]');
            if ($tokenNode->count() > 0) {
                $this->csrfToken = $tokenNode->first()->attr('value') ?? '';
                Log::info('Bid4Assets: CSRF token found: ' . substr($this->csrfToken, 0, 20) . '...');
            } else {
                Log::warning('Bid4Assets: CSRF token not found in page');
            }

            Log::info('Bid4Assets: Session initialized. Cookies: ' . substr($this->cookies, 0, 100));
            return true;

        } catch (\Exception $e) {
            Log::error('Bid4Assets: initSession error: ' . $e->getMessage());
            return false;
        }
    }

    public function scrapeAll(
        string $channelCode   = '22',
        string $categoryCode  = '',
        string $sortColumn    = 'Featured',
        string $sortDirection = 'DESC',
        string $locatedState  = ''
    ): int {
        // প্রথমে session init করুন
        if (!$this->initSession()) {
            Log::error('Bid4Assets: Cannot start — session init failed');
            return 0;
        }

        $page       = 1;
        $totalSaved = 0;

        while (true) {
            $html = $this->fetchPage(
                $channelCode, $categoryCode,
                $page, $sortColumn, $sortDirection, $locatedState
            );

            if (empty($html)) {
                Log::warning("Bid4Assets: Empty response on page $page");
                break;
            }

            $items = $this->parseHtml($html);
            $count = count($items);

            Log::info("Bid4Assets: Page $page parsed — $count items found");

            if ($count === 0) break;

            foreach ($items as $item) {
                $this->saveItem($item, $channelCode, $categoryCode);
                $totalSaved++;
            }

            if ($count < $this->pageSize) break;

            $page++;
            sleep(1);
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
            $headers = array_merge($this->baseHeaders, [
                'Accept'           => 'text/html, */*; q=0.01',
                'X-Requested-With' => 'XMLHttpRequest',
                'Content-Type'     => 'application/x-www-form-urlencoded; charset=UTF-8',
            ]);

            // Cookie যোগ করুন
            if ($this->cookies) {
                $headers['Cookie'] = $this->cookies;
            }

            $postData = [
                'channelCode'               => $channelCode,
                'categoryCode'              => $categoryCode,
                'currentPage'               => $page,
                'pageSize'                  => $this->pageSize,
                'lev3'                      => '',
                'sortOrderColumn'           => $sortColumn,
                'sortOrderDirection'        => $sortDirection,
                'specialtyChannel'          => '',
                'locatedState'              => $locatedState,
            ];

            // CSRF token থাকলে যোগ করুন
            if ($this->csrfToken) {
                $postData['__RequestVerificationToken'] = $this->csrfToken;
            }

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($this->baseUrl . $this->endpoint, $postData);

            Log::info("Bid4Assets: Page $page — HTTP " . $response->status());
            Log::info("Bid4Assets: Response preview: " . substr($response->body(), 0, 300));

            if ($response->successful()) {
                return $response->body();
            }

            return null;

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

            try {
                if ($row->filter('td')->count() < 6) return;

                $titleNode = $row->filter('td.w270 span[linktype="channelauction"]');
                $title     = $titleNode->count() > 0 ? trim($titleNode->text()) : '';

                $linkNode  = $row->filter('td.w270 a');
                $href      = $linkNode->count() > 0 ? $linkNode->attr('href') : '';
                $sourceUrl = $href ? $this->baseUrl . $href : '';

                preg_match('/\/auction\/(\d+)/i', $href, $matches);
                $auctionId = $matches[1] ?? '';

                $type = trim($row->filter('td.w140')->count() > 0
                    ? $row->filter('td.w140')->text() : '');

                $w100      = $row->filter('td.w100');
                $currentBid = $w100->count() > 0 ? trim($w100->eq(0)->text()) : '';
                $bidCount   = $w100->count() > 1 ? (int) trim($w100->eq(1)->text()) : 0;
                $timeLeft   = $w100->count() > 2 ? trim($w100->eq(2)->text()) : '';

                $imgNode = $row->filter('td.w40 span.channel-thumbnail-desktop img');
                $image   = '';
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
                Log::warning("Bid4Assets parse row error: " . $e->getMessage());
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
