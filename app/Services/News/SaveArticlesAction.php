<?php

namespace App\Services\News;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Services\News\Data\ArticleData;
use App\Services\News\Factory\NewsAggregatorFactory;
use App\Types\NewsSource;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SaveArticlesAction
{
    public function execute(NewsSource $source): int
    {
        $articles = NewsAggregatorFactory::make($source)->articles();

        $updateCount = $this->saveArticles($articles);
        $this->saveAuthors($articles);
        $this->saveCategories($articles);
        $this->syncRelationships($articles);

        return $updateCount;
    }

    private function saveArticles(Collection $articles): int
    {
        $data = $articles->map(fn(ArticleData $article) => [
            'source' => $article->source,
            'title' => $article->title,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'url' => $article->url,
            'image_url' => $article->imageUrl,
            'published_at' => $this->parseDate($article->date),
        ])->toArray();

        return Article::upsert(
            $data,
            ['source', 'title', 'published_at'],
            ['excerpt', 'content', 'url', 'image_url']
        );
    }

    private function saveAuthors(Collection $articles): void
    {
        $authors = $articles->map(fn(ArticleData $article) => $article->authors)->flatten()->filter();

        $data = $authors->map(fn($author) => [
            'source' => $articles[0]->source,
            'name' => $author
        ]);

        if ($data->count() > 0) {
            Author::upsert($data->toArray(), ['name', 'source']);
        }
    }

    private function saveCategories(Collection $articles): void
    {
        $categories = $articles->map(fn(ArticleData $article) => $article->categories)->flatten()->filter();

        $data = $categories->map(fn($category) => [
            'name' => $category
        ]);

        if ($data->count() > 0) {
            Category::upsert($data->toArray(), ['name']);
        }
    }

    private function syncRelationships(Collection $articles): void
    {
        $articles->each(function (ArticleData $article) {
            $dbArticle = Article::where([
                'source' => $article->source->name,
                'title' => $article->title,
                'published_at' => $this->parseDate($article->date),
            ])->first();

            if ($dbArticle) {
                $this->syncAuthors($dbArticle, $article);
                $this->syncCategories($dbArticle, $article);
            }
        });
    }

    private function syncAuthors(Article $article, ArticleData $articleData): void
    {
        $authorIds = Author::where('source', $articleData->source)
            ->whereIn('name', $articleData->authors)
            ->pluck('id');

        $article->authors()->sync($authorIds);
    }

    private function syncCategories(Article $article, ArticleData $articleData): void
    {
        $categoryIds = Category::whereIn('name', $articleData->categories)
            ->pluck('id');

        $article->categories()->sync($categoryIds);
    }

    private function parseDate(?string $date): ?Carbon
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (Exception) {
            return null;
        }
    }
}
