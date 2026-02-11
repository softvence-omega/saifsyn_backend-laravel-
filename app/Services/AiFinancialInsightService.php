<?php

namespace App\Services;

class AiFinancialInsightService
{
    public function generateInsights($income, $expense, $loan)
    {
        $insights = [];

        $safeIncome = max($income, 1); // avoid division by zero

        // Expense %
        $expensePercent = round(($expense / $safeIncome) * 100);

        // Loan %
        $loanPercent = round(($loan / $safeIncome) * 100);

        // Real savings
        $savings = $income - $expense - $loan;
        $savingsPercent = round(($savings / $safeIncome) * 100);

        // ---------------------------
        // Insights logic
        // ---------------------------
        if ($income == 0 && $expense > 0) {
            $insights[] = "You have expenses but no income. Add income sources.";
        }

        if ($expensePercent > 100) {
            $insights[] = "Overspending ({$expensePercent}% of income). Reduce urgently.";
        } elseif ($expensePercent > 70) {
            $insights[] = "High spending ({$expensePercent}%). Try budgeting.";
        }

        if ($savings < 0) {
            $insights[] = "Negative balance. Focus on saving.";
        }

        if ($loan > 0) {
            if ($loanPercent > 80) {
                $insights[] = "Very high loan burden ({$loanPercent}%). Avoid new loans.";
            } elseif ($loanPercent > 40) {
                $insights[] = "Moderate loan pressure ({$loanPercent}%). Repay faster.";
            } else {
                $insights[] = "Loan level manageable ({$loanPercent}%).";
            }
        }

        if ($savingsPercent > 30) {
            $insights[] = "Excellent savings ({$savingsPercent}%). Consider investing.";
        } elseif ($savingsPercent > 10) {
            $insights[] = "Good savings ({$savingsPercent}%). Keep it up.";
        } elseif ($savingsPercent < 5) {
            $insights[] = "Low savings ({$savingsPercent}%). Try saving more.";
        }

        // Score calculation (0-100)
        if ($income == 0 && $expense == 0 && $loan == 0) {
            $score = 50;
            $insights = ["No financial activity recorded yet."];
        } else {
            $score = 50;
            if ($savings > 0) {
                $score += min(50, round(($savings / $safeIncome) * 50));
            } else {
                $score -= min(50, round((abs($savings) / $safeIncome) * 50));
            }
            $score = max(0, min(100, $score));
        }

        return [
            'score' => $score,
            'savings' => $savings,
            'savingsPercent' => $savingsPercent,
            'insights' => $insights,
        ];
    }
}
