<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialManagerController extends Controller
{
    // -----------------------------
    // 1. Get financial summary with negative balance handling
    // -----------------------------
    public function index(Request $request)
    {
        try {
            $userId = Auth::id();

            // Validate date format
            if ($request->filled('from_date') && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->from_date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid from_date format. Use YYYY-MM-DD'
                ], 422);
            }

            if ($request->filled('to_date') && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->to_date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid to_date format. Use YYYY-MM-DD'
                ], 422);
            }

            // -----------------------------
            // Build filtered queries
            // -----------------------------
            $incomeQuery = Income::where('user_id', $userId);
            $expenseQuery = Expense::where('user_id', $userId);
            $loanQuery = Loan::where('user_id', $userId);

            if ($request->filled('from_date')) {
                $incomeQuery->whereDate('date', '>=', $request->from_date);
                $expenseQuery->whereDate('date', '>=', $request->from_date);
                $loanQuery->whereDate('start_date', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $incomeQuery->whereDate('date', '<=', $request->to_date);
                $expenseQuery->whereDate('date', '<=', $request->to_date);
                $loanQuery->whereDate('start_date', '<=', $request->to_date);
            }

            // -----------------------------
            // Calculate totals
            // -----------------------------
            $totalIncome = $incomeQuery->sum('amount');
            $totalExpense = $expenseQuery->sum('amount');
            $totalLoan = $loanQuery->sum('amount');

            $netBalance = $totalIncome - $totalExpense;
            $balanceStatus = $netBalance >= 0 ? 'positive' : 'negative';
            $warningMessage = $netBalance < 0 ? 'Your balance is negative!' : null;

            return response()->json([
                'success' => true,
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'totalLoan' => $totalLoan,
                'netBalance' => $netBalance,
                'balanceStatus' => $balanceStatus,
                'warning' => $warningMessage,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch financial summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
