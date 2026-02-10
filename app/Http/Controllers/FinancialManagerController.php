<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use App\Models\Loan;
use App\Services\AiFinancialInsightService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialManagerController extends Controller
{
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

            // Build queries
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

            // Totals
            $totalIncome = $incomeQuery->sum('amount');
            $totalExpense = $expenseQuery->sum('amount');
            $totalLoan = $loanQuery->sum('amount');

            $netBalance = $totalIncome - $totalExpense;
            $balanceStatus = $netBalance >= 0 ? 'positive' : 'negative';
            $warningMessage = $netBalance < 0 ? 'Your balance is negative!' : null;

            // AI Insights
            $aiService = new AiFinancialInsightService();
            $insights = $aiService->generateInsights($totalIncome, $totalExpense, $totalLoan, $netBalance);

            // Realistic score calculation
            if ($totalIncome + $totalExpense + $totalLoan == 0) {
                $score = 50; // Neutral if no activity
            } else {
                $score = round(($netBalance / max($totalIncome, 1)) * 100);
                $score = max(0, min(100, $score)); // Ensure 0-100
            }

            return response()->json([
                'success' => true,
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'totalLoan' => $totalLoan,
                'netBalance' => $netBalance,
                'balanceStatus' => $balanceStatus,
                'warning' => $warningMessage,
                'ai_insights' => [
                    'score' => $score,
                    'insights' => $insights
                ]
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
