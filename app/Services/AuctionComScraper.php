<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\AuctionListing;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuctionComScraper
{
    protected Client $client;
    protected string $graphqlUrl = 'https://graph.auction.com/graphql';

    protected string $query = '
        fragment ListingCardFields on Listing {
            __typename
            listing_id
            listing_status_group
            listing_status
            listing_status_label(intent: SEARCH)
            primary_photo
            listing_page_path
            formatted_address(format: DOUBLE_LINE)
            listing_configuration {
                product_type
                occupancy_status
                asset_type
            }
            venue { venue_type }
            event { event_code trustee_sale }
            valuation { seller_current_value_amount }
            seller_property {
                street_description
                municipality
                country_primary_subdivision
                country_secondary_subdivision
                postal_code
            }
            primary_property {
                property_id
                summary {
                    total_bedrooms
                    total_bathrooms
                    square_footage
                    lot_size
                    year_built
                    structure_type_code
                    address {
                        coordinates { lon lat }
                    }
                }
            }
            auction {
                start_date
                end_date
                starting_bid
                is_online
                visible_auction_start_date_time
            }
            selling_method(resolvePolicy: CACHE_ONLY) {
                __typename
                ... on OnlineAuctionSegment {
                    starting_bid_amount
                    current_highest_bid { bid_amount }
                    bid_count
                }
                ... on LiveAuctionSegment {
                    _alias_LiveAuctionSegment__starting_bid_amount: starting_bid_amount
                    current_highest_bid { bid_amount }
                }
            }
        }

        query resiSearch_blueprint_seekListingsFromFilters(
            $filters: ListingCompatabilityFilters!,
            $aggregationFields: [String!]!,
            $hasAuthenticatedUser: Boolean!,
            $requiresAggregation: Boolean!
        ) {
            seek_listings_from_filters(filters: $filters) {
                total_count
                total_pages
                size
                current_page
                content { ...ListingCardFields }
            }
        }
    ';

    public function __construct()
    {
        $this->client = new Client([
            'proxy'   => env('AUCTION_COM_PROXY'),
            'timeout' => 30,
            'verify'  => false,
            'headers' => [
                'Content-Type'         => 'application/json',
                'Accept'               => 'application/json',
                'Origin'               => 'https://www.auction.com',
                'Referer'              => 'https://www.auction.com/',
                'auction-graph-source' => 'auctioncom',
                'User-Agent'           => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36',
            ],
        ]);
    }

    // ── সব pages fetch করো ──────────────────────────────────────
    public function syncAll(): int
    {
        $page    = 0;
        $total   = 0;
        $updated = 0;

        do {
            $result = $this->fetchPage($page);

            if (!$result) break;

            $listings    = $result['content'] ?? [];
            $totalPages  = $result['total_pages'] ?? 0;

            foreach ($listings as $listing) {
                $this->upsertListing($listing);
                $updated++;
            }

            Log::info("AuctionCom page {$page}/{$totalPages} — " . count($listings) . " listings");

            $page++;
            sleep(2); // rate limit

        } while ($page < $totalPages);

        return $updated;
    }

    // ── Single page fetch ────────────────────────────────────────
    public function fetchPage(int $offset = 0, int $limit = 500): ?array
    {
        try {
            $response = $this->client->post($this->graphqlUrl, [
                'json' => [
                    'query'     => $this->query,
                    'variables' => [
                        'filters' => [
                            'listing_type' => 'active',
                            'sort'         => 'auction_date_order,resi_sort_v2',
                            'limit'        => $limit,
                            'version'      => 1,
                            'offset'       => $offset,
                        ],
                        'aggregationFields'    => [],
                        'hasAuthenticatedUser' => false,
                        'requiresAggregation'  => false,
                    ],
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            return $data['data']['seek_listings_from_filters'] ?? null;
        } catch (\Exception $e) {
            Log::error("AuctionCom fetch failed (page {$offset}): " . $e->getMessage());
            return null;
        }
    }

    // ── DB তে save করো ──────────────────────────────────────────
    protected function upsertListing(array $item): void
    {
        try {
            $address  = $item['formatted_address'] ?? [];
            $seller   = $item['seller_property'] ?? [];
            $auction  = $item['auction'] ?? [];
            $selling  = $item['selling_method'] ?? [];
            $summary  = $item['primary_property']['summary'] ?? [];

            // Bid amount
            $bidAmount = $selling['current_highest_bid']['bid_amount']
                ?? $selling['_alias_LiveAuctionSegment__starting_bid_amount']
                ?? $selling['starting_bid_amount']
                ?? null;

            AuctionListing::updateOrCreate(
                ['auction_id' => $item['listing_id']],
                [
                    'title'          => implode(', ', $address),
                    'source_url'     => 'https://www.auction.com' . ($item['listing_page_path'] ?? ''),
                    'image_url'      => $item['primary_photo'] ?? null,
                    'address'        => $seller['street_description'] ?? null,
                    'city'           => $seller['municipality'] ?? null,
                    'state'          => $seller['country_primary_subdivision'] ?? null,
                    'zip'            => $seller['postal_code'] ?? null,
                    'county'         => $seller['country_secondary_subdivision'] ?? null,
                    'country'        => 'United States',
                    'bid_amount'     => $bidAmount,
                    'bid_count'      => $selling['bid_count'] ?? null,
                    'auction_date'   => isset($auction['visible_auction_start_date_time'])
                        ? Carbon::parse($auction['visible_auction_start_date_time'])->toDateTimeString()
                        : null,
                    'type'           => $item['listing_configuration']['asset_type'] ?? null,
                    'channel_code'   => 'auction_com',
                    'scraped_at'     => now(),
                    'otp_verified_at' => now(), // email verified না — এটা আলাদা model এ
                ]
            );
        } catch (\Exception $e) {
            Log::warning("AuctionCom upsert failed #{$item['listing_id']}: " . $e->getMessage());
        }
    }
}
