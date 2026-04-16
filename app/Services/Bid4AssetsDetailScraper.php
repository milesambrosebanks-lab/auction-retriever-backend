<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\AuctionListing;
use Illuminate\Support\Facades\Log;

class Bid4AssetsDetailScraper
{
    protected string $baseUrl = 'https://www.bid4assets.com';
    protected Client $client;
    protected string $source    = 'bid4assets';


    // US State codes
    protected array $stateCodes = [
        'AL',
        'AK',
        'AZ',
        'AR',
        'CA',
        'CO',
        'CT',
        'DE',
        'FL',
        'GA',
        'HI',
        'ID',
        'IL',
        'IN',
        'IA',
        'KS',
        'KY',
        'LA',
        'ME',
        'MD',
        'MA',
        'MI',
        'MN',
        'MS',
        'MO',
        'MT',
        'NE',
        'NV',
        'NH',
        'NJ',
        'NM',
        'NY',
        'NC',
        'ND',
        'OH',
        'OK',
        'OR',
        'PA',
        'RI',
        'SC',
        'SD',
        'TN',
        'TX',
        'UT',
        'VT',
        'VA',
        'WA',
        'WV',
        'WI',
        'WY',
    ];

    public function __construct()
    {
        $this->client = new Client([
            'base_uri'        => 'https://www.bid4assets.com', // ← এটা add করুন
            'cookies'         => new CookieJar(),
            'allow_redirects' => true,
            'timeout'         => 30,
            'verify'          => false,
            'headers'         => [
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/122.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ],
        ]);
    }

    // ── সব listing এর detail fetch করুন ────────────────────────────────────
    public function syncAll(int $batchSize = 50): int
    {
        // যেগুলোর state নেই সেগুলো fetch করুন
        $listings = AuctionListing::whereNull('state')
            ->whereNotNull('auction_id')->where('source', $this->source)
            ->limit($batchSize)->orderBY('id','asc')
            ->get();

        $updated = 0;

        foreach ($listings as $listing) {
            $detail = $this->fetchDetail($listing->auction_id);
            Log::info("Fetched #{$listing->auction_id}: " . json_encode($detail));  // ← add করো
            // Log::info($detail);

            if ($detail) {
                $listing->update($detail);
                $updated++;
                Log::info("Detail synced: #{$listing->auction_id} — {$detail['city']}, {$detail['state']}");
            }

            sleep(1); // rate limit
        }

        return $updated;
    }

    // ── Single listing detail fetch ──────────────────────────────────────────
    public function fetchDetail(string $auctionId): ?array
    {
        try {
            $response = $this->client->get("/auction/{$auctionId}");
            $html     = (string) $response->getBody();

            return $this->parseDetail($html);
        } catch (\Exception $e) {
            Log::warning("Detail fetch failed for #{$auctionId}: " . $e->getMessage());
            return null;
        }
    }

    protected function parseDetail(string $html): ?array
    {
        try {
            $crawler = new Crawler($html);
            $data    = [];

            // ── Location: "Cherokee Village, AR 72529" ──────────────────
            $locationRaw = '';
            $address = '';
            $country = '';

            $crawler->filter('.auction-info-summary table tr')->each(function (Crawler $row) use (&$locationRaw,&$address,&$country) {
                $th = $row->filter('td strong');


                if ($th->count() > 0 && str_contains($th->text(), 'Location')) {
                    $tds = $row->filter('td');

                    if ($tds->count() > 1) {
                        // ✅ html() নিয়ে <br> দিয়ে split করো — text() নয়
                        $rawHtml = $tds->eq(1)->html();
                        $lines   = preg_split('/<br\s*\/?>/i', $rawHtml);
                        $lines   = array_map(fn($l) => trim(strip_tags($l)), $lines);
                        $lines   = array_filter($lines); // empty lines বাদ

                        // শেষ line এ "City, ST ZIP" থাকে
                        //  $locationRaw = end($lines);
                        // Parse address, city/state/zip, country from lines
                        $address = trim($lines[0] ?? '');
                        $country = trim(end($lines)); // 'United States'
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if (preg_match('/^(.+),\s*([A-Z]{2})\s*(\d{5})?$/i', $line)) {
                                $locationRaw = $line;
                                break;
                            }
                        }

                        // Log::info($address);
                    }
                }
            });


            // Method 2: item-specifics-table Address row থেকে (fallback)
            if (empty($locationRaw)) {
                $crawler->filter('.item-specifics-table table tr')->each(function (Crawler $row) use (&$locationRaw) {
                    $td = $row->filter('td');
                    if ($td->count() > 0 && str_contains($td->eq(0)->text(), 'Address')) {
                        if ($td->count() > 1) {
                            // "Cherokee Village, AR 72529\nUnited States" — first line নিন
                            $rawText     = $td->eq(1)->html();
                            $lines       = explode('<br>', $rawText);
                            $locationRaw = trim(strip_tags($lines[0]));
                        }
                    }
                });
            }

            // ── Location parse: "City, ST ZIP" ──────────────────────────
            $parsed = $this->parseLocation($locationRaw);
            $data   = array_merge($data, $parsed);
            $data['address'] = $address;
            $data['country'] = $country;

            // ── Auction Started ──────────────────────────────────────────
            $auctionStarted = '';
            $auctionClosed  = '';

            $crawler->filter('.auction-data-table table tr')->each(function (Crawler $row) use (&$auctionStarted, &$auctionClosed) {
                $th = $row->filter('th');
                $td = $row->filter('td');

                if ($th->count() > 0 && $td->count() > 0) {
                    $label = trim($th->text());
                    $value = trim($td->text());

                    if (str_contains($label, 'Auction Started')) {
                        $auctionStarted = $value;
                    }
                    if (str_contains($label, 'Auction Closes')) {
                        $auctionClosed = $value;
                    }
                }
            });

            // ── Date parse: "03-14-26 02:00 PM ET" ──────────────────────
            $data['auction_started_at'] = $this->parseDate($auctionStarted);
            $data['auction_date']       = $this->parseDate($auctionClosed);

            // ── Parcel Number ────────────────────────────────────────────
            $parcelNumber = '';
            $crawler->filter('.item-specifics-table table tr')->each(function (Crawler $row) use (&$parcelNumber) {
                $tds = $row->filter('td');
                if ($tds->count() > 1 && str_contains($tds->eq(0)->text(), 'Parcel Number')) {
                    $parcelNumber = trim($tds->eq(1)->text());
                }
            });
            $data['parcel_number'] = $parcelNumber ?: null;

            return $data;
        } catch (\Exception $e) {
            Log::warning("Detail parse error: " . $e->getMessage());
            return null;
        }
    }

    // ── "Cherokee Village, AR 72529" → city, state, zip ─────────────────────
    protected function parseLocation(string $raw): array
    {
        $raw    = trim($raw);
        $city   = null;
        $state  = null;
        $zip    = null;
        $county = null;

        if (empty($raw)) {
            return compact('city', 'state', 'zip', 'county');
        }

        // Pattern: "Cherokee Village, AR 72529"
        // Pattern: "Montello, NV 89830"
        if (preg_match('/^(.+),\s*([A-Z]{2})\s*(\d{5})?$/i', $raw, $m)) {
            $city  = trim($m[1]);
            $state = strtoupper(trim($m[2]));
            $zip   = isset($m[3]) ? trim($m[3]) : null;

            // State code valid কিনা check
            if (!in_array($state, $this->stateCodes)) {
                $state = null;
            }
        }

        // County extract — "Cherokee Village" থেকে county guess করা যায় না
        // তাই city থেকেই county set করুন (যদি "County" শব্দ থাকে)
        if ($city && str_contains(strtolower($city), 'county')) {
            $county = $city;
        }

        return compact('city', 'state', 'zip', 'county');
    }

    // ── "03-14-26 02:00 PM ET" → Carbon ─────────────────────────────────────
    protected function parseDate(string $raw): ?string
    {
        $raw = trim($raw);
        if (empty($raw)) return null;

        try {
            // "03-14-26 02:00 PM ET" format
            $cleaned = preg_replace('/\s+ET$/i', '', $raw);   // ET remove
            $cleaned = preg_replace('/\s+/', ' ', $cleaned);  // extra space

            // "03-14-26 02: 30 PM" → "03-14-26 02:30 PM" (space after colon fix)
            $cleaned = preg_replace('/:\s+/', ':', $cleaned);

            $date = \Carbon\Carbon::createFromFormat('m-d-y h:i A', $cleaned, 'America/New_York');
            return $date->utc()->toDateTimeString();
        } catch (\Exception $e) {
            Log::warning("Date parse failed for '$raw': " . $e->getMessage());
            return null;
        }
    }
}
