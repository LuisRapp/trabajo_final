<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Optimiza el sistema de auditoría dispatching en background
 * en lugar de guardar síncronamente
 */
class OptimizeAuditing
{
    public function handle(Request $request, Closure $next)
    {
        // Configurar auditoría para usar queue (asincrónico)
        // en lugar de guardar directamente
        config(['audit.queue.enabled' => true]);
        config(['audit.queue.connection' => 'database']);

        return $next($request);
    }
}
