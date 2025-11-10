<?php

namespace App\Console\Commands;

use App\Services\News\Factory\NewsAggregatorFactory;
use App\Services\News\SaveArticlesAction;
use App\Types\NewsSource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NewsUpdateCommand extends Command
{
    protected $signature = 'news:update';

    protected $description = 'Update the latest news from the configured sources.';

    public function handle(): int
    {
        $this->line('Updating News Items...');

        foreach (NewsSource::cases() as $source) {
            $this->line('Updating from ' . $source->name);

            $updateCount = DB::transaction( fn() => app(SaveArticlesAction::class)->execute($source));

            $this->info("DB has been updated with $updateCount new articles from $source->name.");
        }

        $this->info('Completed.');

        return static::SUCCESS;
    }
}
