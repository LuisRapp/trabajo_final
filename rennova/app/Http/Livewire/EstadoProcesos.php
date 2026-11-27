<?php

namespace App\Http\Livewire;

use App\Services\EstadoProcesosService;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class EstadoProcesos extends Component
{
    /** @var array<int, array{nombre: string, descripcion: string, comando: string, cron: string, proximo: ?string, editable: bool}> */
    public array $procesos = [];

    /** @var array<int, array{lote: string, con_coordenadas: bool, estado_operativo: ?string, fuente: ?string, api_error: ?string, actualizado: ?string}> */
    public array $climaPorLote = [];

    /** @var array{pendientes: int, fallidos: array<int, array{id: int, uuid: string, nombre: ?string, cola: string, error: ?string, fallido_en: ?string}>} */
    public array $cola = [];

    public function mount(): void
    {
        $this->refrescar();
    }

    /**
     * Recarga los datos de monitoreo (invocado por el poll de la vista).
     */
    public function refrescar(): void
    {
        $servicio = app(EstadoProcesosService::class);

        $this->procesos = $servicio->procesos();
        $this->climaPorLote = $servicio->climaPorLote();
        $this->cola = $servicio->colaTrabajos();
    }

    public function reintentarJob(string $uuid): void
    {
        $this->authorize('gestionar-usuarios');

        try {
            Artisan::call('queue:retry', ['id' => [$uuid]]);
            session()->flash('message', 'Trabajo reencolado para reintentar.');
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo reintentar el trabajo: '.$e->getMessage());
        }

        $this->refrescar();
    }

    public function descartarJob(string $uuid): void
    {
        $this->authorize('gestionar-usuarios');

        try {
            Artisan::call('queue:forget', ['id' => $uuid]);
            session()->flash('message', 'Trabajo fallido descartado.');
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo descartar el trabajo: '.$e->getMessage());
        }

        $this->refrescar();
    }

    public function render()
    {
        return view('livewire.estado-procesos');
    }
}
