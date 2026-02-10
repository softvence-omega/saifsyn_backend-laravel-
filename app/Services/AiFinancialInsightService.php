<?php

namespace App\Services;

class AiFinancialInsightService
{
    public function generateInsights($income, $expense, $loan, $netBalance)
    {
        $insights = [];

        // Avoid division by zero
        $safeIncome = max($income, 1);

        // Expense vs Income %
        $expensePercent = round(($expense / $safeIncome) * 100);

        // Loan vs Income %
        $loanPercent = round(($loan / $safeIncome) * 100);

        // Savings %
        $savingsPercent = round(($netBalance / $safeIncome) * 100);

        // -----------------------------
        // Insights logic
        // -----------------------------
        if ($income == 0 && $expense > 0) {
            $insights[] = "You have expenses but no income. Add income sources.";
        }

        if ($expensePercent > 100) {
            $insights[] = "Overspending ({$expensePercent}% of income). Reduce urgently.";
        } elseif ($expensePercent > 70) {
            $insights[] = "High spending ({$expensePercent}%). Try budgeting.";
        }

        if ($netBalance < 0) {
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

        // -----------------------------
        // Score calculation
        // -----------------------------
        if ($income == 0 && $expense == 0 && $loan == 0 && $netBalance == 0) {
            $score = 0; // realistic score when nothing exists
            $insights = ["No financial activity recorded yet."];
        } else {
            // Net balance vs income based score
            $score = 50; // base score
            if ($netBalance > 0) {
                $score += min(50, round(($netBalance / $safeIncome) * 50));
            } elseif ($netBalance < 0) {
                $score -= min(50, round((abs($netBalance) / $safeIncome) * 50));
            }

            // Ensure score is between 0 and 100
            $score = max(0, min(100, $score));
        }

        if (empty($insights)) {
            $insights[] = "Your financial status looks stable.";
        }

        return [
            'score' => $score,
            'insights' => $insights,
        ];
    }
}
