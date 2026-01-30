@php
    $lotes = $lotes ?? \App\Models\Lote::where('estado', 'activo')->get();
    $loteSeleccionado = $loteSeleccionado ?? ($lotes->first() ?? null);
@endphp

@if($lotes && $lotes->count() > 0)
<div class="card mb-4 border-0 shadow-sm" style="background-color: #f8f9fa; border: 2px solid var(--primary-color);">
    <div class="card-body">
        <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-3">
            <label class="fw-semibold text-dark small mb-0" style="min-width: fit-content;">📍 Seleccionar lote:</label>
            <select name="lote" class="form-select form-select-sm" style="max-width: 350px; border: 2px solid var(--primary-color);" onchange="this.form.submit()">
                @foreach($lotes as $op)
                    <option value="{{ $op->id_lote }}" @selected(optional($loteSeleccionado)->id_lote === $op->id_lote)>
                        {{ $op->nombre ?? $op->propietario ?? ('Lote #' . $op->id_lote) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-primary" style="background-color: var(--primary-color); border: none;">Actualizar</button>
        </form>
    </div>
</div>
@else
<div class="alert alert-warning mb-4">
    <i class="bi bi-exclamation-triangle"></i> No hay lotes activos disponibles
</div>
@endif
