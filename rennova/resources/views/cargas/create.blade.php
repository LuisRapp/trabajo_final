@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-plus-circle"></i> Nueva Carga</h1>
        <a href="{{ route('cargas.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form method="POST" action="{{ route('cargas.store') }}">
                @csrf
                
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Lote <span class="text-danger">*</span></label>
                        <select name="id_lote" class="form-select @error('id_lote') is-invalid @enderror" required>
                            <option value="">Seleccione un lote...</option>
                            @foreach($lotes as $lote)
                                <option value="{{ $lote->id_lote }}" @selected(old('id_lote')==$lote->id_lote)>
                                    Lote #{{ $lote->id_lote }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_lote') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Categoría Madera</label>
                        <select name="id_categoria_madera" class="form-select @error('id_categoria_madera') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id_categoria_madera }}" @selected(old('id_categoria_madera')==$cat->id_categoria_madera)>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_categoria_madera') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Parte Diario</label>
                        <select name="id_parte_diario" class="form-select @error('id_parte_diario') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($partes as $parte)
                                <option value="{{ $parte->id_parte_diario }}" @selected(old('id_parte_diario')==$parte->id_parte_diario)>
                                    Parte #{{ $parte->id_parte_diario }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_parte_diario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Chofer</label>
                        <select name="id_chofer" class="form-select @error('id_chofer') is-invalid @enderror">
                            <option value="">Seleccione un chofer...</option>
                            @foreach($choferes as $ch)
                                <option value="{{ $ch->id_chofer }}" @selected(old('id_chofer')==$ch->id_chofer)>
                                    {{ $ch->cliente?->nombre }} - {{ $ch->apellido }} {{ $ch->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_chofer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Ticket</label>
                        <input type="text" name="ticket" value="{{ old('ticket') }}" maxlength="20" 
                               class="form-control @error('ticket') is-invalid @enderror" placeholder="Número de ticket">
                        @error('ticket') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha Carga <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_carga" value="{{ old('fecha_carga', now()->toDateString()) }}" 
                               class="form-control @error('fecha_carga') is-invalid @enderror" required>
                        @error('fecha_carga') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Peso Bruto (kg)</label>
                        <input type="number" step="0.01" name="peso_bruto" value="{{ old('peso_bruto') }}" 
                               class="form-control @error('peso_bruto') is-invalid @enderror" placeholder="0.00">
                        @error('peso_bruto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tara (kg)</label>
                        <input type="number" step="0.01" name="tara" value="{{ old('tara') }}" 
                               class="form-control @error('tara') is-invalid @enderror" placeholder="0.00">
                        @error('tara') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Peso Neto (kg)</label>
                        <input type="number" step="0.01" name="peso_neto" value="" 
                               class="form-control bg-light" id="peso_neto" readonly>
                        <small class="text-muted">Calculado automáticamente</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Destino</label>
                        <input type="text" name="destino" value="{{ old('destino') }}" maxlength="100" 
                               class="form-control @error('destino') is-invalid @enderror" placeholder="Lugar de destino">
                        @error('destino') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('cargas.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar Carga
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function calcularPesoNeto() {
        const bruto = parseFloat(document.querySelector('[name=peso_bruto]').value) || 0;
        const tara = parseFloat(document.querySelector('[name=tara]').value) || 0;
        document.getElementById('peso_neto').value = (bruto - tara).toFixed(2);
    }
    document.querySelector('[name=peso_bruto]').addEventListener('input', calcularPesoNeto);
    document.querySelector('[name=tara]').addEventListener('input', calcularPesoNeto);
    window.addEventListener('DOMContentLoaded', calcularPesoNeto);
</script>
@endpush
@endsection
