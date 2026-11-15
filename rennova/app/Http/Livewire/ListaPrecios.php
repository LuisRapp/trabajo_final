<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Services\ListaPrecioService;
use Livewire\Component;
use Livewire\WithPagination;

class ListaPrecios extends Component
{
    use MensajesErrorUsuario;
    use WithPagination;

    public $clientes;

    public $categorias;

    public $precio_id;

    public $cliente_id;

    public $categoria_id;

    public $precio;

    public $fecha_desde;

    public $fecha_hasta;

    public $busqueda = '';

    public $tab_activo = 'listado';

    public $mostrar_historico = false;

    protected ListaPrecioService $servicio;

    public function boot(): void
    {
        $this->servicio = app(ListaPrecioService::class);
    }

    protected function rules()
    {
        return $this->servicio->reglas();
    }

    protected $messages = [
        'fecha_hasta.after_or_equal' => 'La fecha hasta debe ser posterior o igual a la fecha desde.',
    ];

    public function mount()
    {
        $this->clientes = $this->servicio->listarClientes();
        $this->categorias = $this->servicio->listarCategorias();
        $this->fecha_desde = now()->toDateString();
    }

    public function render()
    {
        return view('livewire.lista-precios', [
            'precios' => $this->servicio->listar($this->busqueda, $this->mostrar_historico),
        ]);
    }

    public function updatedMostrarHistorico()
    {
        $this->resetPage();
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        try {
            $resultado = $this->servicio->crearOActualizar($this->precio_id, [
                'cliente_id' => $this->cliente_id,
                'categoria_id' => $this->categoria_id,
                'precio' => $this->precio,
                'fecha_desde' => $this->fecha_desde,
                'fecha_hasta' => $this->fecha_hasta,
            ]);

            if (! $resultado['success']) {
                session()->flash('error', $resultado['message']);

                return;
            }

            session()->flash('message', $this->precio_id ? 'Precio actualizado correctamente.' : 'Precio creado correctamente.');
            $this->resetCampos();
            $this->dispatch('precioGuardado');

        } catch (\Exception $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'guardar el precio'));
        }
    }

    public function editar($id)
    {
        $precioItem = $this->servicio->obtener($id);
        $this->precio_id = $precioItem->id;
        $this->cliente_id = $precioItem->cliente_id;
        $this->categoria_id = $precioItem->categoria_id;
        $this->precio = $precioItem->precio;
        $this->fecha_desde = $precioItem->fecha_desde ? $precioItem->fecha_desde->format('Y-m-d') : null;
        $this->fecha_hasta = $precioItem->fecha_hasta ? $precioItem->fecha_hasta->format('Y-m-d') : null;
    }

    public function eliminar($id)
    {
        $this->servicio->eliminar($id);
        session()->flash('message', 'Precio eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset([
            'precio_id', 'cliente_id', 'categoria_id', 'precio', 'fecha_hasta',
        ]);
        $this->fecha_desde = now()->toDateString();
    }
}
