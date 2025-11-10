<?php

namespace App\Services\News\Factory;

use App\Services\News\Data\ArticleData;
use Illuminate\Support\Collection;

interface NewsAggregatorContract
{
    /**
     * @return Collection<ArticleData>
     */
    public function articles(): Collection;

    public function formatArticle(array $article): ?ArticleData;
}
