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
use Illuminate\Support\Str;

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
        $tokens = User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();

        if (empty($tokens)) return;

        $notification = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $this->analysis->title,
                'body' => Str::limit($this->analysis->content, 100),
                'click_action' => url('/analysis/' . $this->analysis->slug),
            ],
            'data' => [
                'analysis_id' => $this->analysis->id,
                'slug' => $this->analysis->slug,
            ]
        ];

        Http::withHeaders([
            'Authorization' => 'key=' . config('services.fcm.key'),
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $notification);
    }
}
