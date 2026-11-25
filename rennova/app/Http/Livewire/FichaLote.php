<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Services\AsignacionLoteService;
use App\Services\LoteService;
use Livewire\Component;

/**
 * Ficha del lote: un solo lugar para ver en que paso esta el ciclo
 * (datos -> tareas -> recursos) y cual es la proxima accion.
 */
class FichaLote extends Component
{
    use MensajesErrorUsuario;

    public int $loteId = 0;

    protected LoteService $servicio;

    public function boot(LoteService $servicio): void
    {
        $this->servicio = $servicio;
    }

    public function mount(int $loteId): void
    {
        $this->loteId = $loteId;
    }

    public function finalizarLote()
    {
        $this->authorize('editar-lotes');

        try {
            $finalizado = app(AsignacionLoteService::class)->finalizar($this->loteId, $this->datosSolicitud());

            if ($finalizado) {
                session()->flash('message', 'Lote finalizado. Los recursos quedaron liberados.');

                return $this->redirect(route('lotes.index'));
            }

            session()->flash('error', 'El lote ya está finalizado.');
        } catch (\Throwable $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'finalizar el lote'));
        }
    }

    public function render()
    {
        $lote = $this->servicio->ficha($this->loteId);

        $tareas = $lote->tareas;
        $empleados = $lote->empleados;
        $maquinarias = $lote->maquinarias;

        $tieneTareas = $tareas->isNotEmpty();
        $tieneRecursos = $empleados->isNotEmpty() || $maquinarias->isNotEmpty();
        $superficie = number_format((float) ($lote->superficie ?? 0), 2);

        $siguiente = match (true) {
            in_array($lote->estado, ['cerrado', 'baja'], true) => [
                'titulo' => 'Lote cerrado',
                'texto' => 'Este lote no admite cambios. Podés consultar lo que quedó registrado.',
                'accion' => null,
            ],
            $lote->estado === 'inactivo' => [
                'titulo' => 'Lote pausado',
                'texto' => 'Activalo desde el listado de lotes para volver a asignar recursos.',
                'accion' => null,
            ],
            $tieneRecursos => [
                'titulo' => 'El lote está en marcha',
                'texto' => 'Trabajando con '.$empleados->count().' '.
                    ($empleados->count() === 1 ? 'persona' : 'personas').' y '.$maquinarias->count().' '.
                    ($maquinarias->count() === 1 ? 'máquina' : 'máquinas').'.',
                'accion' => 'finalizar',
            ],
            ! $tieneTareas => [
                'titulo' => 'Paso 2: planificá las tareas',
                'texto' => 'Con las tareas calculamos cuánta gente y cuántas máquinas necesitás para las '.$superficie.' ha.',
                'accion' => 'tareas',
            ],
            default => [
                'titulo' => 'Paso 3: asigná los recursos',
                'texto' => 'Ya hay '.$superficie.' ha planificadas. Asigná quién trabaja en el lote.',
                'accion' => 'recursos',
            ],
        };

        return view('livewire.ficha-lote', [
            'lote' => $lote,
            'tareas' => $tareas,
            'empleados' => $empleados,
            'maquinarias' => $maquinarias,
            'siguiente' => $siguiente,
            'pasos' => [
                ['numero' => 1, 'titulo' => 'Datos del lote', 'completo' => true],
                ['numero' => 2, 'titulo' => 'Tareas', 'completo' => $tieneTareas],
                ['numero' => 3, 'titulo' => 'Recursos', 'completo' => $tieneRecursos],
            ],
        ]);
    }

    /**
     * @return array{user_id: ?int, ip_address: ?string, user_agent: ?string, url: ?string}
     */
    private function datosSolicitud(): array
    {
        return [
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ];
    }
}
