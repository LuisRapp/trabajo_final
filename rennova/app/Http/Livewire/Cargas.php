<?php

namespace App\Http\Livewire;

use App\Services\CargaService;
use Livewire\Component;
use Livewire\WithPagination;

class Cargas extends Component
{
    use WithPagination;

    public $lotes = [];

    public $categorias = [];

    public $partes = [];

    public $tab_activo = 'listado';

    public $pagina = 15;

    public $carga_id;

    public $id_lote;

    public $id_categoria_madera;

    public $id_chofer;

    public $id_parte_diario;

    public $ticket;

    public $peso_bruto;

    public $tara;

    public $peso_neto;

    public $id_cliente;

    public $fecha_carga;

    public $busqueda = '';

    protected CargaService $servicio;

    public function boot(): void
    {
        $this->servicio = app(CargaService::class);
    }

    protected function rules(): array
    {
        return $this->servicio->reglas();
    }

    public function mount()
    {
        $this->lotes = $this->servicio->listarLotes();
        $this->categorias = $this->servicio->listarCategorias();
        $this->partes = $this->servicio->listarPartes();
    }

    public function render()
    {
        return view('livewire.cargas', [
            'cargas' => $this->servicio->listar($this->busqueda, $this->pagina),
            'clientes' => $this->servicio->listarClientes(),
            'choferes' => $this->servicio->listarChoferes($this->id_cliente ? (int) $this->id_cliente : null),
            // Solo lotes en proceso; al editar se incluye el de la carga aunque
            // haya salido de proceso, para no perder la seleccion.
            'lotes' => $this->servicio->listarLotes($this->carga_id ? (int) $this->id_lote : null),
        ]);
    }

    public function updatedIdCliente(): void
    {
        // El chofer depende del cliente: al cambiarlo, la seleccion previa deja de ser valida.
        $this->id_chofer = null;
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->authorize($this->carga_id ? 'editar-cargas' : 'crear-cargas');
        $this->validate();

        $this->servicio->crearOActualizar($this->carga_id, [
            'id_lote' => $this->id_lote,
            'id_categoria_madera' => $this->id_categoria_madera,
            'id_chofer' => $this->id_chofer,
            'id_parte_diario' => $this->id_parte_diario,
            'id_cliente' => $this->id_cliente,
            'ticket' => $this->ticket,
            'peso_bruto' => $this->peso_bruto,
            'tara' => $this->tara,
            'peso_neto' => $this->peso_neto,
            'fecha_carga' => $this->fecha_carga,
        ]);

        session()->flash('message', $this->carga_id ? 'Carga actualizada correctamente.' : 'Carga creada correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->resetPage();
        $this->dispatch('cargaGuardada');
    }

    public function editar($id)
    {
        $this->authorize('editar-cargas');
        $this->tab_activo = 'nuevo';
        $carga = $this->servicio->obtener($id);
        $this->carga_id = $carga->id_carga;
        $this->id_lote = $carga->id_lote;
        $this->id_categoria_madera = $carga->id_categoria_madera;
        $this->id_chofer = $carga->id_chofer;
        $this->id_parte_diario = $carga->id_parte_diario;
        $this->ticket = $carga->ticket;
        $this->peso_bruto = $carga->peso_bruto;
        $this->tara = $carga->tara;
        $this->peso_neto = $carga->peso_neto;
        $this->id_cliente = $carga->id_cliente;
        $this->fecha_carga = $carga->fecha_carga;
    }

    public function eliminar($id)
    {
        $this->authorize('eliminar-cargas');
        $this->servicio->eliminar($id);
        session()->flash('message', 'Carga eliminada correctamente.');
        $this->resetCampos();
        $this->resetPage();
    }

    public function resetCampos()
    {
        $this->reset([
            'carga_id', 'id_lote', 'id_categoria_madera', 'id_chofer', 'id_parte_diario', 'ticket',
            'peso_bruto', 'tara', 'peso_neto', 'id_cliente', 'fecha_carga',
        ]);
    }
}
