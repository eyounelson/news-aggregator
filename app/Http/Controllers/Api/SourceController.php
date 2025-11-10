<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Types\NewsSource;
use Illuminate\Http\JsonResponse;

class SourceController
{
    public function index()
    {
        $sources = array_map(
            fn($source) => $source->name,
            NewsSource::cases()
        );

        return response()->json($sources);
    }
}
