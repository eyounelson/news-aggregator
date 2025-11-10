<?php

namespace App\Services\News\Actions;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Services\News\Data\ArticleData;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SyncRelationships
{
    public function execute(Collection $articles): void
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
