<?php

use App\Services\News\Factory\NewsAggregatorFactory;
use App\Types\NewsSource;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('news:update')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->onOneServer();
