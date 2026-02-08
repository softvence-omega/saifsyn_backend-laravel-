<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WealthDashboardController extends Controller
{
    // -----------------------------
    // Get user's wealth summary (with optional date range)
    // -----------------------------
    public function index(Request $request)
    {
        try {
            $userId = Auth::id();

            // -----------------------------
            // Validate from_date & to_date
            // -----------------------------
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

            $netSavings = $totalIncome - $totalExpense - $totalLoan;

            // -----------------------------
            // Balance status
            // -----------------------------
            $balanceStatus = $netSavings >= 0 ? 'positive' : 'negative';
            $warningMessage = $netSavings < 0 ? 'Your net savings is negative!' : null;

            return response()->json([
                'success' => true,
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'totalLoan' => $totalLoan,
                'netSavings' => $netSavings,
                'balanceStatus' => $balanceStatus,
                'warning' => $warningMessage,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wealth summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
