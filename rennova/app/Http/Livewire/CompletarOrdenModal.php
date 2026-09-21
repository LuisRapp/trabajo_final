<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Services\MantenimientoService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class CompletarOrdenModal extends Component
{
    use MensajesErrorUsuario;

    public bool $mostrarModal = false;

    public ?int $orden_completar_id = null;

    public array $orden_completar_info = [];

    public bool $orden_es_correctivo = false;

    public ?string $fecha_fin_completar = null;

    public ?float $costo_total_completar = null;

    public array $insumos_usados = [];

    public $insumosDisponibles = [];

    public function mount(): void
    {
        $this->insumosDisponibles = app(MantenimientoService::class)->obtenerInsumosParaCierre();
    }

    #[On('abrirCompletarOrden')]
    public function abrirModal(int $id): void
    {
        $servicio = app(MantenimientoService::class);
        $orden = $servicio->obtenerOrdenParaCompletar($id);

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

        $this->orden_es_correctivo = $orden->tipoMantenimiento
            && $servicio->esTipoCorrectivo($orden->tipoMantenimiento);

        if (! $this->orden_es_correctivo) {
            $kitInsumos = $servicio->obtenerKitPreventivoParaMaquinaria(
                $orden->id_maquinaria,
                $orden->id_tipo_mantenimiento
            );

            if (! empty($kitInsumos)) {
                foreach ($kitInsumos as $item) {
                    $this->insumos_usados[] = [
                        'id_insumo' => $item['id_insumo'],
                        'cantidad' => $item['cantidad_requerida'],
                    ];
                }
            } else {
                $this->insumos_usados = [['id_insumo' => '', 'cantidad' => '']];
            }
        } else {
            $this->insumos_usados = [['id_insumo' => '', 'cantidad' => '']];
        }

        $this->mostrarModal = true;
    }

    public function cerrarModal(): void
    {
        $this->mostrarModal = false;
        $this->reset(['orden_completar_id', 'orden_completar_info', 'orden_es_correctivo', 'fecha_fin_completar', 'costo_total_completar', 'insumos_usados']);
    }

    public function agregarInsumo(): void
    {
        $this->insumos_usados[] = ['id_insumo' => '', 'cantidad' => ''];
    }

    public function eliminarInsumo(int $index): void
    {
        unset($this->insumos_usados[$index]);
        $this->insumos_usados = array_values($this->insumos_usados);
    }

    public function completarOrden(): void
    {
        try {
            $orden = app(MantenimientoService::class)
                ->obtenerOrdenParaCompletar($this->orden_completar_id);

            $this->validate([
                'fecha_fin_completar' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) use ($orden) {
                        if ($value < $orden->fecha_inicio) {
                            $fail('La fecha de finalización no puede ser anterior a la fecha de inicio del mantenimiento.');
                        }
                    },
                ],
                'costo_total_completar' => 'nullable|numeric|min:0',
            ]);

            $costoBase = floatval($this->costo_total_completar ?? 0);

            $insumosData = collect($this->insumos_usados)
                ->map(fn ($item) => [
                    'id_insumo' => $item['id_insumo'],
                    'cantidad_utilizada' => $item['cantidad'],
                ])
                ->toArray();

            $servicio = app(MantenimientoService::class);
            $resultado = $servicio->completarMantenimiento(
                $this->orden_completar_id,
                $insumosData,
                $costoBase,
                $this->fecha_fin_completar
            );

            if (! $resultado['success']) {
                session()->flash('error', $resultado['message']);
                $this->addError('general', $resultado['message']);

                return;
            }

            session()->flash('message', 'Orden completada exitosamente. Costo total: $'.number_format($resultado['costo_total'], 2));

            $this->cerrarModal();
            $this->dispatch('ordenCompletada');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error completando orden', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', $this->mensajeErrorUsuario($e, 'completar la orden de mantenimiento'));
            $this->addError('general', 'Ocurrió un error al completar la orden. Intente nuevamente o contacte al administrador.');
        }
    }

    public function render()
    {
        return view('livewire.completar-orden-modal');
    }
}
