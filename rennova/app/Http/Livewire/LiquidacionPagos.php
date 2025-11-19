<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Empleado;
use App\Models\Recibo;
use App\Models\Adelanto;
use Carbon\Carbon;

class LiquidacionPagos extends Component
{
    public $empleados = [];
    public $id_empleado;
    public $fecha_inicio;
    public $fecha_fin;
    
    // Datos calculados
    public $calculo = null;
    public $empleado_seleccionado = null;
    public $adelantos_pendientes = [];
    public $total_adelantos = 0;
    
    // Datos editables para el recibo
    public $monto_bruto;
    public $descuentos = 0;
    public $monto_neto;
    public $observaciones;
    
    // Control de flujo
    public $mostrar_liquidacion = false;
    public $recibo_generado = false;

    protected $rules = [
        'id_empleado' => 'required|exists:empleados,id_empleado',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        'monto_bruto' => 'required|numeric|min:0',
        'descuentos' => 'nullable|numeric|min:0',
        'observaciones' => 'nullable|string|max:150',
    ];

    protected $messages = [
        'id_empleado.required' => 'Debe seleccionar un empleado',
        'fecha_inicio.required' => 'Debe ingresar una fecha de inicio',
        'fecha_fin.required' => 'Debe ingresar una fecha de fin',
        'fecha_fin.after_or_equal' => 'La fecha fin debe ser posterior o igual a la fecha de inicio',
        'monto_bruto.required' => 'El monto bruto es requerido',
        'monto_bruto.min' => 'El monto bruto no puede ser negativo',
        'descuentos.min' => 'Los descuentos no pueden ser negativos',
    ];

    public function mount()
    {
        $this->empleados = Empleado::with('rolLaboral')
            ->whereNull('fecha_fin_actividades')
            ->orderBy('apellido')
            ->get();
        
        // Valores por defecto: mes actual
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function calcularLiquidacion()
    {
        $this->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $this->empleado_seleccionado = Empleado::with('rolLaboral')->find($this->id_empleado);
        
        if (!$this->empleado_seleccionado) {
            session()->flash('error', 'Empleado no encontrado');
            return;
        }

        // Ejecutar el cálculo
        $this->calculo = $this->empleado_seleccionado->calcularPagoRango(
            $this->fecha_inicio,
            $this->fecha_fin
        );

        // Buscar adelantos pendientes en el rango de fechas
        $this->adelantos_pendientes = Adelanto::where('id_empleado', $this->id_empleado)
            ->where('estado', 'pendiente')
            ->whereBetween('fecha_emision', [$this->fecha_inicio, $this->fecha_fin])
            ->where('activo', true)
            ->get();
        
        $this->total_adelantos = $this->adelantos_pendientes->sum('monto');

        // Cargar valores editables
        $this->monto_bruto = $this->calculo['total_pagar_final'];
        $this->descuentos = $this->total_adelantos; // Descuento automático por adelantos
        $this->monto_neto = $this->monto_bruto - $this->descuentos;
        
        // Generar observaciones automáticas
        $obs_base = sprintf(
            'Liquidación período %s a %s - %d días caídos + %.2f ton',
            Carbon::parse($this->fecha_inicio)->format('d/m/Y'),
            Carbon::parse($this->fecha_fin)->format('d/m/Y'),
            $this->calculo['cantidad_dias_caidos'],
            $this->calculo['total_peso_toneladas'] ?? 0
        );
        
        if ($this->total_adelantos > 0) {
            $obs_base .= sprintf(' | Adelantos: $%.2f', $this->total_adelantos);
        }
        
        $this->observaciones = $obs_base;

        $this->mostrar_liquidacion = true;
        $this->recibo_generado = false;
    }

    public function updatedDescuentos()
    {
        $this->calcularMontoNeto();
    }

    public function updatedMontoBruto()
    {
        $this->calcularMontoNeto();
    }

    private function calcularMontoNeto()
    {
        $this->monto_neto = max(0, ($this->monto_bruto ?? 0) - ($this->descuentos ?? 0));
    }

    public function generarRecibo()
    {
        $this->validate([
            'monto_bruto' => 'required|numeric|min:0',
            'descuentos' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:150',
        ]);

        try {
            \DB::beginTransaction();
            
            $recibo = Recibo::create([
                'id_empleado' => $this->empleado_seleccionado->id_empleado,
                'fecha_emision' => now(),
                'monto_bruto' => $this->monto_bruto,
                'descuentos' => $this->descuentos ?? 0,
                'monto' => $this->monto_neto,
                'observaciones' => $this->observaciones,
                'activo' => true,
            ]);

            // Marcar adelantos como pagados
            if ($this->adelantos_pendientes && count($this->adelantos_pendientes) > 0) {
                foreach ($this->adelantos_pendientes as $adelanto) {
                    $adelanto->estado = 'pagado';
                    $adelanto->save();
                }
            }

            \DB::commit();
            
            $this->recibo_generado = true;
            
            $mensaje = 'Recibo #' . $recibo->id_recibo . ' generado correctamente.';
            if (count($this->adelantos_pendientes) > 0) {
                $mensaje .= ' Se marcaron ' . count($this->adelantos_pendientes) . ' adelanto(s) como pagado(s).';
            }
            
            session()->flash('message', $mensaje);
            
            // Limpiar formulario después de 2 segundos (opcional)
            $this->dispatch('reciboGenerado');

        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Error al generar el recibo: ' . $e->getMessage());
        }
    }

    public function nuevaLiquidacion()
    {
        $this->reset([
            'id_empleado', 
            'calculo', 
            'empleado_seleccionado', 
            'adelantos_pendientes',
            'total_adelantos',
            'monto_bruto', 
            'descuentos', 
            'monto_neto', 
            'observaciones', 
            'mostrar_liquidacion', 
            'recibo_generado'
        ]);
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.liquidacion-pagos');
    }
}
