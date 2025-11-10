<?php

namespace Tests\Common;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

trait FakesNewsApiResponses
{
    protected function fakeNewsApiResponses(): void
    {
        Http::preventStrayRequests();

        Http::fake([
            'api.nytimes.com/*' => Http::response(
                File::json(storage_path('data/ny_times.json'))
            ),

            'content.guardianapis.com/*' => Http::response(
                File::json(storage_path('data/the_guardian.json'))
            ),

            'newsapi.org/*' => Http::response(
                File::json(storage_path('data/news_api.json'))
            ),
        ]);
    }

    protected function saveArticlesForAllSources(): void
    {
        $this->artisan('news:update');
    }
}
