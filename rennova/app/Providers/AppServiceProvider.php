<?php

namespace App\Providers;

use App\Events\CargaRegistrada;
use App\Listeners\ActualizarOdometroMaquina;
use App\Models\Lote;
use App\Observers\LoteObserver;
use Illuminate\Cache\RateLimiting\Limit;
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
        // Registrar observadores
        Lote::observe(LoteObserver::class);

        // Registrar listener para actualización de odómetro
        Event::listen(
            CargaRegistrada::class,
            ActualizarOdometroMaquina::class
        );

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
