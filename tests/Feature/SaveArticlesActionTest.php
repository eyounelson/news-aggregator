<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Services\News\Actions\SaveArticlesAction;
use App\Types\NewsSource;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Common\FakesNewsApiResponses;
use Tests\TestCase;

class SaveArticlesActionTest extends TestCase
{
    use LazilyRefreshDatabase;
    use FakesNewsApiResponses;

    public function test_it_saves_articles_from_all_sources(): void
    {
        $this->saveArticlesForAllSources();

        foreach (NewsSource::cases() as $source) {
            $this->assertDatabaseHas(Article::class, [
                'source' => $source,
            ]);

            $this->assertDatabaseMissing(Category::class, [
                'source' => $source,
            ]);
        }
    }

    public function test_it_saves_articles_with_correct_data(): void
    {
        app(SaveArticlesAction::class)->execute(NewsSource::NYTimes);

        $article = Article::where('source', NewsSource::NYTimes)->first();

        $this->assertNotNull($article);
        $this->assertNotNull($article->title);
        $this->assertNotNull($article->content);
        $this->assertNotNull($article->url);
        $this->assertNotNull($article->published_at);
    }

    public function test_it_saves_authors_with_source(): void
    {
        app(SaveArticlesAction::class)->execute(NewsSource::NYTimes);

        $this->assertDatabaseHas(Author::class, [
            'source' => NewsSource::NYTimes,
        ]);
    }

    public function test_it_saves_categories(): void
    {
        app(SaveArticlesAction::class)->execute(NewsSource::NYTimes);

        $this->assertDatabaseHas(Category::class, ['name' => 'Arts']);
    }

    public function test_it_syncs_article_author_relationships(): void
    {
        app(SaveArticlesAction::class)->execute(NewsSource::NYTimes);

        $article = Article::where('source', NewsSource::NYTimes)->first();

        $this->assertGreaterThan(0, $article->authors()->count());

        $author = $article->authors()->first();
        $this->assertEquals(NewsSource::NYTimes, $author->source);
    }

    public function test_it_syncs_article_category_relationships(): void
    {
        app(SaveArticlesAction::class)->execute(NewsSource::NYTimes);

        $article = Article::first();

        $this->assertGreaterThan(0, $article->categories()->count());
    }

    public function test_it_handles_duplicate_articles_with_upsert(): void
    {
        app(SaveArticlesAction::class)->execute(NewsSource::NYTimes);
        $firstCount = Article::count();

        app(SaveArticlesAction::class)->execute(NewsSource::NYTimes);
        $secondCount = Article::count();

        // Count should be the same (no duplicates created)
        $this->assertEquals($firstCount, $secondCount);
    }

    public function test_it_updates_existing_articles_on_duplicate(): void
    {
        app(SaveArticlesAction::class)->execute(NewsSource::NYTimes);

        $article = Article::where('source', NewsSource::NYTimes)->first();
        $originalContent = $article->content;

        // Manually update the article
        $article->update(['content' => 'Modified content']);

        // Save again - should update back to original
        app(SaveArticlesAction::class)->execute(NewsSource::NYTimes);

        $article->refresh();
        $this->assertEquals($originalContent, $article->content);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeNewsApiResponses();
    }
}
