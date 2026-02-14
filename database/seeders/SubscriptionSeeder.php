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
            // Beginner Plan (Core App Access)
            // ==============================
            [
                'title' => 'Beginner',
                'description' => 'Access to core financial tools, analysis, news, and basic stock reports.',
                'price' => 79.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Market News Access',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Watchlist Management',
                    'Basic Wealth Dashboard Overview',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 1,
                'is_popular' => false,
                'status' => true,
            ],
            [
                'title' => 'Beginner 6 Months',
                'description' => 'Access to core financial tools, analysis, news, and basic stock reports.',
                'price' => 299.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Market News Access',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Watchlist Management',
                    'Basic Wealth Dashboard Overview',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 6,
                'is_popular' => false,
                'status' => true,
            ],
            [
                'title' => 'Beginner 12 Months',
                'description' => 'Access to core financial tools, analysis, news, and basic stock reports.',
                'price' => 556.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Market News Access',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Watchlist Management',
                    'Basic Wealth Dashboard Overview',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 12,
                'is_popular' => false,
                'status' => true,
            ],

            // ==============================
            // Elite Plan (Advanced Reports)
            // ==============================
            [
                'title' => 'Elite',
                'description' => 'Unlock advanced and international stock reports with full dashboard features.',
                'price' => 79.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Advanced Stock Reports',
                    'International Stock Reports',
                    'ETF / Fund Reports',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Unlimited Watchlist',
                    'Market News Access',
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
                'description' => 'Unlock advanced and international stock reports with full dashboard features.',
                'price' => 299.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Advanced Stock Reports',
                    'International Stock Reports',
                    'ETF / Fund Reports',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Unlimited Watchlist',
                    'Market News Access',
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
                'description' => 'Unlock advanced and international stock reports with full dashboard features.',
                'price' => 556.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Advanced Stock Reports',
                    'International Stock Reports',
                    'ETF / Fund Reports',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Unlimited Watchlist',
                    'Market News Access',
                    'Advanced Wealth Dashboard & Insights',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 12,
                'is_popular' => true,
                'status' => true,
            ],

            // ==============================
            // Elite Pro Plan (Full Premium Access)
            // ==============================
            [
                'title' => 'Elite Pro',
                'description' => 'Complete premium access including regional reports, API access, and advanced insights.',
                'price' => 79.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Advanced Stock Reports',
                    'International Stock Reports',
                    'Regional Reports',
                    'MENA Region Compliance Reports',
                    'ETF / Fund Reports',
                    'Access to All Exchanges',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Unlimited Watchlist',
                    'Advanced Wealth Dashboard & Insights',
                    'Market News Access',
                    'API Access',
                    'Priority Support / Consultation',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 1,
                'is_popular' => true,
                'status' => true,
            ],
            [
                'title' => 'Elite Pro 6 Months',
                'description' => 'Complete premium access including regional reports, API access, and advanced insights.',
                'price' => 299.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Advanced Stock Reports',
                    'International Stock Reports',
                    'Regional Reports',
                    'MENA Region Compliance Reports',
                    'ETF / Fund Reports',
                    'Access to All Exchanges',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Unlimited Watchlist',
                    'Advanced Wealth Dashboard & Insights',
                    'Market News Access',
                    'API Access',
                    'Priority Support / Consultation',
                    'Profile & Settings Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 6,
                'is_popular' => true,
                'status' => true,
            ],
            [
                'title' => 'Elite Pro 12 Months',
                'description' => 'Complete premium access including regional reports, API access, and advanced insights.',
                'price' => 556.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Advanced Stock Reports',
                    'International Stock Reports',
                    'Regional Reports',
                    'MENA Region Compliance Reports',
                    'ETF / Fund Reports',
                    'Access to All Exchanges',
                    'Full Financial Management (Income, Expense, Loan)',
                    'Full Analysis Access',
                    'Unlimited Watchlist',
                    'Advanced Wealth Dashboard & Insights',
                    'Market News Access',
                    'API Access',
                    'Priority Support / Consultation',
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
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }
}
