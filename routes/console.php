<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('settlement:daily')
    ->dailyAt((string) config('settlement.run_at', '00:05'))
    ->timezone((string) config('settlement.timezone', 'Asia/Shanghai'))
    ->when(fn (): bool => (bool) config('settlement.enabled', true))
    ->withoutOverlapping(10)
    ->runInBackground();
