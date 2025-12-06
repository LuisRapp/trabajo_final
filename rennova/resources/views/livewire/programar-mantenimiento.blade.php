
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">
            <i class="bi bi-calendar-check text-primary"></i> Programar Mantenimiento
        </h1>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-4 mb-3">
                <div class="col-md-6">
                    <div class="alert alert-info mb-0">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle-fill me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                <strong>{{ $notificacion->titulo }}</strong>
                                <p class="mb-0 mt-1 small">{{ $notificacion->mensaje }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-light rounded p-3 h-100">
                        <h6 class="text-primary mb-2">
                            <i class="bi bi-tools"></i> Detalles del Mantenimiento
                        </h6>
                        <div class="row g-2">
                            <div class="col-12">
                                <small class="text-muted">Maquinaria:</small>
                                <div class="fw-semibold">{{ $mantenimiento->maquinaria->nombre ?? 'N/A' }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Tipo:</small>
                                <div class="fw-semibold">{{ $mantenimiento->tipoMantenimiento->nombre ?? 'N/A' }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Estado:</small>
                                <div>
                                    @if($mantenimiento->estado === 'programado')
                                        <span class="badge bg-info">Programado</span>
                                    @elseif($mantenimiento->estado === 'en curso')
                                        <span class="badge bg-warning">En Curso</span>
                                    @elseif($mantenimiento->estado === 'completado')
                                        <span class="badge bg-success">Completado</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($mantenimiento->estado) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Fecha de Inicio:</small>
                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($mantenimiento->fecha_inicio)->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <form wire:submit.prevent="guardarFecha">
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-md-6">
                        <label for="fechaProgramada" class="form-label fw-semibold">
                            <i class="bi bi-calendar3"></i> Fecha Programada <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="fechaProgramada"
                            class="form-control @error('fechaProgramada') is-invalid @enderror" 
                            wire:model="fechaProgramada"
                            min="{{ $fechaMinima }}"
                            max="{{ $fechaMaxima }}"
                        >
                        @error('fechaProgramada')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i>
                            La fecha debe estar dentro del rango permitido: 
                            <strong>{{ \Carbon\Carbon::parse($fechaMinima)->format('d/m/Y') }}</strong> 
                            a 
                            <strong>{{ \Carbon\Carbon::parse($fechaMaxima)->format('d/m/Y') }}</strong>
                            (7 días desde la notificación)
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('home') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Confirmar y Programar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
