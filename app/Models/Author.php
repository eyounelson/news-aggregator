<?php

namespace App\Models;

use App\Types\NewsSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class Author extends Model
{
    protected $guarded = [];

    public static function filter()
    {
        return QueryBuilder::for(static::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::exact('source'),
            ])
            ->defaultSort('name');
    }

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
