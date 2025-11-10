<?php

namespace App\Services\News\Data;

use App\Types\NewsSource;

readonly class ArticleData
{
    public function __construct(
        public NewsSource $source,
        public string $title,
        public ?string $excerpt,
        public string $content,
        public string $url,
        public ?string $imageUrl,
        public string $date,
        public array $authors,
        public array $categories,
    ) {
        //
    }
}
