<?php

namespace App\Models;

use App\Types\NewsSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class Article extends Model
{
    protected $guarded = [];

    public static function filter()
    {
        return QueryBuilder::for(static::class)
            ->with(['authors', 'categories'])
            ->allowedFilters([
                AllowedFilter::exact('sources', 'source'),
                AllowedFilter::exact('categories', 'categories.name'),
                AllowedFilter::exact('authors', 'authors.name'),
                AllowedFilter::scope('date_from'),
                AllowedFilter::scope('date_to'),
                AllowedFilter::scope('search'),
            ])
            ->defaultSort('-published_at');
    }

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

    public function scopeDateFrom($query, string $date)
    {
        return $query->where('published_at', '>=', $date);
    }

    public function scopeDateTo($query, string $date)
    {
        return $query->where('published_at', '<=', $date);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(fn ($q) => $q->where('title', 'like', "%{$term}%")
            ->orWhere('content', 'like', "%{$term}%")
        );
    }
}
