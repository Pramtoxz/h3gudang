<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('picking:sync-do')
    ->everyTwoMinutes()
    ->withoutOverlapping(10)
    ->runInBackground();

Schedule::command('picking:sync-storing')
    ->everyThreeHours()
    ->withoutOverlapping(30)
    ->runInBackground();



