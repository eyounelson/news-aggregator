<?php

namespace App\Services\News;

use App\Services\News\Data\ArticleData;
use App\Services\News\Factory\NewsAggregatorContract;
use App\Types\NewsSource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TheGuardianNews implements NewsAggregatorContract
{
    public function articles(): Collection
    {
        /**
         * Fetch articles using the Content API
         * See https://open-platform.theguardian.com/documentation/search
         */
        $baseUrl = 'https://content.guardianapis.com';
        $apiKey = config('services.the_guardian.api_key');

        $keywords = config('app.news.keywords');

        $query = [
            'api-key' => $apiKey,
            'page-size' => 50,
            'fq' => implode(' OR ', $keywords),
            'show-fields' => 'headline,body',
            'show-tags' => 'contributor,keyword',
            'show-elements' => 'image',
        ];

        $articles = Http::baseUrl($baseUrl)
            ->get('/search', $query)
            ->collect('response.results');

        return collect($articles)->map(fn ($article) => $this->formatArticle($article))->filter();
    }

    public function formatArticle(array $article): ?ArticleData
    {
        $mainImage = (array) Arr::first($article['elements'], fn ($el) => $el['type'] === 'image' && $el['relation'] === 'main');
        $imageUrl = Arr::get($mainImage, 'assets.0.file');

        try {
            return new ArticleData(
                source: NewsSource::TheGuardian,
                title: $article['webTitle'],
                excerpt: Str::limit($article['webTitle'], end: ''),
                content: strip_tags($article['fields']['body']),
                url: $article['webUrl'],
                imageUrl: $imageUrl,
                date: $article['webPublicationDate'],
                authors: collect($article['tags'])
                    ->where('type', 'contributor')
                    ->pluck('webTitle')
                    ->toArray(),
                categories: collect($article['tags'])
                    ->where('type', 'keyword')
                    ->pluck('webTitle')
                    ->toArray(),
            );
        } catch (Throwable $e) {
            Log::error("Article Parsing failed with error: {$e->getMessage()}", ['source' => 'NyTimes', 'article' => $article]);

            return null;
        }
    }
}
