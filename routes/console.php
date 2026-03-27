<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Keep local project projection in sync with ChatProjects
Schedule::command('ccdf:sync-projects --all')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Natively execute any throttled OpenClaw codebase generation jobs (or other failed items)
Schedule::command('queue:work --stop-when-empty')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();
