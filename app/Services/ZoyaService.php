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

    // -----------------------------
    // 1. Get shariah compliance rating for a specific stock
    // -----------------------------
    public function getStockReport($symbol)
    {
        $query = '
        query {
          basicCompliance {
            report(symbol: "' . $symbol . '") {
              symbol
              name
              exchange
              status
            }
          }
        }';

        return $this->sendQuery($query);
    }

    // -----------------------------
    // 2. Get all shariah compliance ratings for US market
    // -----------------------------
    public function getAllReports($nextToken = null)
    {
        $input = $nextToken ? "{ input: { nextToken: \"$nextToken\" } }" : "";

        $query = '
        query {
          basicCompliance {
            reports' . $input . ' {
              items {
                symbol
                name
                exchange
                status
              }
              nextToken
            }
          }
        }';

        return $this->sendQuery($query);
    }

    // -----------------------------
// 3. Get all shariah compliant stocks in US market (fixed)
// -----------------------------
public function getAllCompliantStocks($nextToken = null)
{
    $input = "{ filters: { status: COMPLIANT } }";
    if ($nextToken) {
        $input = "{ nextToken: \"$nextToken\", filters: { status: COMPLIANT } }";
    }

    $query = '
    query {
      basicCompliance {
        reports(input: ' . $input . ') {
          items {
            symbol
            reportDate
            name
            exchange
          }
          nextToken
        }
      }
    }';

    return $this->sendQuery($query);
}

    // -----------------------------
    // 4. Get full shariah compliance report for a specific stock
    // -----------------------------
    public function getAdvancedReport($symbol)
    {
        $query = '
        query {
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





    // -----------------------------
// Get shariah compliance report for NON-US stock
// -----------------------------
public function getInternationalReport($symbol)
{
    $query = '
    query GetInternationalReport {
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
          ... on AAOIFIReport {
            debtToMarketCapRatio
            securitiesToMarketCapRatio
          }
        }
      }
    }';

    return $this->sendQuery($query);
}






// -----------------------------
// Get shariah compliance reports for a region
// Usage: pass region ISO code (e.g., "GB", "US") and optional nextToken
// -----------------------------
public function getRegionalReports($region, $nextToken = null)
{
    // Build input string for GraphQL (AAOIFI as enum, no quotes)
    $inputString = '{region: "' . $region . '", methodology: AAOIFI';
    if ($nextToken) {
        $inputString .= ', nextToken: "' . $nextToken . '"';
    }
    $inputString .= '}';

    // GraphQL query
    $query = '
    query {
      advancedCompliance {
        reports(input: ' . $inputString . ') {
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





// -----------------------------
// Get all compliant stocks from MENA regions
// -----------------------------
public function getMENAScreens()
{
    $query = '
    query ListMENAScreens {
      advancedCompliance {
        menaScreens {
          rawSymbol
          name
          exchange
          region
          status
        }
      }
    }';

    return $this->sendQuery($query);
}


//reports
public function getETFReports($nextToken = null)
{
    $input = $nextToken ? "{ input: { nextToken: \"$nextToken\" } }" : "";

    $query = '
    query {
        basicCompliance {
            funds' . $input . ' {
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

    return $this->sendQuery($query);
}





//news from zoya
public function getNews($nextToken = null)
{
    $input = $nextToken ? "{ input: { nextToken: \"$nextToken\" } }" : "";

    $query = '
    query {
        news' . $input . ' {
            items {
                title
                description
                category
                image
                publishedAt
            }
            nextToken
        }
    }';

    $response = $this->sendQuery($query);

    // Debug log: uncomment if needed
    // \Log::info('Zoya news response: ', $response);

    // Safety: always return items array
    return $response['data']['news']['items'] ?? [];
}



public function getRegions()
{
    $query = '
    query {
        advancedCompliance {
            regions
        }
    }';

    return $this->sendQuery($query);
}








// -----------------------------
// Zakat calculation
// -----------------------------
// -----------------------------
// Zakat calculation
// -----------------------------
public function calculateZakat(array $holdings)
{
    if (empty($holdings)) {
        return [
            'success' => false,
            'message' => 'No holdings provided.'
        ];
    }

    // GraphQL expects enums without quotes
    $holdingsGraphQL = array_map(function ($holding) {
        $strategy = strtoupper($holding['strategy']); // ACTIVE or PASSIVE
        return sprintf(
            '{symbol: "%s", strategy: %s, quantity: %s, unitPrice: %s}',
            $holding['symbol'],
            $strategy,
            $holding['quantity'],
            $holding['unitPrice']
        );
    }, $holdings);

    $holdingsString = '[' . implode(',', $holdingsGraphQL) . ']';

    $query = "
    query {
        calculate(holdings: $holdingsString) {
            zakatLiableAmount
            zakatDue
            currency
            holdings {
                symbol
                strategy
                currency
                marketValue
                zakatLiableAmount
                zakatDue
                calculationMethod
            }
        }
    }";

    $response = $this->sendQuery($query);

    // Safety check for API errors
    if (!isset($response['data']['calculate'])) {
        return [
            'success' => false,
            'message' => 'Failed to calculate zakat. Please check your input or API key.',
            'raw_response' => $response,
        ];
    }

    return [
        'success' => true,
        'data' => $response['data']['calculate']
    ];
}




    // -----------------------------
    // Helper function to send GraphQL request
    // -----------------------------
    private function sendQuery($query)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl, ['query' => $query]);

        return $response->json();
    }
}
