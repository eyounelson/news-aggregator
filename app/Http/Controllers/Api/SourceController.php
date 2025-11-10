<?php

namespace App\Http\Controllers\Api;

use App\Types\NewsSource;

class SourceController
{
    public function index()
    {
        $sources = array_map(
            fn ($source) => $source->name,
            NewsSource::cases()
        );

        return response()->json(['data' => $sources]);
    }
}
