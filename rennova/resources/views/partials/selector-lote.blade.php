@php
    $lotes = $lotes ?? \App\Models\Lote::all();
    $loteSeleccionado = $loteSeleccionado ?? ($lotes->first() ?? null);
@endphp

@if(!empty($lotes) && $lotes->count() > 0)
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('home') }}" class="d-flex align-items-center gap-3">
            <label class="fw-semibold text-muted small mb-0">Seleccionar lote:</label>
            <select name="lote" class="form-select form-select-sm" style="max-width: 320px;">
                @foreach($lotes as $op)
                    <option value="{{ $op->id }}" @selected(optional($loteSeleccionado)->id === $op->id)>
                        {{ $op->nombre ?? ('Lote #' . $op->id) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm" style="background-color: var(--primary-color); color: white;">Ver</button>
        </form>
    </div>
</div>
@endif
