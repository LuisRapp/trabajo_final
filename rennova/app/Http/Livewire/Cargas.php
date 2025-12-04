<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Carga;
use App\Models\Lote;
use App\Models\CategoriaMadera;
use App\Models\ParteDiario;

class Cargas extends Component
{
    public $cargas = [];
    public $lotes = [];
    public $categorias = [];
    public $partes = [];

    public $carga_id, $id_lote, $id_categoria_madera, $id_chofer, $id_parte_diario, $ticket, $peso_bruto, $tara, $peso_neto, $destino, $fecha_carga;
    public $busqueda = '';

    protected $rules = [
        'id_lote' => 'required|exists:lotes,id_lote',
        'id_categoria_madera' => 'nullable|exists:categoria_maderas,id_categoria_madera',
        'id_chofer' => 'nullable|integer',
        'id_parte_diario' => 'nullable|exists:parte_diarios,id_parte_diario',
        'ticket' => 'nullable|string|max:20',
        'peso_bruto' => 'nullable|numeric|min:0',
        'tara' => 'nullable|numeric|min:0',
        'peso_neto' => 'nullable|numeric|min:0',
        'destino' => 'nullable|string|max:100',
        'fecha_carga' => 'required|date|before_or_equal:today',
    ];

    public function mount()
    {
        $this->lotes = Lote::all();
        $this->categorias = CategoriaMadera::all();
        $this->partes = ParteDiario::all();
        $this->cargarCargas();
    }

    public function cargarCargas()
    {
        $query = Carga::with(['lote', 'parteDiario', 'categoriaMadera', 'chofer']);

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('ticket', 'ILIKE', "%{$busq}%")
                  ->orWhere('destino', 'ILIKE', "%{$busq}%")
                  ->orWhereRaw("CAST(peso_bruto AS TEXT) ILIKE ?", ["%{$busq}%"]) 
                  ->orWhereRaw("CAST(peso_neto AS TEXT) ILIKE ?", ["%{$busq}%"]) 
                  ->orWhereDate('fecha_carga', $busq)
                  ->orWhereHas('lote', function($qr) use ($busq) {
                      $qr->where('propietario', 'ILIKE', "%{$busq}%")
                         ->orWhere('ubicacion', 'ILIKE', "%{$busq}%");
                  })
                  ->orWhereHas('categoriaMadera', function($qr) use ($busq) {
                      $qr->where('nombre', 'ILIKE', "%{$busq}%");
                  })
                  ->orWhereHas('chofer', function($qr) use ($busq) {
                      $qr->where('apellido', 'ILIKE', "%{$busq}%")
                         ->orWhere('nombre', 'ILIKE', "%{$busq}%");
                  });
            });
        }

        $this->cargas = $query->orderBy('id_carga', 'desc')->get();
    }

    public function render()
    {
        $this->cargarCargas();
        return view('livewire.cargas');
    }

    public function updatedBusqueda()
    {
        $this->cargarCargas();
    }

    public function guardar()
    {
        $this->validate();
        // Bloqueo adicional: evitar fecha futura
        if (\Carbon\Carbon::parse($this->fecha_carga)->isAfter(\Carbon\Carbon::today())) {
            session()->flash('error', 'La fecha de la carga no puede ser futura.');
            return;
        }
        // Si el campo destino es un id, buscar el nombre del cliente
        $nombre_cliente = null;
        if (is_numeric($this->destino) && $this->destino) {
            $cliente = \App\Models\Cliente::find($this->destino);
            if ($cliente) {
                $nombre_cliente = $cliente->razon_social;
            }
        }
        // Si no es id, usar el valor tal cual
        $valor_destino = $nombre_cliente ?? $this->destino;
        $carga = Carga::updateOrCreate(
            ['id_carga' => $this->carga_id],
            [
                'id_lote' => $this->id_lote,
                'id_categoria_madera' => $this->id_categoria_madera,
                'id_chofer' => $this->id_chofer,
                'id_parte_diario' => $this->id_parte_diario,
                'ticket' => $this->ticket,
                'peso_bruto' => $this->peso_bruto,
                'tara' => $this->tara,
                'peso_neto' => $this->peso_neto,
                'destino' => $valor_destino,
                'fecha_carga' => $this->fecha_carga,
            ]
        );
        $this->cargarCargas();
        session()->flash('message', $this->carga_id ? 'Carga actualizada correctamente.' : 'Carga creada correctamente.');
        $this->resetCampos();
        $this->dispatch('cargaGuardada');
    }

    public function editar($id)
    {
        $carga = Carga::findOrFail($id);
        $this->carga_id = $carga->id_carga;
        $this->id_lote = $carga->id_lote;
        $this->id_categoria_madera = $carga->id_categoria_madera;
        $this->id_chofer = $carga->id_chofer;
        $this->id_parte_diario = $carga->id_parte_diario;
        $this->ticket = $carga->ticket;
        $this->peso_bruto = $carga->peso_bruto;
        $this->tara = $carga->tara;
        $this->peso_neto = $carga->peso_neto;
        $this->destino = $carga->destino;
        $this->fecha_carga = $carga->fecha_carga;
    }

    public function eliminar($id)
    {
        Carga::findOrFail($id)->delete();
        $this->cargarCargas();
        session()->flash('message', 'Carga eliminada correctamente.');
        $this->resetCampos();
    }

    public function resetCampos()
    {
        $this->reset([
            'carga_id', 'id_lote', 'id_categoria_madera', 'id_chofer', 'id_parte_diario', 'ticket',
            'peso_bruto', 'tara', 'peso_neto', 'destino', 'fecha_carga'
        ]);
    }
}
