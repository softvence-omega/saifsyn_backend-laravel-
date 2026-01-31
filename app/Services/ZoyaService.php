<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ZoyaService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.zoya.url'); 
        $this->apiKey = config('services.zoya.key');  
    }

    // All compliant stocks
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
        return $this->sendQuery($query);
    }

    // Single stock advanced report
    public function getAdvancedReport($symbol)
    {
        $query = '
        query GetAdvancedReport {
          advancedCompliance {
            report(input: {
              symbol: "' . $symbol . '",
              methodology: AAOIFI
            }) {
              symbol
              rawSymbol
              name
              figi
              exchange
              status
              reportDate
              businessScreen
              financialScreen
              compliantRevenue
              nonCompliantRevenue
              questionableRevenue
              ... on AAOIFIReport {
                securitiesToMarketCapRatio 
                debtToMarketCapRatio 
              }
            }
          }
        }';
        return $this->sendQuery($query);
    }

    // Region reports
    public function getRegionReports($region)
    {
        $query = '
        query ListInternationalReports {
          advancedCompliance {
            reports(input: {
              region: "' . $region . '",
              methodology: AAOIFI
            }) {
              items {
                symbol
                rawSymbol
                name
                figi
                exchange
                status
                reportDate
                businessScreen
                financialScreen
                ... on AAOIFIReport {
                  debtToMarketCapRatio 
                  securitiesToMarketCapRatio 
                }
              }
              nextToken
            }
          }
        }';
        return $this->sendQuery($query);
    }

    // Fund reports
    public function getFunds($limit = 10, $nextToken = null)
    {
        $query = '
        query Funds($input: BasicFundsInput) {
          basicCompliance {
            funds(input: $input) {
              items {
                symbol
                name
                status
                reportDate
                holdingsAsOfDate
              }
              nextToken
            }
          }
        }';

        $variables = [
            "input" => [
                "limit" => $limit,
                "nextToken" => $nextToken
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl, [
            'query' => $query,
            'variables' => $variables
        ]);

        return $response->json();
    }

    // Helper to send query
    private function sendQuery($query)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl, ['query' => $query]);

        return $response->json();
    }
}
