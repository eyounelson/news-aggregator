<?php

namespace App\Services\News\Factory;

use App\Services\News\Data\Article;

interface NewsAggregatorContract
{
    /**
     * @return array<Article>
     */
    public function articles(): array;
}
