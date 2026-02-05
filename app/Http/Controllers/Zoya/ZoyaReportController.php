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

    // -----------------------------
    // 1. Get shariah compliance rating for a specific stock
    // -----------------------------
    public function getStockReport(Request $request)
    {
        $symbol = $request->query('symbol');
        if (!$symbol) {
            return response()->json(['error' => 'Symbol is required'], 400);
        }

        return response()->json($this->zoya->getStockReport($symbol));
    }

    // -----------------------------
    // 2. Get all shariah compliance ratings for US market
    // -----------------------------
    public function getAllReports(Request $request)
    {
        $nextToken = $request->query('nextToken', null);

        return response()->json($this->zoya->getAllReports($nextToken));
    }

    // -----------------------------
    // 3. Get all shariah compliant stocks in US market
    // -----------------------------
    public function getAllCompliantStocks(Request $request)
    {
        $nextToken = $request->query('nextToken', null);

        return response()->json($this->zoya->getAllCompliantStocks($nextToken));
    }

    // -----------------------------
    // 4. Get full shariah compliance report for a specific stock
    // -----------------------------
    public function getAdvancedReport(Request $request)
    {
        $symbol = $request->query('symbol');
        if (!$symbol) {
            return response()->json(['error' => 'Symbol is required'], 400);
        }

        return response()->json($this->zoya->getAdvancedReport($symbol));
    }




     // -----------------------------
// Get NON-US / International stock compliance report
// Usage: /api/zoya/international-report?symbol=0R0K-LN
// -----------------------------
public function getInternationalReport(Request $request)
{
    $symbol = $request->query('symbol');

    if (!$symbol) {
        return response()->json([
            'error' => 'Symbol is required (Example: 0R0K-LN)'
        ], 400);
    }

    // Directly return server response without forcing nulls
    $response = $this->zoya->getInternationalReport($symbol);

    return response()->json($response);
}


}
