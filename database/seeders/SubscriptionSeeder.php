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
            // Beginner Plan
            [
                'title' => 'Beginner',
                'description' => 'A simple directory listing with basic stock reports.',
                'price' => 79.00, // Monthly
                'features' => json_encode([
                    'Basic Stock Reports',
                ]),
                'duration_type' => 'month',
                'duration_value' => 1,
                'is_popular' => false,
                'status' => true,
            ],
            [
                'title' => 'Beginner 6 Months',
                'description' => 'A simple directory listing with basic stock reports.',
                'price' => 299.00, // Before 348
                'features' => json_encode([
                    'Basic Stock Reports',
                ]),
                'duration_type' => 'month',
                'duration_value' => 6,
                'is_popular' => false,
                'status' => true,
            ],
            [
                'title' => 'Beginner 12 Months',
                'description' => 'A simple directory listing with basic stock reports.',
                'price' => 556.00, // Before 696
                'features' => json_encode([
                    'Basic Stock Reports',
                ]),
                'duration_type' => 'month',
                'duration_value' => 12,
                'is_popular' => false,
                'status' => true,
            ],

            // Elite Plan
            [
                'title' => 'Elite',
                'description' => 'For businesses that want full visibility and basic booking tools.',
                'price' => 79.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Advanced Stock Reports',
                    'International Stock Reports',
                    'ETF / Fund Reports',
                    'API Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 1,
                'is_popular' => true,
                'status' => true,
            ],
            [
                'title' => 'Elite 6 Months',
                'description' => 'For businesses that want full visibility and basic booking tools.',
                'price' => 299.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Advanced Stock Reports',
                    'International Stock Reports',
                    'ETF / Fund Reports',
                    'API Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 6,
                'is_popular' => true,
                'status' => true,
            ],
            [
                'title' => 'Elite 12 Months',
                'description' => 'For businesses that want full visibility and basic booking tools.',
                'price' => 556.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Advanced Stock Reports',
                    'International Stock Reports',
                    'ETF / Fund Reports',
                    'API Access',
                ]),
                'duration_type' => 'month',
                'duration_value' => 12,
                'is_popular' => true,
                'status' => true,
            ],

            // Elite Pro Plan
            [
                'title' => 'Elite Pro',
                'description' => 'For businesses that want advanced booking controls and payment tools.',
                'price' => 79.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Advanced Stock Reports',
                    'International Stock Reports',
                    'Regional Reports',
                    'MENA Region Compliance',
                    'ETF / Fund Reports',
                    'Access to All Exchanges',
                    'API Access',
                    'Priority Support / Consultation',
                ]),
                'duration_type' => 'month',
                'duration_value' => 1,
                'is_popular' => true,
                'status' => true,
            ],
            [
                'title' => 'Elite Pro 6 Months',
                'description' => 'For businesses that want advanced booking controls and payment tools.',
                'price' => 299.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Advanced Stock Reports',
                    'International Stock Reports',
                    'Regional Reports',
                    'MENA Region Compliance',
                    'ETF / Fund Reports',
                    'Access to All Exchanges',
                    'API Access',
                    'Priority Support / Consultation',
                ]),
                'duration_type' => 'month',
                'duration_value' => 6,
                'is_popular' => true,
                'status' => true,
            ],
            [
                'title' => 'Elite Pro 12 Months',
                'description' => 'For businesses that want advanced booking controls and payment tools.',
                'price' => 556.00,
                'features' => json_encode([
                    'Basic Stock Reports',
                    'Advanced Stock Reports',
                    'International Stock Reports',
                    'Regional Reports',
                    'MENA Region Compliance',
                    'ETF / Fund Reports',
                    'Access to All Exchanges',
                    'API Access',
                    'Priority Support / Consultation',
                ]),
                'duration_type' => 'month',
                'duration_value' => 12,
                'is_popular' => true,
                'status' => true,
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('subscription_plans')->updateOrInsert(
                ['title' => $plan['title']], // unique key
                array_merge($plan, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }
}
