<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ========== TAREAS PROGRAMADAS (SCHEDULER) ==========

// Verificación de umbrales de mantenimiento - Diariamente a las 2:00 AM
Schedule::command('mantenimiento:check-umbrales')
    ->dailyAt('02:00')
    ->withoutOverlapping(10)
    ->onFailure(function () {
        \Log::error('Tarea de mantenimiento fallida: mantenimiento:check-umbrales');
    })
    ->onSuccess(function () {
        \Log::info('Tarea de mantenimiento completada: mantenimiento:check-umbrales');
    });

// Análisis de decisiones climáticas - Cada 6 horas
Schedule::command('clima:decisiones')
    ->everySixHours()
    ->withoutOverlapping(5)
    ->onFailure(function () {
        \Log::error('Tarea de clima fallida: clima:decisiones');
    })
    ->onSuccess(function () {
        \Log::info('Tarea de clima completada: clima:decisiones');
    });

// Análisis de riesgo climático - Diariamente a las 6:00 AM
Schedule::command('clima:analizar --dias=7')
    ->dailyAt('06:00')
    ->withoutOverlapping(10)
    ->onFailure(function () {
        \Log::error('Tarea de riesgo climático fallida: clima:analizar');
    })
    ->onSuccess(function () {
        \Log::info('Tarea de riesgo climático completada: clima:analizar');
    });

// Verificación de mantenimientos programados - Cada 4 horas
Schedule::command('mantenimiento:check-programados')
    ->everyFourHours()
    ->withoutOverlapping(5);

