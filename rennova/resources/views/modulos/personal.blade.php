@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-secondary mb-2"><i class="bi bi-people me-2"></i>Gestión de Personal</h1>
        <p class="text-muted lead">Administra empleados, choferes, pagos y asignaciones</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Empleados -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-person-workspace display-4"></i></div>
                    <h5 class="card-title fw-bold">Empleados</h5>
                    <a href="{{ route('empleados.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>

        <!-- Choferes -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-person-vcard display-4"></i></div>
                    <h5 class="card-title fw-bold">Choferes</h5>
                    <a href="{{ route('choferes.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>

        <!-- Adelantos -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-cash-coin display-4"></i></div>
                    <h5 class="card-title fw-bold">Adelantos</h5>
                    <a href="{{ route('adelantos.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>

        <!-- Recibos -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-file-earmark-text display-4"></i></div>
                    <h5 class="card-title fw-bold">Recibos</h5>
                    <a href="{{ route('recibos.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>

        <!-- Liquidación -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-calculator display-4"></i></div>
                    <h5 class="card-title fw-bold">Liquidación de Pagos</h5>
                    <a href="{{ route('liquidacion-pagos.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>

        <!-- Asignaciones -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-link-45deg display-4"></i></div>
                    <h5 class="card-title fw-bold">Asignaciones por Lote</h5>
                    <a href="{{ route('asignaciones-lote.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>

        <!-- Roles -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-person-badge display-4"></i></div>
                    <h5 class="card-title fw-bold">Roles Laborales</h5>
                    <a href="{{ route('roles-laborales.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>

        <!-- Histórico Roles -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-clock-history display-4"></i></div>
                    <h5 class="card-title fw-bold">Histórico Roles</h5>
                    <a href="{{ route('historico-roles-laborales.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Ver Histórico</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
