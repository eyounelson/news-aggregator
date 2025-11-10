<?php

namespace App\Services\News;

use App\Services\News\Data\ArticleData;
use App\Services\News\Factory\NewsAggregatorContract;
use App\Types\NewsSource;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use TypeError;

class NewsApiNews implements NewsAggregatorContract
{
    public function articles(): Collection
    {
        $baseUrl = 'https://newsapi.org/v2';

        $keywords = config('app.news.keywords');

        if (! $apiKey = config('services.news_api.api_key')) {
            throw new DomainException('NewsAPI.Org API Key has not been configured.');
        }

        $query = [
            'q' => implode(' OR ', $keywords),
            'pageSize' => 100,
            'sortBy' => 'publishedAt',
            'language' => 'en',
        ];

        $articles = Http::baseUrl($baseUrl)
            ->withHeader('x-api-key', $apiKey)
            ->throw()
            ->get('/everything', $query)->collect('articles');

        return $articles->map(fn ($article) => $this->formatArticle($article))->filter();
    }

    public function formatArticle(array $article): ?ArticleData
    {
        try {
            // Use the longest of content or description as the article body.
            $contents = Arr::only($article, ['content', 'description']);
            usort($contents, fn ($a, $b) => mb_strlen($b) > mb_strlen($a));
            $content = $contents[0];

            return new ArticleData(
                source: NewsSource::NewsApi,
                title: $article['title'],
                excerpt: $article['content'] ?? Str::limit($content, end: ''),
                content: $content,
                url: $article['url'],
                imageUrl: $article['urlToImage'],
                date: $article['publishedAt'],
                authors: [
                    $article['author'],
                ],
                categories: [],
            );
        } catch (TypeError $e) {
            Log::error("Article Parsing failed with error: {$e->getMessage()}", ['source' => 'NewsApi', 'article' => $article]);

            return null;
        }
    }
}
