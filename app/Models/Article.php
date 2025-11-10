<?php

namespace App\Models;

use App\Types\NewsSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'source' => NewsSource::class,
            'published_at' => 'datetime',
        ];
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}
