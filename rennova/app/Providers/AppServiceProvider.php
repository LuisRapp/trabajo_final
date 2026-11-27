<?php

namespace App\Providers;

use App\Events\CargaRegistrada;
use App\Listeners\ActualizarOdometroMaquina;
use App\Models\Lote;
use App\Observers\LoteObserver;
use App\Services\EjecucionAutomatizacionService;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar Carbon en español (días y meses en la UI)
        Carbon::setLocale('es');
        Carbon::setFallbackLocale('es');

        // Registrar observadores
        Lote::observe(LoteObserver::class);

        // Registrar listener para actualización de odómetro
        Event::listen(
            CargaRegistrada::class,
            ActualizarOdometroMaquina::class
        );

        // Estado de la última ejecución de cada automatización programada.
        Event::listen(ScheduledTaskFinished::class, function (ScheduledTaskFinished $event) {
            $servicio = app(EjecucionAutomatizacionService::class);
            $servicio->registrar(
                $servicio->comandoDe($event->task),
                'ok',
                null,
                (int) round($event->runtime * 1000)
            );
        });

        Event::listen(ScheduledTaskFailed::class, function (ScheduledTaskFailed $event) {
            $servicio = app(EjecucionAutomatizacionService::class);
            $servicio->registrar(
                $servicio->comandoDe($event->task),
                'error',
                $servicio->motivoDe($event->exception)
            );
        });

        Event::listen(ScheduledTaskSkipped::class, function (ScheduledTaskSkipped $event) {
            $servicio = app(EjecucionAutomatizacionService::class);
            $servicio->registrar(
                $servicio->comandoDe($event->task),
                'skipped',
                'Omitida: otra ejecución en curso.'
            );
        });

        // Configurar rate limiters
        RateLimiter::for('login', function ($request) {
            return Limit::perMinute(5)->by($request->email.$request->ip());
        });

        RateLimiter::for('two-factor', function ($request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
