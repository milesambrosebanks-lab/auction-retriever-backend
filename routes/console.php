<?php
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


// প্রতিদিন রাত ২টায় auto scrape
// Schedule::command('scrape:bid4assets')->dailyAt('02:00');

