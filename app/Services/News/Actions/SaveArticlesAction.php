<?php

namespace App\Services\News\Actions;

use App\Services\News\Factory\NewsAggregatorFactory;
use App\Types\NewsSource;

class SaveArticlesAction
{
    public function __construct(
        private SaveArticles $saveArticles,
        private SaveAuthors $saveAuthors,
        private SaveCategories $saveCategories,
        private SyncRelationships $syncRelationships
    ) {}

    public function execute(NewsSource $source): int
    {
        $articles = NewsAggregatorFactory::make($source)->articles();

        $updatesCount = $this->saveArticles->execute($articles);

        $this->saveAuthors->execute($articles, $source);
        $this->saveCategories->execute($articles);
        $this->syncRelationships->execute($articles);

        return $updatesCount;
    }
}
