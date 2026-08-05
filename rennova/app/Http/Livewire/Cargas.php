<?php

namespace App\Http\Livewire;

use App\Models\Carga;
use App\Models\CategoriaMadera;
use App\Models\Lote;
use App\Models\ParteDiario;
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

    protected $rules = [
        'id_lote' => 'required|exists:lotes,id_lote',
        'id_categoria_madera' => 'nullable|exists:categoria_maderas,id_categoria_madera',
        'id_chofer' => 'nullable|integer',
        'id_parte_diario' => 'nullable|exists:parte_diarios,id_parte_diario',
        'ticket' => 'nullable|string|max:20',
        'peso_bruto' => 'nullable|numeric|min:0',
        'tara' => 'nullable|numeric|min:0',
        'peso_neto' => 'nullable|numeric|min:0',
        'id_cliente' => 'nullable|exists:clientes,id_cliente',
        'fecha_carga' => 'required|date|before_or_equal:today',
    ];

    public function mount()
    {
        $this->lotes = Lote::all();
        $this->categorias = CategoriaMadera::all();
        $this->partes = ParteDiario::all();
    }

    public function getCargas()
    {
        $query = Carga::with(['lote', 'parteDiario', 'categoriaMadera', 'chofer', 'cliente']);

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('ticket', 'ILIKE', "%{$busq}%")
                    ->orWhereRaw('CAST(peso_bruto AS TEXT) ILIKE ?', ["%{$busq}%"])
                    ->orWhereRaw('CAST(peso_neto AS TEXT) ILIKE ?', ["%{$busq}%"])
                    ->orWhereDate('fecha_carga', $busq)
                    ->orWhereHas('lote', function ($qr) use ($busq) {
                        $qr->where('propietario', 'ILIKE', "%{$busq}%")
                            ->orWhere('ubicacion', 'ILIKE', "%{$busq}%");
                    })
                    ->orWhereHas('categoriaMadera', function ($qr) use ($busq) {
                        $qr->where('nombre', 'ILIKE', "%{$busq}%");
                    })
                    ->orWhereHas('chofer', function ($qr) use ($busq) {
                        $qr->where('apellido', 'ILIKE', "%{$busq}%")
                            ->orWhere('nombre', 'ILIKE', "%{$busq}%");
                    })
                    ->orWhereHas('cliente', function ($qr) use ($busq) {
                        $qr->where('razon_social', 'ILIKE', "%{$busq}%");
                    });
            });
        }

        return $query->orderBy('id_carga', 'desc')->paginate($this->pagina);
    }

    public function render()
    {
        $clientes = \App\Models\Cliente::orderBy('razon_social')->get();

        return view('livewire.cargas', [
            'cargas' => $this->getCargas(),
            'clientes' => $clientes,
        ]);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();
        // Bloqueo adicional: evitar fecha futura
        if (\Carbon\Carbon::parse($this->fecha_carga)->isAfter(\Carbon\Carbon::today())) {
            session()->flash('error', 'La fecha de la carga no puede ser futura.');

            return;
        }
        $carga = Carga::updateOrCreate(
            ['id_carga' => $this->carga_id],
            [
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
            ]
        );
        session()->flash('message', $this->carga_id ? 'Carga actualizada correctamente.' : 'Carga creada correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->resetPage();
        $this->dispatch('cargaGuardada');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
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
        $this->id_cliente = $carga->id_cliente;
        $this->fecha_carga = $carga->fecha_carga;
    }

    public function eliminar($id)
    {
        Carga::findOrFail($id)->delete();
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
