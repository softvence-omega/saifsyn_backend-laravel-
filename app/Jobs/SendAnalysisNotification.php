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
        // Get all users with FCM token
        $tokens = User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();

        if (empty($tokens)) return;

        $notification = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => "Stock Update: " . $this->analysis->symbol,
                'body' => $this->analysis->note ?? 'New stock analysis available',
                'click_action' => url('/analyses/' . $this->analysis->id), // link to analysis page
            ],
            'data' => [
                'analysis_id' => $this->analysis->id,
                'symbol' => $this->analysis->symbol,
            ]
        ];

        Http::withHeaders([
            'Authorization' => 'key=' . config('services.fcm.key'),
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $notification);
    }
}
