<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\News;
use Illuminate\Support\Facades\Http;

class SendNewsNotification extends Command
{
    protected $signature = 'news:notify {newsId}';
    protected $description = 'Send FCM notification for a specific news';

    public function handle()
    {
        $newsId = $this->argument('newsId');
        $news = News::find($newsId);

        if (!$news) {
            $this->error("News not found");
            return 1;
        }

        $tokens = \App\Models\User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
        if (empty($tokens)) {
            $this->info("No users with FCM tokens");
            return 0;
        }

        $notification = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $news->title,
                'body' => \Str::limit($news->content, 100),
                'click_action' => url('/news/' . $news->slug),
            ],
            'data' => [
                'news_id' => $news->id,
                'slug' => $news->slug,
            ]
        ];

        Http::withHeaders([
            'Authorization' => 'key=' . config('services.fcm.key'),
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $notification);

        $this->info("Notification sent for news ID: $newsId");
        return 0;
    }
}
