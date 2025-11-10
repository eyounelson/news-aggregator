<?php

namespace App\Services\News\Actions;

use App\Models\Article;
use App\Services\News\Data\ArticleData;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SaveArticles
{
    public function execute(Collection $articles): int
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
