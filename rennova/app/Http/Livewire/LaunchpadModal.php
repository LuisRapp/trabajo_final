<?php

namespace App\Http\Livewire;

use App\Enums\TaskType;
use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Jobs\SendPurchaseOrderEmail;
use App\Models\Lote;
use App\Models\PropuestaAsignacion;
use App\Models\PropuestaAsignacionEmpleado;
use App\Services\AutomaticAllocationService;
use App\Services\PropuestaAsignacionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LaunchpadModal extends Component
{
    use MensajesErrorUsuario;

    public bool $showModal = false;

    public ?int $loteId = null;

    public ?PropuestaAsignacion $proposal = null;

    /** @var Collection<int, mixed> */
    public Collection $employees;

    /** @var Collection<int, mixed> */
    public Collection $machinery;

    /** @var Collection<int, mixed> */
    public Collection $supplies;

    public float $supplies_cost = 0.0;

    public float $week_1_fuel = 0.0;

    public array $employeeSelected = [];

    public bool $guardando = false;

    protected $listeners = ['open-launchpad' => 'open'];

    public function mount(): void
    {
        $this->employees = collect();
        $this->machinery = collect();
        $this->supplies = collect();
    }

    public function open(int $loteId): void
    {
        $this->loteId = $loteId;
        $this->showModal = true;

        $this->loadProposal();
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    private function loadProposal(): void
    {
        if (! $this->loteId) {
            return;
        }

        $proposal = PropuestaAsignacion::query()
            ->with([
                'lote',
                'loteTarea',
                'proposedEmployees.empleado.rolLaboral',
                'proposedMaquinarias.maquinaria.tipoMaquinaria',
                'proposedInsumos.insumo.unidadMedida',
            ])
            ->where('id_lote', $this->loteId)
            ->orderByDesc('id_allocation_proposal')
            ->first();

        if (! $proposal) {
            $lote = Lote::find($this->loteId);
            if (! $lote) {
                return;
            }

            $taskType = TaskType::tryFrom((string) $lote->main_task_type);
            if ($taskType) {
                $proposal = app(AutomaticAllocationService::class)->proposeForLotAndTask(
                    lote: $lote,
                    taskType: $taskType,
                );
                $proposal->load([
                    'lote',
                    'loteTarea',
                    'proposedEmployees.empleado.rolLaboral',
                    'proposedMaquinarias.maquinaria.tipoMaquinaria',
                    'proposedInsumos.insumo.unidadMedida',
                ]);
            }
        }

        $this->proposal = $proposal;

        $this->employees = $proposal?->proposedEmployees ?? collect();
        $this->machinery = $proposal?->proposedMaquinarias ?? collect();
        $this->supplies = $proposal?->proposedInsumos ?? collect();

        $this->employeeSelected = $this->employees
            ->mapWithKeys(fn ($row) => [$row->id_allocation_proposal_employee => (bool) $row->selected])
            ->toArray();

        $this->supplies_cost = (float) $this->supplies->sum(fn ($row) => (float) ($row->costo_estimado_semana_1 ?? 0));

        $this->week_1_fuel = (float) $this->supplies
            ->filter(function ($row) {
                $name = mb_strtolower((string) ($row->insumo->nombre ?? ''));

                return str_contains($name, 'diesel') || str_contains($name, 'gasoil');
            })
            ->sum(fn ($row) => (float) ($row->cantidad_semana_1 ?? 0));
    }

    public function confirmAndLaunch(): void
    {
        if (! $this->proposal || ! $this->loteId) {
            return;
        }

        $this->guardando = true;
        $requiresReview = false;
        $reviewMessage = null;

        try {
            DB::transaction(function () use (&$requiresReview, &$reviewMessage) {
                foreach ($this->employeeSelected as $rowId => $selected) {
                    PropuestaAsignacionEmpleado::where('id_allocation_proposal_employee', (int) $rowId)
                        ->where('id_allocation_proposal', (int) $this->proposal->id_allocation_proposal)
                        ->update(['selected' => (bool) $selected]);
                }

                /** @var PropuestaAsignacion $proposal */
                $proposal = PropuestaAsignacion::query()
                    ->with(['lote', 'proposedEmployees', 'proposedMaquinarias'])
                    ->lockForUpdate()
                    ->findOrFail((int) $this->proposal->id_allocation_proposal);

                $lote = $proposal->lote;
                if (! $lote) {
                    return;
                }

                $meta = $proposal->meta ?? [];
                $lowConfidence = PropuestaAsignacionService::esBajaConfianza($meta);
                if ($lowConfidence && $proposal->status !== 'confirmed') {
                    $meta['review_required'] = true;
                    $meta['reviewed_at'] = now()->toISOString();
                    $proposal->meta = $meta;
                    $proposal->status = 'confirmed';
                    if (! $proposal->confirmed_at) {
                        $proposal->confirmed_at = now();
                    }
                    $proposal->save();
                    $requiresReview = true;
                    $reviewMessage = 'Propuesta con baja confianza. Confirmada para revisiÃ³n manual. Vuelva a aplicar para asignar.';

                    return;
                }

                $empleadosIds = $proposal->proposedEmployees
                    ->where('selected', true)
                    ->pluck('id_empleado')
                    ->map(fn ($v) => (int) $v)
                    ->values()
                    ->toArray();

                $maquinariasIds = $proposal->proposedMaquinarias
                    ->where('selected', true)
                    ->pluck('id_maquinaria')
                    ->map(fn ($v) => (int) $v)
                    ->values()
                    ->toArray();

                $busyEmployees = PropuestaAsignacionService::buscarEmpleadosOcupados($empleadosIds, (int) $lote->id_lote);
                if (! empty($busyEmployees)) {
                    throw new \RuntimeException('Algunos empleados ya estÃ¡n asignados a otros lotes en proceso.');
                }

                $busyMaquinarias = PropuestaAsignacionService::buscarMaquinariasOcupadas($maquinariasIds, (int) $lote->id_lote);
                if (! empty($busyMaquinarias)) {
                    throw new \RuntimeException('Algunas maquinarias ya estÃ¡n asignadas a otros lotes en proceso.');
                }

                PropuestaAsignacionService::cerrarPropuestasCompetidoras($proposal);

                $lote->empleados()->sync($empleadosIds);
                $lote->maquinarias()->sync($maquinariasIds);

                $lote->estado = 'en_proceso';
                $lote->save();

                $proposal->status = 'applied';
                if (! $proposal->confirmed_at) {
                    $proposal->confirmed_at = now();
                }
                $proposal->applied_at = now();
                $proposal->save();
            });

            if ($requiresReview) {
                session()->flash('message', $reviewMessage);

                return;
            }

            SendPurchaseOrderEmail::dispatch($this->proposal->id_allocation_proposal);
            $this->showModal = false;

            session()->flash('message', 'Asignación aplicada y lote iniciado.');
        } catch (\Throwable $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'iniciar la operación'));
        } finally {
            $this->guardando = false;
        }
    }

    public function render()
    {
        return view('livewire.launchpad-modal');
    }
}
