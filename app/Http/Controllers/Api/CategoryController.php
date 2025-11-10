<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController
{
    public function index()
    {
        $categories = Category::filter()->paginate();

        return CategoryResource::collection($categories);
    }
}
