<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Types\NewsSource;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Common\FakesNewsApiResponses;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    use LazilyRefreshDatabase;
    use FakesNewsApiResponses;

    public function test_it_lists_articles(): void
    {
        $response = $this->getJson('/api/articles');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'source',
                        'title',
                        'excerpt',
                        'content',
                        'url',
                        'image_url',
                        'published_at',
                        'authors' => [
                            '*' => ['id', 'name', 'source']
                        ],
                        'categories' => [
                            '*' => ['id', 'name']
                        ],
                        'created_at',
                        'updated_at',
                    ]
                ],
                'links',
                'meta',
            ]);

        $this->assertGreaterThan(0, count($response->json('data')));
    }

    public function test_it_returns_articles_with_relationships(): void
    {
        $response = $this->getJson('/api/articles');

        $firstArticle = $response->json('data.0');

        $this->assertNotEmpty($firstArticle['authors']);
        $this->assertNotEmpty($firstArticle['categories']);
        $this->assertArrayHasKey('id', $firstArticle['authors'][0]);
        $this->assertArrayHasKey('name', $firstArticle['authors'][0]);
        $this->assertArrayHasKey('source', $firstArticle['authors'][0]);
    }

    public function test_it_filters_by_source(): void
    {
        $response = $this->getJson('/api/articles?filter[source]=NYTimes');

        $response->assertOk();

        $response->collect('data')
            ->every(fn($article) => $this->assertEquals($article['source'], NewsSource::NYTimes->name));
    }

    public function test_it_filters_by_category(): void
    {
        $response = $this->getJson('/api/articles?filter[categories]=Arts');

        $response->assertOk();

        $articles = $response->json('data');
        $this->assertGreaterThan(0, count($articles));

      foreach ($articles as $article) {
          $this->assertNotNull(collect($article['categories'])->firstWhere('name', 'Arts'));
      }
    }

    public function test_it_filters_by_author(): void
    {
        $response = $this->getJson('/api/articles?filter[authors]=Alex Williams');

        $response->assertOk();

        $articles = $response->json('data');
        $this->assertGreaterThan(0, count($articles));

        foreach ($articles as $article) {
            $this->assertNotNull(collect($article['authors'])->firstWhere('name', 'Alex Williams'));
        }
    }

    public function test_it_filters_by_date_from(): void
    {
        $response = $this->getJson('/api/articles?filter[date_from]=2025-11-09');

        $response->assertOk();

        $articles = $response->collect('data');

        $articles->every(
            fn($article) =>  $this->assertTrue(
                Carbon::parse($article['published_at'])->gte('2025-11-09')
            )
        );
    }

    public function test_it_filters_by_date_to(): void
    {
        $response = $this->getJson('/api/articles?filter[date_to]=2025-11-09');

        $response->assertOk();

        $articles = $response->collect('data');

        $articles->every(
            fn($article) =>  $this->assertTrue(
                Carbon::parse($article['published_at'])->lte('2025-11-09 23:59:59')
            )
        );
    }

    public function test_it_searches_articles(): void
    {
        $response = $this->getJson('/api/articles?filter[search]=climate');

        $response->assertOk();

        $articles = $response->collect('data');
        $this->assertGreaterThan(0, count($articles));

        $searchTerm = 'climate';

        $articles->every(
            fn($article) => $this->assertTrue(
                str_contains($article['title'], $searchTerm) || str_contains($article['content'], $searchTerm)
            )
        );
    }

    public function test_it_combines_multiple_filters(): void
    {
        $response = $this->getJson('/api/articles?filter[source]=NYTimes&filter[categories]=Arts');

        $response->assertOk();

        $firstArticle = $response->json('data.0');

        $this->assertEquals('NYTimes', $firstArticle['source']);
        $categoryNames = array_column($firstArticle['categories'], 'name');
        $this->assertContains('Arts', $categoryNames);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeNewsApiResponses();
        $this->saveArticlesForAllSources();
    }
}
