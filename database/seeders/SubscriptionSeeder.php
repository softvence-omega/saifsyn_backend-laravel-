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
    // Beginner Plans
    // ==============================
    [
                'title' => 'Beginner 1 Month',
                'description' => 'Access to basic Shariah stock reports and core financial management tools.',
                'price' => 79,
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
                'price' => 299,
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
                'price' => 556,
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
            // Elite Plans
            // ==============================
            [
                'title' => 'Elite 1 Month',
                'description' => 'Full access to all Shariah compliance reports including ETF and complete financial tools.',
                'price' => 149,
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
                'price' => 799,
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
                'price' => 1399,
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

            // ==============================
            // Elite Pro Plans
            // ==============================
            [
                'title' => 'Elite Pro 1 Month',
                'description' => 'Premium access with essential Shariah reports and financial tools.',
                'price' => 299,
                'features' => json_encode([
                    'Specific Stock Shariah Report',
                    'ETF / Fund Reports',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Unlimited Watchlist',
                    'Advanced Wealth Dashboard & Insights',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 1,
                'is_popular' => true,
                'status' => true,
            ],
            [
                'title' => 'Elite Pro 6 Months',
                'description' => 'Premium access with essential Shariah reports and financial tools.',
                'price' => 1299,
                'features' => json_encode([
                    'Specific Stock Shariah Report',
                    'ETF / Fund Reports',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Unlimited Watchlist',
                    'Advanced Wealth Dashboard & Insights',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 6,
                'is_popular' => true,
                'status' => true,
            ],
            [
                'title' => 'Elite Pro 12 Months',
                'description' => 'Premium access with essential Shariah reports and financial tools.',
                'price' => 2399,
                'features' => json_encode([
                    'Specific Stock Shariah Report',
                    'ETF / Fund Reports',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Unlimited Watchlist',
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