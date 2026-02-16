<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ZakatCalculation;
use App\Models\ZakatHolding;
use Illuminate\Support\Facades\Auth;

class ZakatController extends Controller
{




/**
 * List ZakatCalculations for the logged-in user
 */
public function index()
{
    try {
        // Fetch calculations only for the authenticated user
        $calculations = ZakatCalculation::with('holdings')
            ->where('user_id', Auth::id())
            ->get();

        return response()->json([
            'success' => true,
            'data' => $calculations
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch your Zakat calculations.',
            'error' => $e->getMessage()
        ], 500);
    }
}


    /**
     * Calculate Zakat for user holdings and save to DB
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'holdings' => 'required|array|min:1',
            'holdings.*.symbol' => 'required|string',
            'holdings.*.strategy' => 'required|in:ACTIVE,PASSIVE',
            'holdings.*.quantity' => 'required|numeric|min:0',
            'holdings.*.unitPrice' => 'required|numeric|min:0',
        ]);

        $totalLiable = 0;
        $totalMarket = 0;
        $currency = 'USD'; // Default, you can make dynamic if needed

        // Create ZakatCalculation first
        $calculation = ZakatCalculation::create([
            'user_id' => Auth::id() ?: 1,
            'zakat_liable_amount' => 0, // will update later
            'zakat_due' => 0,            // will update later
            'currency' => $currency,
        ]);

        $holdingsResult = [];

        foreach ($request->holdings as $holding) {
            $marketValue = $holding['quantity'] * $holding['unitPrice'];

            // Zakat liable amount
            if ($holding['strategy'] === 'ACTIVE') {
                $liableAmount = $marketValue;
                $calculationMethod = 'TREAT_AS_CASH';
            } else {
                $liableAmount = $marketValue * 0.30; // Passive fallback
                $calculationMethod = 'FALLBACK_30';
            }

            $zakatDue = round($liableAmount * 0.025, 2);

            $totalLiable += $liableAmount;
            $totalMarket += $marketValue;

            // Save each holding
            $holdingModel = ZakatHolding::create([
                'zakat_calculation_id' => $calculation->id,
                'symbol' => $holding['symbol'],
                'strategy' => $holding['strategy'],
                'currency' => $currency,
                'quantity' => $holding['quantity'],
                'unit_price' => $holding['unitPrice'],
                'market_value' => $marketValue,
                'zakat_liable_amount' => $liableAmount,
                'zakat_due' => $zakatDue,
                'calculation_method' => $calculationMethod,
            ]);

            $holdingsResult[] = $holdingModel;
        }

        // Update total zakat in calculation
        $calculation->update([
            'zakat_liable_amount' => $totalLiable,
            'zakat_due' => round($totalLiable * 0.025, 2),
        ]);

        // Return result in Zoya-like structure
        return response()->json([
            'success' => true,
            'message' => 'Zakat calculated successfully.',
            'data' => $calculation->load('holdings')
        ]);
    }
}
