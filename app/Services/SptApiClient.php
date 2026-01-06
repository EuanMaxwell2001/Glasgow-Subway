<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SptApiClient
{
    private string $baseUrl;
    private string $source;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('spt.api_base');
        $this->source = config('spt.source');
        $this->timeout = config('spt.timeout');
    }

    /**
     * Fetch all disruptions from the SPT API
     * 
     * @return array Array of disruption items
     * @throws \Exception
     */
    public function fetchAllDisruptions(): array
    {
        if ($this->source === 'fixture') {
            return $this->fetchFromFixtures();
        }

        return $this->fetchFromApi();
    }

    /**
     * Fetch disruptions from live API
     */
    private function fetchFromApi(): array
    {
        $allResults = [];
        $page = 1;
        $totalPages = 1;

        try {
            do {
                Log::info("Fetching SPT disruptions page {$page}");

                $response = Http::timeout($this->timeout)
                    ->get($this->baseUrl, [
                        'category' => 'all',
                        'order' => 'descending',
                        'page' => $page,
                    ]);

                if (!$response->successful()) {
                    throw new \Exception("API request failed with status {$response->status()}");
                }

                $data = $response->json();

                if (!isset($data['results'])) {
                    throw new \Exception("Invalid API response: missing 'results' field");
                }

                $allResults = array_merge($allResults, $data['results']);

                // Update total pages from first response
                if ($page === 1 && isset($data['pages'])) {
                    $totalPages = $data['pages'];
                }

                $page++;

            } while ($page <= $totalPages);

            Log::info("Fetched " . count($allResults) . " total disruptions from {$totalPages} pages");

            return $allResults;

        } catch (\Exception $e) {
            Log::error("SPT API fetch error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch disruptions from fixture files
     */
    private function fetchFromFixtures(): array
    {
        Log::info("Fetching disruptions from fixtures");
        
        $allResults = [];
        $page = 1;

        // Try to load fixture files until we can't find one
        while (true) {
            $fixturePath = "fixtures/spt_disruptions_page{$page}.json";
            
            if (!Storage::exists($fixturePath)) {
                break;
            }

            $content = Storage::get($fixturePath);
            $data = json_decode($content, true);

            if (!isset($data['results'])) {
                Log::warning("Invalid fixture file: {$fixturePath}");
                break;
            }

            $allResults = array_merge($allResults, $data['results']);
            $page++;

            // If the fixture specifies total pages, respect that
            if (isset($data['pages']) && $page > $data['pages']) {
                break;
            }
        }

        Log::info("Loaded " . count($allResults) . " disruptions from " . ($page - 1) . " fixture files");

        return $allResults;
    }

    /**
     * Test API connection
     */
    public function testConnection(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl, [
                    'category' => 'all',
                    'order' => 'descending',
                    'page' => 1,
                ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'message' => $response->successful() 
                    ? 'API connection successful' 
                    : 'API connection failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 0,
                'message' => $e->getMessage(),
            ];
        }
    }
}
