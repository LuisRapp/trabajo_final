<?php

namespace App\Listeners;

use App\Events\CargaRegistrada;
use App\Models\Maquinaria;
use Illuminate\Support\Facades\Log;

class ActualizarOdometroMaquina
{
    /**
     * Handle the event.
     */
    public function handle(CargaRegistrada $event): void
    {
        try {
            $maquinaria = Maquinaria::findOrFail($event->maquinariaId);
            
            // Incrementar las toneladas acumuladas (odómetro)
            $maquinaria->increment('toneladas_acumuladas', $event->toneladas);
            
            Log::info("Odómetro actualizado", [
                'maquinaria_id' => $event->maquinariaId,
                'toneladas_agregadas' => $event->toneladas,
                'toneladas_totales' => $maquinaria->fresh()->toneladas_acumuladas
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error actualizando odómetro de maquinaria", [
                'maquinaria_id' => $event->maquinariaId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
