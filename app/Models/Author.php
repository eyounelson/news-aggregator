<?php

namespace App\Models;

use App\Types\NewsSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'source' => NewsSource::class,
        ];
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }
}
