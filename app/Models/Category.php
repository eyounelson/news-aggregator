<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class Category extends Model
{
    protected $guarded = [];

    public static function filter()
    {
        return QueryBuilder::for(static::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
            ])
            ->defaultSort('name');
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }
}
