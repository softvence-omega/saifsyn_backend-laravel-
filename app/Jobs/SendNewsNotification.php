<?php

namespace App\Jobs;

use App\Models\News;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendNewsNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $news;

    public function __construct(News $news)
    {
        $this->news = $news;
    }

    public function handle()
    {
        $tokens = User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();

        if (empty($tokens)) return;

        $notification = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $this->news->title,
                'body' => \Str::limit($this->news->content, 100),
                'click_action' => url('/news/' . $this->news->slug),
            ],
            'data' => [
                'news_id' => $this->news->id,
                'slug' => $this->news->slug,
            ]
        ];

        Http::withHeaders([
            'Authorization' => 'key=' . config('services.fcm.key'),
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $notification);
    }
}
