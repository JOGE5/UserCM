<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programa el motor de retención heurístico para ejecutarse diariamente
\Illuminate\Support\Facades\Schedule::command('campusmarket:retention-emails')->dailyAt('09:00');
