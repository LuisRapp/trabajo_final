<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Models\Lote;
use App\Models\PropuestaAsignacion;
use App\Services\PropuestaAsignacionService;
use Livewire\Component;

class AllocationProposals extends Component
{
    use MensajesErrorUsuario;

    public $loteId;

    public $lotes = [];

    public $filter_lote_id = '';

    public $filter_status = '';

    public $proposals = [];

    public $mostrar_listado = true;

    public $selected_proposal_id;

    public $selectedProposal;

    public $employeeSelected = [];

    public $maquinariaSelected = [];

    public $insumoSelected = [];

    public $guardando = false;

    public function mount($loteId = null)
    {
        $this->loteId = $loteId !== null ? (int) $loteId : null;

        $this->lotes = Lote::orderBy('id_lote', 'desc')->get();

        if ($this->loteId) {
            $this->filter_lote_id = (string) $this->loteId;
        }

        $this->refreshProposals();

        if ($this->loteId) {
            // Al entrar desde un lote no hay historial que navegar: se muestra
            // directamente la sugerencia vigente (la mas nueva sin cerrar).
            $this->mostrar_listado = false;

            $vigente = $this->proposals->first(fn ($propuesta) => $propuesta->status !== 'closed');

            if ($vigente) {
                $this->seleccionar((int) $vigente->id_allocation_proposal);
            }
        }
    }

    public function getLoteParaGenerarProperty(): ?Lote
    {
        $id = $this->loteId ?: (int) $this->filter_lote_id;

        return $id ? $this->lotes->firstWhere('id_lote', $id) : null;
    }

    public function updatedFilterLoteId()
    {
        $this->refreshProposals();
    }

    public function updatedFilterStatus()
    {
        $this->refreshProposals();
    }

    public function refreshProposals()
    {
        $query = PropuestaAsignacion::query()
            ->with(['lote', 'loteTarea'])
            ->orderByDesc('id_allocation_proposal');

        if (! empty($this->filter_lote_id)) {
            $query->where('id_lote', (int) $this->filter_lote_id);
        }

        if (! empty($this->filter_status)) {
            $query->where('status', $this->filter_status);
        }

        $this->proposals = $query->limit(200)->get();

        if ($this->selected_proposal_id) {
            $exists = $this->proposals->firstWhere('id_allocation_proposal', (int) $this->selected_proposal_id);
            if (! $exists) {
                $this->resetSelection();
            }
        }
    }

    public function generarAhora()
    {
        $this->authorize('crear-propuestas-asignacion');
        $loteId = $this->loteId ?: (int) $this->filter_lote_id;
        if (! $loteId) {
            session()->flash('error', 'Seleccione un lote para generar propuestas.');

            return;
        }

        $this->guardando = true;

        try {
            $resultado = PropuestaAsignacionService::generar($loteId);

            $this->refreshProposals();

            if ($resultado['error']) {
                session()->flash('error', $resultado['error']);

                return;
            }

            // La generacion cierra los borradores anteriores: si la seleccion no se
            // mueve a la propuesta nueva, la pantalla sigue mostrando la vieja (ya
            // cerrada) con sus datos y parece que no se encontro nada.
            $masReciente = $this->proposals->first()?->id_allocation_proposal;

            if ($this->loteId && $masReciente) {
                $this->seleccionar((int) $masReciente);
            } elseif ($this->selected_proposal_id) {
                $this->loadSelectedProposal();
            }

            session()->flash('message', 'Propuestas generadas correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'generar las propuestas'));
        } finally {
            $this->guardando = false;
        }
    }

    public function seleccionar($proposalId)
    {
        $this->selected_proposal_id = (int) $proposalId;
        $this->mostrar_listado = false;
        $this->loadSelectedProposal();
    }

    public function volver()
    {
        $this->mostrar_listado = true;
    }

    private function resetSelection(): void
    {
        $this->reset([
            'selected_proposal_id',
            'selectedProposal',
            'employeeSelected',
            'maquinariaSelected',
            'insumoSelected',
        ]);
    }

    private function loadSelectedProposal(): void
    {
        if (! $this->selected_proposal_id) {
            $this->resetSelection();

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
            ->find($this->selected_proposal_id);

        if (! $proposal) {
            $this->resetSelection();

            return;
        }

        $this->selectedProposal = $proposal;

        $this->employeeSelected = $proposal->proposedEmployees
            ->mapWithKeys(fn ($row) => [$row->id_allocation_proposal_employee => (bool) $row->selected])
            ->toArray();

        $this->maquinariaSelected = $proposal->proposedMaquinarias
            ->mapWithKeys(fn ($row) => [$row->id_allocation_proposal_maquinaria => (bool) $row->selected])
            ->toArray();

        $this->insumoSelected = $proposal->proposedInsumos
            ->mapWithKeys(fn ($row) => [$row->id_allocation_proposal_insumo => (bool) $row->selected])
            ->toArray();
    }

    public function guardarSeleccion()
    {
        $this->authorize('editar-propuestas-asignacion');
        if (! $this->selected_proposal_id) {
            return;
        }

        $this->guardando = true;

        try {
            $this->persistirSeleccion();

            $this->loadSelectedProposal();
            session()->flash('message', 'Selección guardada correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'guardar la selección'));
        } finally {
            $this->guardando = false;
        }
    }

    public function confirmar()
    {
        abort_unless(auth()->user()?->canAny(['crear-propuestas-asignacion', 'editar-propuestas-asignacion']), 403);
        if (! $this->selected_proposal_id) {
            return;
        }

        $this->guardando = true;

        try {
            $this->persistirSeleccion();

            $resultado = PropuestaAsignacionService::confirmar((int) $this->selected_proposal_id);

            if ($resultado['error']) {
                session()->flash('error', $resultado['error']);

                return;
            }

            $this->loadSelectedProposal();
            $this->refreshProposals();

            if ($resultado['requiresReview']) {
                session()->flash('message', $resultado['reviewMessage']);

                return;
            }

            session()->flash('message', 'Propuesta confirmada y aplicada al lote.');

            if ($this->loteId) {
                return $this->redirect(route('lotes.ficha', ['loteId' => $this->loteId]));
            }
        } catch (\Throwable $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'confirmar la propuesta'));
        } finally {
            $this->guardando = false;
        }
    }

    private function persistirSeleccion(): void
    {
        if (! $this->selected_proposal_id) {
            return;
        }

        app(PropuestaAsignacionService::class)->guardarSeleccion(
            (int) $this->selected_proposal_id,
            $this->employeeSelected,
            $this->maquinariaSelected,
            $this->insumoSelected
        );
    }

    public function render()
    {
        return view('livewire.allocation-proposals');
    }
}
