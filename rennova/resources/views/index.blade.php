@extends('layouts.app')

@section('content')
<style>
    /* * Tus estilos para el fondo y las tarjetas.
     * Esto está perfecto y no se toca.
     */
    body, .main-content.bg-light {
        background: #F4F7F6 !important;
    }
    .erp-welcome {
        font-size: 2.1rem;
        font-weight: 700;
        color: #2A6041;
        margin-bottom: 2.5rem;
        margin-top: 1.5rem;
    }
    .erp-card-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 2.2rem;
        justify-content: flex-start;
    }
    .erp-nav-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(44, 62, 80, 0.07);
        padding: 2.2rem 2rem 1.7rem 2rem;
        min-width: 240px;
        max-width: 270px;
        flex: 1 1 240px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .erp-nav-card .erp-icon {
        font-size: 2.2rem;
        color: #2A6041;
        margin-bottom: 0.7rem;
    }
    .erp-nav-card .card-title {
        color: #2A6041;
        font-size: 1.18rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .erp-nav-card .card-btn {
        background: #2A6041;
        color: #fff;
        border: none;
        border-radius: 7px;
        padding: 0.45rem 1.2rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        margin-top: 1.1rem;
        transition: background 0.13s;
        text-decoration: none;
        display: inline-block;
        box-shadow: 0 1px 4px rgba(44, 62, 80, 0.07);
    }
    .erp-nav-card .card-btn:hover {
        background: #1e4630;
        color: #fff;
    }
    @media (max-width: 900px) {
        .erp-card-grid { gap: 1rem; }
        .erp-nav-card { padding: 1.2rem 1rem; min-width: 180px; max-width: 100%; }
    }
</style>

<!-- 
  Se eliminó el div exterior que tenía 'margin-left: 0' y 'max-width'.
  Este div interior ahora se renderizará dentro del contenedor 
  principal de 'layouts.app', permitiendo que la barra lateral 
  funcione correctamente.
-->
<div style="margin-top: 4.5rem;">
    <div class="erp-welcome text-center">Bienvenido {{ Auth::user()->name ?? 'Usuario' }}</div>
    <!-- Fila 1 -->
    <div class="erp-card-grid justify-content-center">
        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-truck"></i></div>
            <div class="card-title">Maquinaria</div>
            <a href="{{ route('modulos.maquinaria') }}" class="card-btn">Acceder</a>
        </div>
        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-tree"></i></div>
            <div class="card-title">Inventario Forestal</div>
            <a href="{{ route('modulos.inventario-forestal') }}" class="card-btn">Acceder</a>
        </div>
        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-person"></i></div>
            <div class="card-title">Personal</div>
            <a href="{{ route('modulos.personal') }}" class="card-btn">Acceder</a>
        </div>
    </div>

    <!-- Fila 2 -->
    <div class="erp-card-grid justify-content-center">
        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-clipboard-check"></i></div>
            <div class="card-title">Operaciones</div>
            <a href="{{ route('modulos.operaciones') }}" class="card-btn">Acceder</a>
        </div>
        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-bar-chart"></i></div>
            <div class="card-title">Estadísticas</div>
            <a href="{{ route('historico-costos-maquinarias.index') }}" class="card-btn">Ver Informes</a>
        </div>
        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-shield-lock"></i></div>
            <div class="card-title">Administración</div>
            <a href="{{ route('modulos.administracion') }}" class="card-btn">Configurar</a>
        </div>
    </div>
</div>
@endsection