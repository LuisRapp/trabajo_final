@extends('layouts.app')

@section('content')
<style>
    body, .main-content.bg-light {
        background: #F4F7F6 !important;
    }
    .module-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2A6041;
        margin-bottom: 1rem;
        text-align: center;
    }
    .module-subtitle {
        font-size: 1.1rem;
        color: #6c757d;
        margin-bottom: 3rem;
        text-align: center;
    }
    .erp-card-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 2.2rem;
        justify-content: center;
        max-width: 1200px;
        margin: 0 auto;
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
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .erp-nav-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 20px rgba(44, 62, 80, 0.15);
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
        padding: 0.5rem 1.5rem;
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
</style>

<div class="container py-4">
    <h1 class="module-title"><i class="bi bi-person"></i> Gestión de Personal</h1>
    <p class="module-subtitle">Administra empleados, choferes, pagos y asignaciones</p>

    <div class="erp-card-grid">
        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-person-workspace"></i></div>
            <div class="card-title">Empleados</div>
            <a href="{{ route('empleados.index') }}" class="card-btn">Gestionar</a>
        </div>

        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-person-vcard"></i></div>
            <div class="card-title">Choferes</div>
            <a href="{{ route('choferes.index') }}" class="card-btn">Gestionar</a>
        </div>

        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="card-title">Adelantos</div>
            <a href="{{ route('adelantos.index') }}" class="card-btn">Gestionar</a>
        </div>

        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div class="card-title">Recibos</div>
            <a href="{{ route('recibos.index') }}" class="card-btn">Gestionar</a>
        </div>

        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-calculator"></i></div>
            <div class="card-title">Liquidación de Pagos</div>
            <a href="{{ route('liquidacion-pagos.index') }}" class="card-btn">Gestionar</a>
        </div>

        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-link-45deg"></i></div>
            <div class="card-title">Asignaciones por Lote</div>
            <a href="{{ route('asignaciones-lote.index') }}" class="card-btn">Gestionar</a>
        </div>

        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-person-badge"></i></div>
            <div class="card-title">Roles Laborales</div>
            <a href="{{ route('roles-laborales.index') }}" class="card-btn">Gestionar</a>
        </div>

        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-clock-history"></i></div>
            <div class="card-title">Histórico Roles</div>
            <a href="{{ route('historico-roles-laborales.index') }}" class="card-btn">Ver Histórico</a>
        </div>
    </div>
</div>
@endsection
