<?php

namespace App\Http\Livewire\Traits;

use Illuminate\Support\Facades\Log;

trait MensajesErrorUsuario
{
    private function mensajeErrorUsuario(\Throwable $e, string $contexto): string
    {
        Log::error("Error en {$contexto}: ".$e->getMessage());

        return "Ocurrió un error al {$contexto}. Intente nuevamente o contacte al administrador.";
    }
}
