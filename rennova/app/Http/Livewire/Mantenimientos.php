<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\TipoMantenimiento;
use Illuminate\Support\Facades\DB;

class Mantenimientos extends Component
{
    public $mantenimientos, $mantenimiento_id, $id_maquinaria, $id_tipo_mantenimiento, $fecha_inicio, $estado, $busqueda = '';
    public $maquinarias, $tipos;
    public $kitPreventivo = [];
    public $activeTab = 'nuevo';
    
    // Modal completar
    public $mostrarModalCompletar = false;
    public $orden_completar_id = null;
    public $orden_completar_info = [];
    public $orden_es_correctivo = false;
    public $fecha_fin_completar;
    public $costo_total_completar;
    public $insumos_usados = [];

    protected $rules = [
        'id_maquinaria' => 'required|exists:maquinarias,id_maquinaria',
        'id_tipo_mantenimiento' => 'required|exists:tipo_mantenimientos,id_tipo_mantenimiento',
        'fecha_inicio' => 'required|date',
        'estado' => 'required|in:programado,en curso',
    ];

    protected $messages = [
        'id_maquinaria.required' => 'Debe seleccionar una maquinaria.',
        'id_tipo_mantenimiento.required' => 'Debe seleccionar un tipo de mantenimiento.',
        'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
        'estado.required' => 'El estado es obligatorio.',
    ];

    public function mount()
    {
        $this->maquinarias = Maquinaria::where('estado', '!=', 'dado_de_baja')->orderBy('modelo')->get();
        $this->tipos = TipoMantenimiento::where('activo', true)->orderBy('nombre')->get();
        $this->fecha_inicio = date('Y-m-d');
        $this->estado = 'programado';
        // Pestaña por defecto: si hay órdenes, mostrar listado
        $this->activeTab = \App\Models\Mantenimiento::count() > 0 ? 'listado' : 'nuevo';
    }

    public function updatedIdMaquinaria()
    {
        $this->cargarKitPreventivo();
    }

    public function updatedIdTipoMantenimiento()
    {
        $this->cargarKitPreventivo();
    }

    public function cargarKitPreventivo()
    {
        $this->kitPreventivo = [];
        
        if ($this->id_maquinaria && $this->id_tipo_mantenimiento) {
            $tipo = TipoMantenimiento::find($this->id_tipo_mantenimiento);
            
            if ($tipo && str_contains(strtolower($tipo->nombre), 'preventivo')) {
                $this->kitPreventivo = \App\Models\KitMantenimientoPreventivo::where('kit_mantenimiento_preventivo.id_maquinaria', $this->id_maquinaria)
                    ->join('insumos', 'kit_mantenimiento_preventivo.id_insumo', '=', 'insumos.id_insumo')
                    ->select('insumos.nombre', 'kit_mantenimiento_preventivo.cantidad_requerida')
                    ->get()
                    ->toArray();
            }
        }
    }

    public function render()
    {
        $this->cargarMantenimientos();
        return view('livewire.mantenimientos');
    }

    public function cargarMantenimientos()
    {
        $query = Mantenimiento::with(['maquinaria', 'tipoMantenimiento']);

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function($q) use ($busq) {
                $q->where('estado', 'ILIKE', '%' . $busq . '%')
                  ->orWhereRaw("CAST(costo_total AS TEXT) ILIKE ?", ['%' . $busq . '%'])
                  ->orWhereHas('maquinaria', function($qm) use ($busq) {
                      $qm->where('modelo', 'ILIKE', '%' . $busq . '%');
                  })
                  ->orWhereHas('tipoMantenimiento', function($qt) use ($busq) {
                      $qt->where('nombre', 'ILIKE', '%' . $busq . '%');
                  });
            });
        }

        $this->mantenimientos = $query->orderBy('id_mantenimiento', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarMantenimientos();
    }

    public function guardar()
    {
        $this->validate();

        $mantenimiento = Mantenimiento::create([
            'id_maquinaria' => $this->id_maquinaria,
            'id_tipo_mantenimiento' => $this->id_tipo_mantenimiento,
            'fecha_inicio' => $this->fecha_inicio,
            'estado' => $this->estado,
        ]);

        $tipoNombre = TipoMantenimiento::find($this->id_tipo_mantenimiento)->nombre ?? 'Mantenimiento';
        $maquinaNombre = Maquinaria::find($this->id_maquinaria)->modelo ?? '';
        
        session()->flash('message', "Orden de {$tipoNombre} creada correctamente para {$maquinaNombre}.");
        $this->resetCampos();
        $this->activeTab = 'listado';
        $this->dispatch('mantenimientoGuardado');
    }

    public function editar($id)
    {
        $mantenimiento = Mantenimiento::findOrFail($id);
        $this->mantenimiento_id = $mantenimiento->id_mantenimiento;
        $this->id_maquinaria = $mantenimiento->id_maquinaria;
        $this->id_tipo_mantenimiento = $mantenimiento->id_tipo_mantenimiento;
        $this->fecha_inicio = $mantenimiento->fecha_inicio;
        $this->estado = $mantenimiento->estado;
        $this->activeTab = 'nuevo';
    }

    public function eliminar($id)
    {
        Mantenimiento::findOrFail($id)->delete();
        session()->flash('message', 'Mantenimiento eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['mantenimiento_id', 'id_maquinaria', 'id_tipo_mantenimiento']);
        $this->fecha_inicio = date('Y-m-d');
        $this->estado = 'programado';
        $this->kitPreventivo = [];
    }

    public function abrirModalCompletar($id)
    {
        $orden = Mantenimiento::with(['maquinaria', 'tipoMantenimiento'])->findOrFail($id);

        $this->orden_completar_id = $orden->id_mantenimiento;
        $this->orden_completar_info = [
            'id' => $orden->id_mantenimiento,
            'maquinaria' => $orden->maquinaria?->modelo ?? 'N/A',
            'tipo' => $orden->tipoMantenimiento?->nombre ?? 'N/A',
            'fecha_inicio' => $orden->fecha_inicio,
        ];

        $this->fecha_fin_completar = date('Y-m-d');
        $this->costo_total_completar = null;
        $this->insumos_usados = [];

        $this->orden_es_correctivo = str_contains(strtolower($this->orden_completar_info['tipo']), 'correctivo');

        if ($this->orden_es_correctivo) {
            $this->insumos_usados = [['id_insumo' => '', 'cantidad' => '', 'precio_unitario' => '']];
        }
        
        $this->activeTab = 'listado';
        $this->mostrarModalCompletar = true;
        $this->dispatch('modal-completar-opened', id: $id);
    }

    public function cerrarModalCompletar()
    {
        $this->mostrarModalCompletar = false;
        $this->reset(['orden_completar_id', 'orden_completar_info', 'orden_es_correctivo', 'fecha_fin_completar', 'costo_total_completar', 'insumos_usados']);
    }

    public function agregarInsumo()
    {
        $this->insumos_usados[] = ['id_insumo' => '', 'cantidad' => '', 'precio_unitario' => ''];
    }

    public function eliminarInsumo($index)
    {
        unset($this->insumos_usados[$index]);
        $this->insumos_usados = array_values($this->insumos_usados);
    }

    public function completarOrden()
    {
        try {
            \Log::info('Iniciando completarOrden', ['orden_id' => $this->orden_completar_id]);
            
            $orden = Mantenimiento::with(['maquinaria', 'tipoMantenimiento'])->findOrFail($this->orden_completar_id);
            
            \Log::info('Orden encontrada', ['orden' => $orden->toArray()]);
            
            $this->validate([
                'fecha_fin_completar' => 'required|date|after_or_equal:' . $orden->fecha_inicio,
                'costo_total_completar' => 'nullable|numeric|min:0',
            ]);

            \Log::info('Validación pasada');

            // Calcular costo total automáticamente sumando insumos
            $costoTotal = floatval($this->costo_total_completar ?? 0);
            
            if ($this->orden_es_correctivo && count($this->insumos_usados) > 0) {
                foreach ($this->insumos_usados as $insumo) {
                    if (!empty($insumo['id_insumo']) && !empty($insumo['cantidad']) && !empty($insumo['precio_unitario'])) {
                        $costoTotal += floatval($insumo['cantidad']) * floatval($insumo['precio_unitario']);
                    }
                }
            }

            \Log::info('Costo total calculado', ['costo' => $costoTotal]);

            DB::beginTransaction();
            
            // Actualizar orden
            $orden->fecha_fin = $this->fecha_fin_completar;
            $orden->costo_total = $costoTotal;
            $orden->estado = 'completado';
            $orden->save();

            \Log::info('Orden actualizada');

            // Si es correctivo, registrar insumos usados
            if ($this->orden_es_correctivo && count($this->insumos_usados) > 0) {
                foreach ($this->insumos_usados as $insumo) {
                    if (!empty($insumo['id_insumo']) && !empty($insumo['cantidad'])) {
                        // Registrar movimiento de stock (salida)
                        DB::table('movimiento_stocks')->insert([
                            'id_insumo' => $insumo['id_insumo'],
                            'tipo' => 'salida',
                            'cantidad' => $insumo['cantidad'],
                            'fecha' => $this->fecha_fin_completar,
                            'motivo' => 'Mantenimiento Correctivo - Orden #' . $orden->id_mantenimiento,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $precioUnitario = floatval($insumo['precio_unitario'] ?? 0);
                        $cantidad = floatval($insumo['cantidad']);
                        $subtotal = $cantidad * $precioUnitario;

                        // Registrar en mantenimiento_insumos
                        DB::table('mantenimiento_insumos')->insert([
                            'id_mantenimiento' => $orden->id_mantenimiento,
                            'id_insumo' => $insumo['id_insumo'],
                            'cantidad_utilizada' => $cantidad,
                            'costo_unitario' => $precioUnitario,
                            'subtotal' => $subtotal,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
                \Log::info('Insumos registrados');
            }

            DB::commit();
            
            \Log::info('Transacción confirmada');
            
            session()->flash('message', 'Orden completada exitosamente. Costo total: $' . number_format($costoTotal, 2));
            
            $this->cerrarModalCompletar();
            $this->cargarMantenimientos();
            
            \Log::info('Proceso completado exitosamente');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación', ['errors' => $e->errors()]);
            $this->addError('general', 'Error de validación: ' . implode(', ', array_map(fn($err) => implode(', ', $err), $e->errors())));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error completando orden', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error al completar la orden: ' . $e->getMessage());
            $this->addError('general', $e->getMessage());
        }
    }
}
