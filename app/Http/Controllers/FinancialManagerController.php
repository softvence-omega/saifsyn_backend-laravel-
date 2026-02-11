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
            foreach (['from_date', 'to_date'] as $field) {
                if ($request->filled($field) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->$field)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invalid {$field} format. Use YYYY-MM-DD"
                    ], 422);
                }
            }

            // Base queries
            $incomeQuery = Income::where('user_id', $userId);
            $expenseQuery = Expense::where('user_id', $userId);
            $loanQuery = Loan::where('user_id', $userId);

            // Apply filters
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

            // Check if any data exists
            if (!$incomeQuery->exists() && !$expenseQuery->exists() && !$loanQuery->exists()) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => $request->filled('from_date') || $request->filled('to_date')
                        ? 'No financial data for selected period'
                        : 'No financial data available'
                ]);
            }

            // Totals
            $totalIncome = $incomeQuery->sum('amount');
            $totalExpense = $expenseQuery->sum('amount');
            $totalLoan = $loanQuery->sum('amount');

            // Net balance
            $netBalance = $totalIncome - $totalExpense - $totalLoan;
            $balanceStatus = $netBalance >= 0 ? 'positive' : 'negative';

            // Transaction details with date
            $incomes = $incomeQuery->get(['amount', 'date']);
            $expenses = $expenseQuery->get(['amount', 'date']);
            $loans = $loanQuery->get(['amount', 'start_date']);

            // AI insights
            $aiService = new AiFinancialInsightService();
            $aiInsights = $aiService->generateInsights($totalIncome, $totalExpense, $totalLoan);

            return response()->json([
                'success' => true,
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'totalLoan' => $totalLoan,
                'netBalance' => $netBalance,
                'balanceStatus' => $balanceStatus,
                'incomes' => $incomes,
                'expenses' => $expenses,
                'loans' => $loans,
                'ai_insights' => $aiInsights
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
