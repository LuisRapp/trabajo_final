@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-secondary mb-2"><i class="bi bi-tree me-2"></i>Inventario Forestal</h1>
        <p class="text-muted lead">Gestiona lotes, clientes, ventas y productos forestales</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Lotes -->
        @can('ver-lotes')
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-geo-alt display-4"></i></div>
                    <h5 class="card-title fw-bold">Lotes</h5>
                    <a href="{{ route('lotes.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        @endcan

        <!-- Clientes -->
        @can('ver-clientes')
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-people display-4"></i></div>
                    <h5 class="card-title fw-bold">Clientes</h5>
                    <a href="{{ route('clientes.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        @endcan

        <!-- Ventas -->
        @can('ver-ventas')
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-receipt display-4"></i></div>
                    <h5 class="card-title fw-bold">Ventas</h5>
                    <a href="{{ route('ventas.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        @endcan

        <!-- Cargas -->
        @can('ver-cargas')
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-box-seam display-4"></i></div>
                    <h5 class="card-title fw-bold">Cargas</h5>
                    <a href="{{ route('cargas.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        @endcan

        <!-- Categorías -->
        @can('ver-categorias-madera')
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-tree display-4"></i></div>
                    <h5 class="card-title fw-bold">Categorías de Madera</h5>
                    <a href="{{ route('categorias-madera.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        @endcan

        <!-- Lista de Precios -->
        @can('ver-lista-precios')
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-tags display-4"></i></div>
                    <h5 class="card-title fw-bold">Lista de Precios</h5>
                    <a href="{{ route('lista-precios.index') }}" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection
