<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZoyaService;
use App\Models\News;

class FetchZoyaNews extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'zoya:fetch-news';

    /**
     * The console command description.
     */
    protected $description = 'Fetch latest news from Zoya API and save to DB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching news from Zoya...');

        try {
            $service = new ZoyaService();
            $response = $service->getNews();

            if (!isset($response['data']['news']['items']) || empty($response['data']['news']['items'])) {
                $this->warn('No news found from Zoya.');
                return 0;
            }

            foreach ($response['data']['news']['items'] as $item) {
                // Avoid duplicates by title + publishedAt
                $news = News::updateOrCreate(
                    [
                        'title' => $item['title'],
                        'published_at' => $item['publishedAt'],
                    ],
                    [
                        'description' => $item['description'] ?? null,
                        'category' => $item['category'] ?? null,
                        'image' => $item['image'] ?? null,
                        'status' => 'published', // Zoya news is always published
                    ]
                );

                $this->info("Saved news: {$news->title}");
            }

            $this->info('All news fetched successfully!');

        } catch (\Exception $e) {
            $this->error('Failed to fetch news: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
