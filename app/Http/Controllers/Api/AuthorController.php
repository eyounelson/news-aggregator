<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AuthorResource;
use App\Models\Author;

class AuthorController
{
    public function index()
    {
        $authors = Author::filter()->paginate(15);

        return AuthorResource::collection($authors);
    }
}
