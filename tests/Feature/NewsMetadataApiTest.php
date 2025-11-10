<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Author;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Common\FakesNewsApiResponses;
use Tests\TestCase;

class NewsMetadataApiTest extends TestCase
{
    use LazilyRefreshDatabase;
    use FakesNewsApiResponses;

    public function test_it_lists_categories(): void
    {
        $response = $this->getJson('/api/categories');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'created_at',
                        'updated_at',
                    ]
                ],
            ]);

        $this->assertGreaterThan(0, count($response->json('data')));
    }

    public function test_it_lists_authors(): void
    {
        $response = $this->getJson('/api/authors');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'source',
                        'created_at',
                        'updated_at',
                    ]
                ],
            ]);

        $this->assertGreaterThan(0, count($response->json('data')));
    }

    public function test_it_lists_sources(): void
    {
        $response = $this->getJson('/api/sources');

        $response->assertOk()
            ->assertJson([
                'NYTimes',
                'TheGuardian',
                'NewsApi',
            ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeNewsApiResponses();
        $this->saveArticlesForAllSources();
    }
}
