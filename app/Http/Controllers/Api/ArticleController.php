<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ArticleController
{
    public function index(): AnonymousResourceCollection
    {
        $articles = QueryBuilder::for(Article::class)
            ->with(['authors', 'categories'])
            ->allowedFilters([
                AllowedFilter::exact('source'),
                AllowedFilter::exact('categories', 'categories.name'),
                AllowedFilter::exact('authors', 'authors.name'),
                AllowedFilter::scope('date_from'),
                AllowedFilter::scope('date_to'),
                AllowedFilter::scope('search'),
            ])
            ->defaultSort('-published_at')
            ->paginate(15);

        return ArticleResource::collection($articles);
    }
}
