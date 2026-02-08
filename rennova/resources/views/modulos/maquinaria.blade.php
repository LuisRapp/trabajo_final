@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-secondary mb-2"><i class="bi bi-truck me-2"></i>Gestión de Maquinaria</h1>
        <p class="text-muted lead">Administra tu flota de maquinaria, mantenimientos y costos operativos</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Maquinarias -->
        @can('ver-maquinarias')
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-truck display-4"></i></div>
                    <h5 class="card-title fw-bold">Maquinarias</h5>
                    <a href="{{ route('maquinarias.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        @endcan

        <!-- Mantenimientos -->
        @can('ver-mantenimientos')
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-tools display-4"></i></div>
                    <h5 class="card-title fw-bold">Mantenimientos</h5>
                    <a href="{{ route('mantenimientos.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        @endcan

        <!-- Kits -->
        @can('ver-kits-mantenimiento')
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-gear-fill display-4"></i></div>
                    <h5 class="card-title fw-bold">Kits de Mantenimiento</h5>
                    <a href="{{ route('kits-mantenimiento.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        @endcan

        <!-- Costos -->
        @can('ver-historico-costos-maquinarias')
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-graph-up display-4"></i></div>
                    <h5 class="card-title fw-bold">Costos de Maquinaria</h5>
                    <a href="{{ route('historico-costos-maquinarias.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Ver Histórico</a>
                </div>
            </div>
        </div>
        @endcan

        <!-- Tipos -->
        @can('ver-tipos-maquinaria')
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-gear-wide-connected display-4"></i></div>
                    <h5 class="card-title fw-bold">Tipos de Maquinaria</h5>
                    <a href="{{ route('tipos-maquinaria.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        @endcan

        @can('configurar-notificaciones-mantenimiento')
        <!-- Notificaciones -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-bell-fill display-4"></i></div>
                    <h5 class="card-title fw-bold">Configuración de Notificaciones</h5>
                    <a href="{{ route('configuracion-notificaciones.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Configurar</a>
                </div>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection
