<?php

namespace App\Jobs;

use App\Models\OurAnalysis;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendAnalysisNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $analysis;

    /**
     * Create a new job instance.
     */
    public function __construct(OurAnalysis $analysis)
    {
        $this->analysis = $analysis;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get all users with FCM token
            $tokens = User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();

            if (empty($tokens)) {
                Log::info("No FCM tokens found for analysis ID {$this->analysis->id}");
                return;
            }

            $notification = [
                'registration_ids' => $tokens,
                'notification' => [
                    'title' => "Stock Update: " . $this->analysis->symbol,
                    'body' => $this->analysis->note ?? 'New stock analysis available',
                    'click_action' => url('/analyses/' . $this->analysis->id),
                ],
                'data' => [
                    'analysis_id' => $this->analysis->id,
                    'symbol' => $this->analysis->symbol,
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . config('services.fcm.key'),
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $notification);

            // Log FCM response for debugging
            Log::info("FCM notification sent for analysis ID {$this->analysis->id}", [
                'response' => $response->body()
            ]);

        } catch (\Exception $e) {
            Log::error("FCM notification failed for analysis ID {$this->analysis->id}: " . $e->getMessage());
            throw $e; // rethrow to let failed job be recorded in failed_jobs table
        }
    }
}
