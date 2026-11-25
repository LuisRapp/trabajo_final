<?php

namespace App\Http\Livewire;

use App\Enums\TaskType;
use App\Jobs\GenerateAllocationProposalsForLote;
use App\Models\Lote;
use App\Models\LoteTarea;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LotePlanificacionTareas extends Component
{
    public int $loteId;

    public $lote;

    /**
     * Array de filas: [ ['tipo_tarea' => 'raleo', 'superficie_afectada_ha' => 5, 'observaciones' => '...'], ...]
     */
    public array $tareas = [];

    public bool $guardando = false;

    public function mount($loteId)
    {
        $this->loteId = (int) $loteId;
        $this->lote = Lote::findOrFail($this->loteId);

        // Un lote cerrado o de baja no admite cambios en su planificacion.
        abort_unless(
            in_array($this->lote->estado, ['activo', 'en_proceso', 'inactivo'], true),
            403,
            'El lote está cerrado y no admite cambios en las tareas.'
        );

        $existentes = LoteTarea::query()
            ->where('id_lote', $this->loteId)
            ->where('estado', 'planificada')
            ->orderBy('id_lote_tarea')
            ->get();

        if ($existentes->isEmpty()) {
            // La tarea principal elegida al crear el lote es la primera actividad.
            $this->tareas = [[
                'tipo_tarea' => $this->lote->main_task_type ?: TaskType::TALA_RASA->value,
                'superficie_afectada_ha' => (float) ($this->lote->superficie ?? 0),
                'observaciones' => null,
            ]];
        } else {
            $this->tareas = $existentes->map(fn ($t) => [
                'tipo_tarea' => (string) $t->tipo_tarea,
                'superficie_afectada_ha' => $t->superficie_afectada_ha !== null ? (float) $t->superficie_afectada_ha : null,
                'observaciones' => $t->observaciones,
            ])->values()->all();
        }
    }

    public function addTareaRow(): void
    {
        $this->tareas[] = [
            'tipo_tarea' => TaskType::RALEO->value,
            'superficie_afectada_ha' => null,
            'observaciones' => null,
        ];
    }

    public function removeTareaRow(int $index): void
    {
        if (! isset($this->tareas[$index])) {
            return;
        }

        array_splice($this->tareas, $index, 1);

        if (empty($this->tareas)) {
            $this->addTareaRow();
        }
    }

    public function getTotalSuperficieProperty(): float
    {
        $sum = 0.0;
        foreach ($this->tareas as $row) {
            if (isset($row['superficie_afectada_ha']) && is_numeric($row['superficie_afectada_ha'])) {
                $sum += (float) $row['superficie_afectada_ha'];
            }
        }

        return round($sum, 2);
    }

    public function guardar(): void
    {
        $this->authorize('editar-lotes');

        abort_unless(
            in_array($this->lote->estado, ['activo', 'en_proceso', 'inactivo'], true),
            403,
            'El lote está cerrado y no admite cambios en las tareas.'
        );

        $this->guardando = true;

        try {
            $this->validate([
                'tareas' => 'required|array|min:1',
                'tareas.*.tipo_tarea' => 'required|in:'.implode(',', array_map(fn ($c) => $c->value, TaskType::cases())),
                'tareas.*.superficie_afectada_ha' => 'nullable|numeric|min:0.01',
                'tareas.*.observaciones' => 'nullable|string|max:500',
            ]);

            $superficieLote = (float) ($this->lote->superficie ?? 0);
            $total = $this->totalSuperficie;

            if ($superficieLote > 0 && $total > $superficieLote + 0.0001) {
                $this->addError('tareas', 'La suma de superficies ('.$total.' ha) supera la superficie del lote ('.$superficieLote.' ha).');

                return;
            }

            DB::transaction(function () {
                // Reemplaza solo la planificación (no toca tareas en_ejecucion).
                LoteTarea::query()
                    ->where('id_lote', $this->loteId)
                    ->where('estado', 'planificada')
                    ->whereDoesntHave('partesDiarios')
                    ->delete();

                foreach ($this->tareas as $row) {
                    LoteTarea::create([
                        'id_lote' => $this->loteId,
                        'tipo_tarea' => (string) $row['tipo_tarea'],
                        'estado' => 'planificada',
                        'superficie_afectada_ha' => $row['superficie_afectada_ha'] !== null && $row['superficie_afectada_ha'] !== ''
                            ? round((float) $row['superficie_afectada_ha'], 2)
                            : null,
                        'observaciones' => $row['observaciones'] ?? null,
                    ]);
                }
            });

            GenerateAllocationProposalsForLote::dispatch($this->loteId);

            session()->flash('message', 'Tareas guardadas. El siguiente paso es asignar los recursos.');
            $this->redirect(route('lotes.ficha', ['loteId' => $this->loteId]));
        } finally {
            $this->guardando = false;
        }
    }

    public function render()
    {
        return view('livewire.lote-planificacion-tareas', [
            'taskTypes' => TaskType::cases(),
        ]);
    }
}
