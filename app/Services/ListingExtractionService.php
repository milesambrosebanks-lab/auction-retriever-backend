<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ExtractionLog;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ListingExtractionService
{

    public function extractFromSource($sourceUrl)
    {
        try {

            $response = Http::get($sourceUrl);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch source');
            }

            $data = $response->json();

            foreach ($data as $item) {

                Listing::updateOrCreate(
                    ['source_url' => $item['url']],
                    [
                        'title' => $item['title'] ?? null,
                        'county' => $this->normalizeCounty($item['county'] ?? null),
                        'state' => $this->normalizeState($item['state'] ?? null),
                        'property_type' => $item['type'] ?? null,
                        'starting_bid' => $this->normalizeCurrency($item['bid'] ?? null),
                        'auction_date' => Carbon::parse($item['auction_date'])->format('Y-m-d'),
                        'source_website' => parse_url($sourceUrl, PHP_URL_HOST)
                    ]
                );
            }

            ExtractionLog::updateOrCreate(
                ['source' => $sourceUrl],
                [
                    'status' => true,
                    'last_successful_run' => now(),
                    'message' => 'Extraction successful'
                ]
            );
        } catch (\Exception $e) {

            $this->retryExtraction($sourceUrl);
        }
    }


    private function retryExtraction($sourceUrl)
    {
        for ($i = 1; $i <= 2; $i++) {

            try {

                $response = Http::get($sourceUrl);

                if ($response->successful()) {

                    $this->extractFromSource($sourceUrl);
                    return;
                }
            } catch (\Exception $e) {
            }
        }

        ExtractionLog::create([
            'source' => $sourceUrl,
            'status' => false,
            'last_successful_run' => now(),
            'message' => 'Extraction failed after retries'
        ]);
    }

    private function normalizeCurrency($value)
    {
        return preg_replace('/[^\d.]/', '', $value);
    }

    private function normalizeState($state)
    {
        return strtoupper($state);
    }

    private function normalizeCounty($county)
    {
        return ucfirst(strtolower($county));
    }
}
