<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AuctionService
{
    private string $url = 'https://graph.auction.com/graphql';

    private function headers()
    {
        return [
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'origin' => 'https://www.auction.com',
            'referer' => 'https://www.auction.com/',
            'auction-graph-source' => 'auctioncom',
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];
    }

    // 🔥 Warmup request (important)
    public function warmup()
    {
        Http::withHeaders($this->headers())
            ->get('https://www.auction.com');
    }

    // 🔹 Get Listings
    public function getListings($offset = 0, $limit = 50)
    {
        return Http::retry(3, 1000)
            ->withOptions([
                'verify' => false,
                'http_errors' => false,
            ])
            ->withHeaders($this->headers())
            ->post($this->url, [
                'query' => 'query resiSearch($filters: ListingCompatabilityFilters!) {
                    seek_listings_from_filters(filters: $filters) {
                        total_count
                        content {
                            ... on Listing {
                                listing_id
                                listing_status
                                formatted_address(format: DOUBLE_LINE)
                                listing_page_path
                            }
                        }
                    }
                }',
                'variables' => [
                    'filters' => [
                        'listing_type' => 'active',
                        'usecode_property_type' => 'resi_sfr',
                        'sort' => 'auction_date_order,resi_sort_v2',
                        'limit' => $limit,
                        'offset' => $offset,
                        'version' => 1,
                    ]
                ]
            ])->json();
    }

    // 🔹 Get Auction Details
    public function getAuctionDetails(array $ids)
    {
        return Http::retry(3, 1000)
            ->withOptions([
                'verify' => false,
                'http_errors' => false,
            ])
            ->withHeaders($this->headers())
            ->post($this->url, [
                'query' => 'query ($listingIds: [ID!]!) {
                    online_segments_batched(filters: { listing_ids: $listingIds }) {
                        listing_id
                        start_date
                        initial_end_date
                        starting_bid_amount
                        segment_status
                    }
                }',
                'variables' => [
                    'listingIds' => $ids
                ]
            ])->json();
    }
}
