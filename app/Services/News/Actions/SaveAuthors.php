<?php

namespace App\Services\News\Actions;

use App\Models\Author;
use App\Types\NewsSource;
use Illuminate\Support\Collection;

class SaveAuthors
{
    public function execute(Collection $articles, NewsSource $source): void
    {
        $authors = $articles
            ->pluck('authors')
            ->flatten()
            ->filter()
            ->map(fn ($author) => [
                'source' => $source->name,
                'name' => $author,
            ]);

        if ($authors->isNotEmpty()) {
            Author::upsert($authors->toArray(), ['name', 'source']);
        }
    }
}
