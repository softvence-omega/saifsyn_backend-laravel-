<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoanCalculatorController extends Controller
{
    public function calculate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'interest_rate' => 'required|numeric',
            'repayment_period' => 'required|integer'
        ]);

        $P = $request->amount;
        $r = $request->interest_rate / 12 / 100;
        $n = $request->repayment_period;

        $emi = $P * $r * pow(1 + $r, $n) / (pow(1 + $r, $n) - 1);
        $totalRepayment = $emi * $n;
        $interest = $totalRepayment - $P;

        return response()->json([
            'emi' => round($emi, 2),
            'totalRepayment' => round($totalRepayment, 2),
            'interest' => round($interest, 2)
        ]);
    }
}
