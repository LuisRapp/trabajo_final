<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\CargaRegistrada;
use App\Listeners\ActualizarOdometroMaquina;

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
        // Registrar listener para actualización de odómetro
        Event::listen(
            CargaRegistrada::class,
            ActualizarOdometroMaquina::class
        );
    }
}
