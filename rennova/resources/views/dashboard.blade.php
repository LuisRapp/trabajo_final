@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Panel de Control</h1>
            <p class="mt-2 text-slate-600">Bienvenido/a, {{ auth()->user()->name }}</p>
        </div>

        {{-- Alerta si no hay lotes activos --}}
        @if(!isset($lotesActivos) || $lotesActivos->isEmpty())
            <div class="mb-8 bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-yellow-800">No hay lotes activos</h3>
                        <p class="mt-1 text-yellow-700">Crea un lote para comenzar a gestionar operaciones forestales.</p>
                        <a href="{{ route('lotes.index') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Crear lote
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Metricas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Total Maquinarias --}}
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-600">Total Maquinarias</h3>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalMaquinarias ?? 0 }}</p>
            </div>

            {{-- Insumos Criticos --}}
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-amber-100 rounded-lg">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-600">Insumos Criticos</h3>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $insumosCriticos ?? 0 }}</p>
            </div>

            {{-- Partes Diarios del Mes --}}
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-emerald-100 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-600">Partes Diarios (Mes)</h3>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $partesDiariosMes ?? 0 }}</p>
            </div>

            {{-- Mantenimientos Pendientes --}}
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-rose-100 rounded-lg">
                        <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-600">Mantenimientos Pendientes</h3>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $mantenimientosPendientes ?? 0 }}</p>
            </div>
        </div>

        {{-- Accesos Rapidos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="{{ route('maquinarias.index') }}" class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6 group">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition-colors">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Gestion de Maquinaria</h3>
                        <p class="text-sm text-slate-600">Administrar equipos</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('insumos.index') }}" class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6 group">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-amber-100 rounded-lg group-hover:bg-amber-200 transition-colors">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Control de Inventario</h3>
                        <p class="text-sm text-slate-600">Gestionar insumos</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('partes-diarios.index') }}" class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6 group">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-emerald-100 rounded-lg group-hover:bg-emerald-200 transition-colors">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Partes Diarios</h3>
                        <p class="text-sm text-slate-600">Registrar operaciones</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('liquidacion-pagos.index') }}" class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6 group">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-rose-100 rounded-lg group-hover:bg-rose-200 transition-colors">
                        <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Liquidaciones</h3>
                        <p class="text-sm text-slate-600">Gestionar pagos</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection
