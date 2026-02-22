<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [

            // ==============================
            // Beginner Plans (Basic, Limited)
            // ==============================
            [
                'title' => 'Beginner 1 Month',
                'description' => 'Basic Shariah stock reports and core financial tools.',
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
                'description' => 'Basic Shariah stock reports and core financial tools.',
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
                'description' => 'Basic Shariah stock reports and core financial tools.',
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
            // Elite Plans (Partial Access)
            // ==============================
            [
                'title' => 'Elite 1 Month',
                'description' => 'Full access to most Shariah compliance reports, limited ETF/funds and analysis.',
                'price' => 149,
                'features' => json_encode([
                    'Specific Stock Shariah Report',
                    'All US Market Shariah Reports (Limited)',
                    'All Compliant Stock Reports (Limited)',
                    'ETF / Fund Shariah Reports (Limited)',
                    'Unlimited Watchlist',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access (Limited)',
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
                'description' => 'Full access to most Shariah compliance reports, limited ETF/funds and analysis.',
                'price' => 799,
                'features' => json_encode([
                    'Specific Stock Shariah Report',
                    'All US Market Shariah Reports (Limited)',
                    'All Compliant Stock Reports (Limited)',
                    'ETF / Fund Shariah Reports (Limited)',
                    'Unlimited Watchlist',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access (Limited)',
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
                'description' => 'Full access to most Shariah compliance reports, limited ETF/funds and analysis.',
                'price' => 1399,
                'features' => json_encode([
                    'Specific Stock Shariah Report',
                    'All US Market Shariah Reports (Limited)',
                    'All Compliant Stock Reports (Limited)',
                    'ETF / Fund Shariah Reports (Limited)',
                    'Unlimited Watchlist',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access (Limited)',
                    'Advanced Wealth Dashboard & Insights',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 12,
                'is_popular' => true,
                'status' => true,
            ],

            // ==============================
            // Elite Pro Plans (All Access)
            // ==============================
            [
                'title' => 'Elite Pro 1 Month',
                'description' => 'Complete premium access including all Shariah reports, ETF, and analysis tools.',
                'price' => 299,
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
                'title' => 'Elite Pro 6 Months',
                'description' => 'Complete premium access including all Shariah reports, ETF, and analysis tools.',
                'price' => 1299,
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
                'title' => 'Elite Pro 12 Months',
                'description' => 'Complete premium access including all Shariah reports, ETF, and analysis tools.',
                'price' => 2399,
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