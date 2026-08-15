<?php

namespace App\Http\Livewire;

use App\Models\CategoriaMadera;
use App\Models\Chofer;
use App\Models\Cliente;
use App\Models\Empleado;
use Livewire\Attributes\On;
use Livewire\Component;

class CargaForm extends Component
{
    // Props from parent
    public $id_lote;

    public $fecha;

    public $empleados_asignados_ids = [];

    public $maquinarias_asignadas_ids = [];

    public $cargas = [];

    // Form state
    public $carga_id_categoria_madera;

    public $carga_ticket;

    public $carga_peso_bruto;

    public $carga_tara;

    public $carga_peso_neto;

    public $carga_id_chofer;

    public $carga_destino;

    public $carga_empleados = [];

    public $carga_maquinarias = [];

    // Search state
    public $busqueda_chofer = '';

    public $busqueda_cliente = '';

    public $busqueda_empleado = '';

    public $busqueda_maquinaria = '';

    // ============ COMPUTED PROPERTIES (Catálogos) ============

    public function getCategoriasMaderaProperty()
    {
        return CategoriaMadera::orderBy('nombre')->get();
    }

    public function getChoferesProperty()
    {
        return Chofer::where('estado', true)
            ->orderBy('apellido')
            ->get();
    }

    public function getClientesProperty()
    {
        return Cliente::orderBy('razon_social')->get();
    }

    public function getEmpleadosProperty()
    {
        return Empleado::with('rolLaboral')
            ->whereNull('fecha_fin_actividades')
            ->orderBy('apellido')
            ->get();
    }

    public function getMaquinariasProperty()
    {
        return \App\Models\Maquinaria::with('tipoMaquinaria')
            ->orderBy('modelo')
            ->get();
    }

    // ============ COMPUTED PROPERTIES (Filtrados) ============

    public function getChoferesFiltradosProperty()
    {
        if (empty($this->busqueda_chofer)) {
            return $this->choferes;
        }

        $busq = strtolower($this->busqueda_chofer);

        return $this->choferes->filter(function ($chofer) use ($busq) {
            $nombre_completo = strtolower($chofer->apellido.' '.$chofer->nombre);

            return str_contains($nombre_completo, $busq);
        });
    }

    public function getClientesFiltradosProperty()
    {
        if (empty($this->busqueda_cliente)) {
            return $this->clientes;
        }

        $busq = strtolower($this->busqueda_cliente);

        return $this->clientes->filter(function ($cliente) use ($busq) {
            return str_contains(strtolower($cliente->razon_social), $busq);
        });
    }

    public function getEmpleadosCargaFiltradosProperty()
    {
        $empleados = $this->empleados;
        if (! empty($this->empleados_asignados_ids)) {
            $empleados = $empleados->filter(function ($emp) {
                return in_array($emp->id_empleado, $this->empleados_asignados_ids);
            });
        }

        if (! empty($this->busqueda_empleado)) {
            $busq = strtolower($this->busqueda_empleado);
            $empleados = $empleados->filter(function ($emp) use ($busq) {
                return str_contains(strtolower($emp->apellido.' '.$emp->nombre), $busq);
            });
        }

        return $empleados;
    }

    public function getMaquinariasCargaFiltradaProperty()
    {
        $maquinarias = $this->maquinarias;
        if (! empty($this->maquinarias_asignadas_ids)) {
            $maquinarias = $maquinarias->filter(function ($maq) {
                return in_array($maq->id_maquinaria, $this->maquinarias_asignadas_ids);
            });
        }

        if (! empty($this->busqueda_maquinaria)) {
            $busq = strtolower($this->busqueda_maquinaria);
            $maquinarias = $maquinarias->filter(function ($maq) use ($busq) {
                return str_contains(strtolower($maq->modelo), $busq);
            });
        }

        return $maquinarias;
    }

    // ============ METHODS ============

    public function agregarCarga(): void
    {
        $this->validate([
            'carga_id_categoria_madera' => 'required|exists:categoria_maderas,id_categoria_madera',
            'carga_ticket' => 'required|string|max:100',
            'carga_peso_bruto' => 'required|numeric|min:0.01',
            'carga_tara' => 'required|numeric|min:0',
            'carga_peso_neto' => 'required|numeric|min:0.01',
            'carga_id_chofer' => 'required|exists:choferes,id_chofer',
            'carga_destino' => 'required|exists:clientes,id_cliente',
            'carga_empleados' => 'required|array|min:1',
            'carga_maquinarias' => 'required|array|min:1',
        ], [
            'carga_id_categoria_madera.required' => 'La categoría de madera es obligatoria',
            'carga_ticket.required' => 'El número de ticket es obligatorio',
            'carga_peso_bruto.required' => 'El peso bruto es obligatorio',
            'carga_peso_bruto.min' => 'El peso bruto debe ser mayor a 0',
            'carga_tara.required' => 'La tara es obligatoria',
            'carga_peso_neto.required' => 'El peso neto es obligatorio',
            'carga_peso_neto.min' => 'El peso neto debe ser mayor a 0',
            'carga_id_chofer.required' => 'El chofer es obligatorio',
            'carga_destino.required' => 'El destino (cliente) es obligatorio',
            'carga_empleados.required' => 'Debe seleccionar al menos un empleado',
            'carga_empleados.min' => 'Debe seleccionar al menos un empleado',
            'carga_maquinarias.required' => 'Debe seleccionar al menos una maquinaria para la carga',
            'carga_maquinarias.min' => 'Debe seleccionar al menos una maquinaria para la carga',
        ]);

        $cliente = Cliente::find($this->carga_destino);
        $nombreCliente = $cliente ? $cliente->razon_social : 'Cliente no encontrado';

        $cargaData = [
            'id_categoria_madera' => $this->carga_id_categoria_madera,
            'ticket' => $this->carga_ticket,
            'peso_bruto' => $this->carga_peso_bruto,
            'tara' => $this->carga_tara,
            'peso_neto' => $this->carga_peso_neto,
            'id_chofer' => $this->carga_id_chofer,
            'destino' => $this->carga_destino,
            'destino_nombre' => $nombreCliente,
            'empleados' => $this->carga_empleados,
            'maquinarias' => $this->carga_maquinarias,
        ];

        $this->dispatch('cargaAgregada', cargaData: $cargaData);
        $this->resetCargaForm();
    }

    public function eliminarCarga(int $index): void
    {
        $this->dispatch('cargaEliminada', index: $index);
    }

    #[On('loteChanged')]
    public function loteChanged(): void
    {
        $this->busqueda_chofer = '';
        $this->busqueda_cliente = '';
        $this->busqueda_empleado = '';
        $this->busqueda_maquinaria = '';
    }

    // ============ PRIVATE HELPERS ============

    private function resetCargaForm(): void
    {
        $this->carga_id_categoria_madera = null;
        $this->carga_ticket = null;
        $this->carga_peso_bruto = null;
        $this->carga_tara = null;
        $this->carga_peso_neto = null;
        $this->carga_id_chofer = null;
        $this->carga_destino = null;
        $this->carga_empleados = [];
        $this->carga_maquinarias = [];
        $this->busqueda_chofer = '';
        $this->busqueda_cliente = '';
    }

    private function calcularPesoNeto(): void
    {
        if ($this->carga_peso_bruto && $this->carga_tara) {
            $this->carga_peso_neto = $this->carga_peso_bruto - $this->carga_tara;
        } else {
            $this->carga_peso_neto = null;
        }
    }

    // ============ LIFECYCLE HOOKS ============

    public function updatedCargaPesoBruto(): void
    {
        $this->calcularPesoNeto();
    }

    public function updatedCargaTara(): void
    {
        $this->calcularPesoNeto();
    }

    public function updatedBusquedaEmpleado(): void
    {
        // Trigger re-computation
    }

    public function updatedBusquedaMaquinaria(): void
    {
        // Trigger re-computation
    }

    public function render()
    {
        return view('livewire.carga-form');
    }
}
