<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('steam:sync-achievements')
    ->dailyAt('03:00');

Schedule::command('games:generate-slugs')
    ->daily();

Schedule::command('igdb:import-games')
    ->weekly();

Schedule::command('igdb:import-covers')
    ->weekly();

Schedule::command('steam:libraries-sync')->daily();