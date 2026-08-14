<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * DO masuk terus sepanjang jam kerja dengan jeda median 1 menit, dan masih ada
 * beberapa di jam lembur, jadi jadwalnya tidak dibatasi jam kerja.
 * withoutOverlapping menggantikan seluruh mesin cache lock milik aplikasi lama.
 */
Schedule::command('picking:sync-do')
    ->everyTwoMinutes()
    ->withoutOverlapping(10)
    ->runInBackground();
