<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0"><i class="bi bi-list-check"></i> Planificar tareas del Lote #{{ $lote->id_lote }}</h4>
            <div class="text-muted small">
                Definí qué actividades vas a realizar (ej: 5 ha raleo + 5 ha tala rasa). Esto alimenta el histórico y dispara recomendaciones.
            </div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('lotes.index') }}">
                <i class="bi bi-arrow-left"></i> Volver a Lotes
            </a>
            <a class="btn btn-outline-primary" href="{{ route('lotes.recomendaciones', ['loteId' => $lote->id_lote]) }}">
                <i class="bi bi-magic"></i> Ver recomendaciones
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-tree"></i> Lote</strong>
            <span class="badge bg-secondary">{{ $lote->especie ?? 'Sin especie' }}</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small">Ubicación</div>
                    <div class="fw-semibold">{{ $lote->ubicacion }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Superficie</div>
                    <div class="fw-semibold">{{ number_format((float) ($lote->superficie ?? 0), 2) }} ha</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Estado</div>
                    <div>
                        <span class="badge bg-{{ $lote->estado === 'en_proceso' ? 'primary' : 'success' }}">{{ $lote->estado }}</span>
                    </div>
                </div>
            </div>

            <hr>

            @error('tareas')
                <div class="alert alert-danger"><small>{{ $message }}</small></div>
            @enderror

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 220px;">Tipo de tarea</th>
                            <th style="width: 160px;" class="text-end">Superficie (ha)</th>
                            <th>Observaciones</th>
                            <th style="width: 80px;" class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tareas as $i => $row)
                            <tr wire:key="row-{{ $i }}">
                                <td>
                                    <select class="form-select" wire:model.live="tareas.{{ $i }}.tipo_tarea" @if($guardando) disabled @endif>
                                        @foreach($taskTypes as $tt)
                                            <option value="{{ $tt->value }}" wire:key="option-{{ $tt->value }}">{{ $tt->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('tareas.' . $i . '.tipo_tarea')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" class="form-control text-end" wire:model.live="tareas.{{ $i }}.superficie_afectada_ha" placeholder="(opcional)" @if($guardando) disabled @endif>
                                    @error('tareas.' . $i . '.superficie_afectada_ha')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <input type="text" class="form-control" wire:model.live="tareas.{{ $i }}.observaciones" placeholder="Opcional" @if($guardando) disabled @endif>
                                    @error('tareas.' . $i . '.observaciones')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-outline-danger btn-sm" type="button" wire:click="removeTareaRow({{ $i }})" @if($guardando) disabled @endif>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4">
                                <div class="d-flex flex-wrap justify-content-between align-items-center">
                                    <button class="btn btn-outline-secondary" type="button" wire:click="addTareaRow" @if($guardando) disabled @endif>
                                        <i class="bi bi-plus"></i> Agregar tarea
                                    </button>
                                    <div class="text-muted small">
                                        Total planificado: <strong>{{ number_format($this->totalSuperficie, 2) }} ha</strong>
                                        · Superficie lote: <strong>{{ number_format((float) ($lote->superficie ?? 0), 2) }} ha</strong>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="alert alert-info mb-0">
                <small>
                    Tip: si dejás la superficie en blanco, se asume la del lote al estimar (pero para dividir 5/5 completá superficies).
                </small>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <button class="btn btn-primary" type="button" wire:click="guardar" @if($guardando) disabled @endif>
                <i class="bi bi-check2-circle"></i> Guardar y generar recomendaciones
            </button>
        </div>
    </div>
</div>
