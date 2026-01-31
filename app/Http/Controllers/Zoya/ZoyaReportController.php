<?php

namespace App\Http\Controllers\Zoya;

use App\Http\Controllers\Controller;
use App\Services\ZoyaService;
use Illuminate\Http\Request;

class ZoyaReportController extends Controller
{
    protected $zoya;

    public function __construct(ZoyaService $zoya)
    {
        $this->zoya = $zoya;
    }

    /**
     * Get Zoya reports based on query params
     * 
     * Query params:
     * - symbol : string (single stock)
     * - region : string (region reports)
     * - fund   : string (fund/ETF)
     * - us_non_us : string ("US" or "NON-US")
     * - nextToken : string (for pagination)
     */
    public function getAllReports(Request $request)
{
    $user = $request->user();
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $symbol    = $request->query('symbol');
    $region    = $request->query('region');
    $fund      = $request->query('fund');
    $usNonUs   = $request->query('us_non_us');
    $nextToken = $request->query('nextToken');

    // Priority: Symbol > Fund > Region > US/Non-US
    if ($symbol) {
        // Single stock (US or Non-US)
        $data = $this->zoya->getAdvancedReport($symbol);
    } elseif ($fund) {
        // Fund/ETF reports with pagination
        $data = $this->zoya->getFunds(10, $nextToken);
    } elseif ($region) {
        // Region specific reports
        $data = $this->zoya->getRegionReports($region);
    } elseif ($usNonUs) {
        if ($usNonUs === 'US') {
            $data = $this->zoya->getAllReports(); // US stocks
        } else {
            // Non-US needs region input, fallback if not provided
            $region = $region ?? 'GB'; // default example region
            $data = $this->zoya->getRegionReports($region);
        }
    } else {
        // Default: all US compliant stocks
        $data = $this->zoya->getAllReports();
    }

    return response()->json([
        'data' => $data
    ]);
}
}