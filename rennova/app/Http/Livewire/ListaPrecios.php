<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\CategoriaClientePrecio;
use App\Models\Cliente;
use App\Models\CategoriaMadera;

class ListaPrecios extends Component
{
    public $precios, $clientes, $categorias;
    public $precio_id, $cliente_id, $categoria_id, $precio, $fecha_desde, $fecha_hasta;
    public $busqueda = '';
    public $mostrar_historico = false; // Toggle para mostrar/ocultar historial

    protected function rules()
    {
        return [
            'cliente_id' => 'required|exists:clientes,id_cliente',
            'categoria_id' => 'required|exists:categoria_maderas,id_categoria_madera',
            'precio' => 'required|numeric|min:0',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ];
    }

    protected $messages = [
        'fecha_hasta.after_or_equal' => 'La fecha hasta debe ser posterior o igual a la fecha desde.',
    ];

    public function mount()
    {
        $this->clientes = Cliente::orderBy('razon_social')->get();
        $this->categorias = CategoriaMadera::orderBy('nombre')->get();
        $this->fecha_desde = now()->toDateString(); // Fecha por defecto: hoy
        $this->cargarPrecios();
    }

    public function render()
    {
        $this->cargarPrecios();
        return view('livewire.lista-precios');
    }

    public function cargarPrecios()
    {
        $query = CategoriaClientePrecio::with(['cliente', 'categoria']);

        // Filtrar por historial o solo actuales
        if (!$this->mostrar_historico) {
            $query->actuales(); // Solo registros con fecha_hasta = NULL
        }

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function($q) use ($busq) {
                $q->whereHas('cliente', function($qc) use ($busq) {
                    $qc->where('razon_social', 'ILIKE', '%' . $busq . '%');
                })
                ->orWhereHas('categoria', function($qcat) use ($busq) {
                    $qcat->where('nombre', 'ILIKE', '%' . $busq . '%');
                })
                ->orWhereRaw('CAST(precio AS TEXT) LIKE ?', ['%' . $busq . '%']);
            });
        }

        // Ordenar: precios vigentes primero (fecha_hasta NULL), luego por fecha_desde descendente
        $this->precios = $query->orderByRaw('fecha_hasta IS NULL DESC')
                              ->orderBy('fecha_desde', 'desc')
                              ->get();
    }

    public function updatedMostrarHistorico()
    {
        $this->cargarPrecios();
    }

    public function updatedBusqueda()
    {
        $this->cargarPrecios();
    }

    public function guardar()
    {
        $this->validate();

        try {
            // Verificar solapamiento de fechas para la misma combinación cliente-categoría
            $solapamiento = CategoriaClientePrecio::where('cliente_id', $this->cliente_id)
                ->where('categoria_id', $this->categoria_id)
                ->when($this->precio_id, function($q) {
                    // Excluir el registro actual si estamos editando
                    return $q->where('id', '!=', $this->precio_id);
                })
                ->where(function($query) {
                    // Verificar solapamiento de fechas
                    $query->where(function($q) {
                        // Caso 1: El nuevo precio empieza durante un período existente
                        $q->where('fecha_desde', '<=', $this->fecha_desde)
                          ->where(function($subq) {
                              $subq->whereNull('fecha_hasta')
                                   ->orWhere('fecha_hasta', '>=', $this->fecha_desde);
                          });
                    })
                    ->orWhere(function($q) {
                        // Caso 2: El nuevo precio termina durante un período existente
                        if ($this->fecha_hasta) {
                            $q->where('fecha_desde', '<=', $this->fecha_hasta)
                              ->where(function($subq) {
                                  $subq->whereNull('fecha_hasta')
                                       ->orWhere('fecha_hasta', '>=', $this->fecha_hasta);
                              });
                        }
                    })
                    ->orWhere(function($q) {
                        // Caso 3: El nuevo precio abarca completamente un período existente
                        if ($this->fecha_hasta) {
                            $q->where('fecha_desde', '>=', $this->fecha_desde)
                              ->where('fecha_desde', '<=', $this->fecha_hasta);
                        }
                    });
                })
                ->exists();

            if ($solapamiento) {
                session()->flash('error', 'Ya existe un precio vigente para este cliente y categoría en el período seleccionado. Las fechas no pueden solaparse.');
                return;
            }

            // Si estamos creando un nuevo precio actual (fecha_hasta = null)
            // debemos cerrar el precio anterior
            if (!$this->precio_id && !$this->fecha_hasta) {
                $precioAnterior = CategoriaClientePrecio::where('cliente_id', $this->cliente_id)
                    ->where('categoria_id', $this->categoria_id)
                    ->whereNull('fecha_hasta')
                    ->first();

                if ($precioAnterior) {
                    // Cerrar el precio anterior con la fecha del día anterior al nuevo precio
                    $fechaCierre = \Carbon\Carbon::parse($this->fecha_desde)->subDay()->toDateString();
                    $precioAnterior->update(['fecha_hasta' => $fechaCierre]);
                }
            }

            CategoriaClientePrecio::updateOrCreate(
                ['id' => $this->precio_id],
                [
                    'cliente_id' => $this->cliente_id,
                    'categoria_id' => $this->categoria_id,
                    'precio' => $this->precio,
                    'fecha_desde' => $this->fecha_desde,
                    'fecha_hasta' => $this->fecha_hasta,
                ]
            );

            $this->cargarPrecios();
            session()->flash('message', $this->precio_id ? 'Precio actualizado correctamente.' : 'Precio creado correctamente.');
            $this->resetCampos();
            $this->dispatch('precioGuardado');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar el precio: ' . $e->getMessage());
        }
    }

    public function editar($id)
    {
        $precioItem = CategoriaClientePrecio::findOrFail($id);
        $this->precio_id = $precioItem->id;
        $this->cliente_id = $precioItem->cliente_id;
        $this->categoria_id = $precioItem->categoria_id;
        $this->precio = $precioItem->precio;
        $this->fecha_desde = $precioItem->fecha_desde ? $precioItem->fecha_desde->format('Y-m-d') : null;
        $this->fecha_hasta = $precioItem->fecha_hasta ? $precioItem->fecha_hasta->format('Y-m-d') : null;
    }

    public function eliminar($id)
    {
        CategoriaClientePrecio::findOrFail($id)->delete();
        $this->cargarPrecios();
        session()->flash('message', 'Precio eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset([
            'precio_id', 'cliente_id', 'categoria_id', 'precio', 'fecha_hasta'
        ]);
        $this->fecha_desde = now()->toDateString(); // Reset a fecha actual
    }
}
