<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ZoyaService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.zoya.url'); // GraphQL endpoint
        $this->apiKey = config('services.zoya.key');  // API key
    }

    public function getAllReports()
    {
        $query = '
        query ListCompliantStocks {
          basicCompliance {
            reports(input: { filters: { status: COMPLIANT } }) {
              items {
                symbol
                name
                exchange
                status
                reportDate
              }
              nextToken
            }
          }
        }';

        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
            'Content-Type' => 'application/json', // optional but recommended
        ])->post($this->baseUrl, [
            'query' => $query
        ]);

        return $response->json();
    }

    public function getSingleReport($symbol)
    {
        $query = '
        query GetAdvancedReport {
          advancedCompliance {
            report(input: { symbol: "' . $symbol . '", methodology: AAOIFI }) {
              symbol
              name
              exchange
              status
              compliantRevenue
              nonCompliantRevenue
            }
          }
        }';

        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl, [
            'query' => $query
        ]);

        return $response->json();
    }
}
