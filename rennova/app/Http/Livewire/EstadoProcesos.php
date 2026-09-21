<?php

namespace App\Http\Livewire;

use App\Services\EstadoProcesosService;
use Livewire\Component;

class EstadoProcesos extends Component
{
    /** @var array<int, array{nombre: string, descripcion: string, disparador: string, frecuencia: string, comando: string}> */
    public array $procesos = [];

    /** @var array<int, array{lote: string, con_coordenadas: bool, estado_operativo: ?string, fuente: ?string, api_error: ?string, actualizado: ?string}> */
    public array $climaPorLote = [];

    /** @var array{pendientes: int, fallidos: array<int, array{id: int, nombre: ?string, cola: string, error: ?string, fallido_en: ?string}>} */
    public array $cola = [];

    public function mount(): void
    {
        $servicio = app(EstadoProcesosService::class);

        $this->procesos = $servicio->procesos();
        $this->climaPorLote = $servicio->climaPorLote();
        $this->cola = $servicio->colaTrabajos();
    }

    public function render()
    {
        return view('livewire.estado-procesos');
    }
}
