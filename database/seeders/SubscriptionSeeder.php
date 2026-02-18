<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [

            // ==============================
            // Beginner Plan (Core Access)
            // ==============================
            [
                'title' => 'Beginner',
                'description' => 'Access to basic Shariah stock reports and core financial management tools.',
                'price' => 79.00,
                'features' => json_encode([
                    'Specific Stock Shariah Report',
                    'Limited US Market Shariah Reports',
                    'Compliant Stock List Access',
                    'Financial Management (Income, Expense, Loan)',
                    'Basic Wealth Dashboard Overview',
                    'Watchlist Management (Limited)',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 1,
                'is_popular' => false,
                'status' => true,
            ],

            [
                'title' => 'Beginner 6 Months',
                'description' => 'Access to basic Shariah stock reports and core financial management tools.',
                'price' => 299.00,
                'features' => json_encode([
                    'Specific Stock Shariah Report',
                    'Limited US Market Shariah Reports',
                    'Compliant Stock List Access',
                    'Financial Management (Income, Expense, Loan)',
                    'Basic Wealth Dashboard Overview',
                    'Watchlist Management (Limited)',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 6,
                'is_popular' => false,
                'status' => true,
            ],

            [
                'title' => 'Beginner 12 Months',
                'description' => 'Access to basic Shariah stock reports and core financial management tools.',
                'price' => 556.00,
                'features' => json_encode([
                    'Specific Stock Shariah Report',
                    'Limited US Market Shariah Reports',
                    'Compliant Stock List Access',
                    'Financial Management (Income, Expense, Loan)',
                    'Basic Wealth Dashboard Overview',
                    'Watchlist Management (Limited)',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 12,
                'is_popular' => false,
                'status' => true,
            ],

            // ==============================
            // Elite Plan (Full Reports Access)
            // ==============================
            [
                'title' => 'Elite',
                'description' => 'Full access to all Shariah compliance reports including ETF and complete financial tools.',
                'price' => 149.00,
                'features' => json_encode([
                    'Specific Stock Shariah Report',
                    'All US Market Shariah Reports',
                    'All Compliant Stock Reports',
                    'ETF / Fund Shariah Reports',
                    'Unlimited Watchlist',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Advanced Wealth Dashboard & Insights',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 1,
                'is_popular' => true,
                'status' => true,
            ],

            [
                'title' => 'Elite 6 Months',
                'description' => 'Full access to all Shariah compliance reports including ETF and complete financial tools.',
                'price' => 799.00,
                'features' => json_encode([
                    'Specific Stock Shariah Report',
                    'All US Market Shariah Reports',
                    'All Compliant Stock Reports',
                    'ETF / Fund Shariah Reports',
                    'Unlimited Watchlist',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Advanced Wealth Dashboard & Insights',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 6,
                'is_popular' => true,
                'status' => true,
            ],

            [
                'title' => 'Elite 12 Months',
                'description' => 'Full access to all Shariah compliance reports including ETF and complete financial tools.',
                'price' => 1399.00,
                'features' => json_encode([
                    'Specific Stock Shariah Report',
                    'All US Market Shariah Reports',
                    'All Compliant Stock Reports',
                    'ETF / Fund Shariah Reports',
                    'Unlimited Watchlist',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Advanced Wealth Dashboard & Insights',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 12,
                'is_popular' => true,
                'status' => true,
            ],

        ];

        foreach ($plans as $plan) {
            DB::table('subscription_plans')->updateOrInsert(
                ['title' => $plan['title']],
                array_merge($plan, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
