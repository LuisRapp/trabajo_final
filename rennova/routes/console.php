<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar verificación de umbrales de mantenimiento diariamente a las 2:00 AM
Schedule::command('mantenimiento:check-umbrales')->dailyAt('02:00');
