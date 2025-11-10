<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController
{
    public function index(): AnonymousResourceCollection
    {
        $articles = Article::filter()->paginate(15);

        return ArticleResource::collection($articles);
    }
}
