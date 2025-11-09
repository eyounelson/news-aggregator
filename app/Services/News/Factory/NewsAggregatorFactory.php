<?php

namespace App\Services\News\Factory;

use App\Services\News\NewsApiNews;
use App\Services\News\NYTimesNews;
use App\Services\News\TheGuardianNews;
use App\Types\NewsSource;
use InvalidArgumentException;

class NewsAggregatorFactory
{
    public static function make(NewsSource $source): NewsAggregatorContract
    {
        return match ($source) {
            NewsSource::NYTimes => app(NYTimesNews::class),
            NewsSource::TheGuardian => app(TheGuardianNews::class),
            NewsSource::NewsApi => app(NewsApiNews::class),
            default => throw new InvalidArgumentException("Unsupported source type: $source->name."),
        };
    }
}
