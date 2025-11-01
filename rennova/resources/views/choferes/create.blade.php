@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-plus-circle"></i> Nuevo Chofer</h1>
        <a href="{{ route('choferes.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form method="POST" action="{{ route('choferes.store') }}">
                @csrf
                
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
                        <select name="id_cliente" class="form-select @error('id_cliente') is-invalid @enderror" required>
                            <option value="">Seleccione un cliente...</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id_cliente }}" @selected(old('id_cliente')==$cliente->id_cliente)>
                                    {{ $cliente->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_cliente') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">El chofer estará asociado a este cliente</small>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Apellido <span class="text-danger">*</span></label>
                        <input type="text" name="apellido" value="{{ old('apellido') }}" 
                               class="form-control @error('apellido') is-invalid @enderror" required placeholder="Apellido del chofer">
                        @error('apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" 
                               class="form-control @error('nombre') is-invalid @enderror" required placeholder="Nombre del chofer">
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">DNI <span class="text-danger">*</span></label>
                        <input type="text" name="dni" value="{{ old('dni') }}" 
                               class="form-control @error('dni') is-invalid @enderror" required placeholder="Ej: 12345678">
                        @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono') }}" 
                               class="form-control @error('telefono') is-invalid @enderror" placeholder="Ej: +54 9 11 1234-5678">
                        @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Estado</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="estado" id="estado" value="activo" {{ old('estado', 'activo') == 'activo' ? 'checked' : '' }}>
                            <label class="form-check-label" for="estado">
                                <span class="badge bg-success">Activo</span>
                            </label>
                        </div>
                        <small class="text-muted">Los choferes inactivos no aparecerán en los formularios de carga</small>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('choferes.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar Chofer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
