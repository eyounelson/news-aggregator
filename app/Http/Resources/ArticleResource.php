<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'source' => $this->source->name,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'url' => $this->url,
            'image_url' => $this->image_url,
            'published_at' => $this->published_at,
            'authors' => $this->whenLoaded('authors', fn() => $this->authors->map(fn($author) => [
                    'id' => $author->id,
                    'name' => $author->name,
                    'source' => $author->source->name,
                ])
            ),
            'categories' => $this->whenLoaded('categories', fn() => $this->categories->map(fn($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ])
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
