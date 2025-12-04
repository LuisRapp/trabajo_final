@extends('layouts.app')

@section('content')

    {{-- 1. ALERTAS DEL SISTEMA --}}
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- 2. SELECTOR DE LOTE --}}
    @include('partials.selector-lote', ['lotes' => $lotes ?? \App\Models\Lote::all(), 'loteSeleccionado' => $loteSeleccionado ?? null])

    {{-- 3. COMPONENTE DE CLIMA --}}
    @if(isset($pronosticoData) && !empty($pronosticoData))
        <div class="mb-5">
            <x-clima.pronostico 
                :alerta="$pronosticoData['alerta'] ?? null"
                :pronostico="$pronosticoData['pronostico'] ?? []"
                :analisisImpacto="$pronosticoData['analisisImpacto'] ?? []"
                :lote="isset($loteSeleccionado) ? ($loteSeleccionado->nombre ?? ('Lote #' . $loteSeleccionado->id)) : null" 
            />
        </div>
    @endif

    {{-- 4. ACCESOS RÁPIDOS (PANEL DE CONTROL) --}}
    <div>
        <h3 class="fw-bold text-secondary mb-4 opacity-75">Panel de Control</h3>

        {{-- Grupo 1: Operativos --}}
        <h6 class="text-uppercase text-muted fw-bold mb-3 small">Módulos Operativos</h6>
        <div class="row g-4 mb-5">
            <!-- Maquinaria -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body text-center p-4">
                        <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-truck display-4"></i></div>
                        <h5 class="card-title fw-bold">Maquinaria</h5>
                        <p class="text-muted small">Gestión de flota</p>
                        <a href="{{ route('modulos.maquinaria') }}" class="btn btn-outline-success w-100 stretched-link">Ingresar</a>
                    </div>
                </div>
            </div>

            <!-- Inventario -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body text-center p-4">
                        <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-tree display-4"></i></div>
                        <h5 class="card-title fw-bold">Inventario</h5>
                        <p class="text-muted small">Control de lotes</p>
                        <a href="{{ route('modulos.inventario-forestal') }}" class="btn btn-outline-success w-100 stretched-link">Ingresar</a>
                    </div>
                </div>
            </div>

            <!-- Personal -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body text-center p-4">
                        <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-people display-4"></i></div>
                        <h5 class="card-title fw-bold">Personal</h5>
                        <p class="text-muted small">RRHH y legajos</p>
                        <a href="{{ route('modulos.personal') }}" class="btn btn-outline-success w-100 stretched-link">Ingresar</a>
                    </div>
                </div>
            </div>

            <!-- Operaciones -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-elevate bg-success bg-opacity-10">
                    <div class="card-body text-center p-4">
                        <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-clipboard-check display-4"></i></div>
                        <h5 class="card-title fw-bold text-success">Operaciones</h5>
                        <p class="text-muted small">Producción diaria</p>
                        <a href="{{ route('modulos.operaciones') }}" class="btn btn-success w-100 stretched-link">Registrar</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grupo 2: Gestión --}}
        <h6 class="text-uppercase text-muted fw-bold mb-3 small">Gestión y Análisis</h6>
        <div class="row g-4">
            <!-- Reportes -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-light rounded p-3 me-3 text-primary"><i class="bi bi-bar-chart fs-2"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Reportes</h6>
                            <a href="{{ route('historico-costos-maquinarias.index') }}" class="text-decoration-none small stretched-link">Ver costos e históricos</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-light rounded p-3 me-3 text-warning"><i class="bi bi-box-seam fs-2"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Stock (FIFO)</h6>
                            <a href="{{ route('modulos.operaciones.gestionstock') }}" class="text-decoration-none small stretched-link">Gestionar Insumos</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-light rounded p-3 me-3 text-secondary"><i class="bi bi-gear fs-2"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Configuración</h6>
                            <a href="{{ route('modulos.administracion') }}" class="text-decoration-none small stretched-link">Ajustes del sistema</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
