<?php

namespace App\Services\News\Actions;

use App\Models\Category;
use Illuminate\Support\Collection;

class SaveCategories
{
    public function execute(Collection $articles): void
    {
        $categories = $articles
            ->pluck('categories')
            ->flatten()
            ->filter()
            ->map(fn ($category) => [
                'name' => $category,
            ]);

        if ($categories->isNotEmpty()) {
            Category::upsert($categories->toArray(), ['name']);
        }
    }
}
