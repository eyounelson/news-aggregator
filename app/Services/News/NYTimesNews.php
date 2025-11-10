<?php

namespace App\Services\News;

use App\Services\News\Data\ArticleData;
use App\Services\News\Factory\NewsAggregatorContract;
use App\Types\NewsSource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;
use TypeError;

class NYTimesNews implements NewsAggregatorContract
{

    public function articles(): Collection
    {
        /**
         * Fetch articles using the Article Search API
         * See https://developer.nytimes.com/docs/articlesearch-product/1/overview
         */

        $baseUrl = 'https://api.nytimes.com';
        $apiKey = config('services.ny_times.api_key');

        $query = [
            'api-key' => $apiKey,
            'fq' => $this->formatFilterQuery(),
        ];

        $articles = Http::baseUrl($baseUrl)
            ->get("/svc/search/v2/articlesearch.json", $query)
            ->collect('response.docs');

       return collect($articles)->map(fn($article) => $this->formatArticle($article))->filter();
    }

    public function formatArticle($article):?ArticleData
    {
        try {
           return new ArticleData(
               source: NewsSource::NYTimes,
               title: $article['headline']['main'],
               excerpt: $article['snippet'],
               content: $article['abstract'],
               url: $article['web_url'],
               imageUrl: $article['multimedia']['default']['url'],
               date: $article['pub_date'],
               authors: str($article['byline']['original'])->after('By ')->explode(' and ')->toArray(),
               categories: collect($article['keywords'])
                   ->where('name', 'Subject')
                    ->pluck('value')
                    ->prepend($article['section_name'])
                    ->toArray(),
           );
        } catch (Throwable $e) {
            Log::error("Article Parsing failed with error: {$e->getMessage()}", ['source' => "NyTimes", 'article' => $article]);

            return null;
        }
    }

    private function formatFilterQuery(): string
    {
        /**
         * Wrap keyword in single quotes and format as specified by the docs.
         */
        $keywords = array_map(fn($word) => "'$word'", config('app.news.keywords'));

        return 'Article.body:('. implode(', ', $keywords).')';
    }
}
