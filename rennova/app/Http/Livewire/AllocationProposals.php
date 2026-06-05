<?php

namespace App\Http\Livewire;

use App\Models\Lote;
use App\Models\PropuestaAsignacion;
use App\Services\PropuestaAsignacionService;
use Livewire\Component;

class AllocationProposals extends Component
{
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

        if ($this->loteId && ! $this->selected_proposal_id && $this->proposals && $this->proposals->count() > 0) {
            $this->seleccionar((int) $this->proposals->first()->id_allocation_proposal);
        }
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
        $loteId = $this->loteId ?: (int) $this->filter_lote_id;
        if (! $loteId) {
            session()->flash('error', 'Seleccione un lote para generar propuestas.');

            return;
        }

        app(PropuestaAsignacionService::class)->despacharGeneracionRecomendaciones($loteId);
        session()->flash('message', 'Generación solicitada. Refrescá en unos segundos.');
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
        if (! $this->selected_proposal_id) {
            return;
        }

        $this->guardando = true;

        try {
            $servicio = app(PropuestaAsignacionService::class);
            $servicio->guardarSeleccion(
                (int) $this->selected_proposal_id,
                $this->employeeSelected,
                $this->maquinariaSelected,
                $this->insumoSelected
            );

            $this->loadSelectedProposal();
            session()->flash('message', 'Selección guardada correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al guardar selección: '.$e->getMessage());
        } finally {
            $this->guardando = false;
        }
    }

    public function confirmar()
    {
        if (! $this->selected_proposal_id) {
            return;
        }

        $this->guardando = true;

        try {
            $this->guardarSeleccion();

            $servicio = app(PropuestaAsignacionService::class);
            $servicio->confirmarRecomendacion((int) $this->selected_proposal_id);
            $servicio->enviarOrdenCompraSiCorresponde((int) $this->selected_proposal_id);

            $this->loadSelectedProposal();
            $this->refreshProposals();
            session()->flash('message', 'Propuesta confirmada.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al confirmar: '.$e->getMessage());
        } finally {
            $this->guardando = false;
        }
    }

    public function aplicar()
    {
        if (! $this->selected_proposal_id) {
            return;
        }

        $this->guardando = true;

        try {
            $this->guardarSeleccion();

            $servicio = app(PropuestaAsignacionService::class);
            $servicio->aplicarPropuesta((int) $this->selected_proposal_id);
            $servicio->enviarOrdenCompraSiCorresponde((int) $this->selected_proposal_id);

            $this->loadSelectedProposal();
            $this->refreshProposals();
            session()->flash('message', 'Asignación aplicada al lote.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al aplicar: '.$e->getMessage());
        } finally {
            $this->guardando = false;
        }
    }

    public function render()
    {
        return view('livewire.allocation-proposals');
    }
}
