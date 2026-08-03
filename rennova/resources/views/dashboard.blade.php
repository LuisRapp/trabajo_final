@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Panel de Control</h1>
            <p class="mt-2 text-slate-600">Bienvenido/a, {{ auth()->user()->name }}</p>
        </div>

        {{-- Alerta si no hay lotes activos --}}
        @if(!$hayLotesActivos)
            <div class="mb-8 bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <flux:icon.exclamation-triangle class="size-6 text-yellow-600" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-yellow-800">No hay lotes activos</h3>
                        <p class="mt-1 text-yellow-700">Creá un lote para comenzar a gestionar operaciones forestales.</p>
                        <a href="{{ route('lotes.index') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-sm font-medium transition-colors">
                            <flux:icon.plus class="size-4" />
                            Crear lote
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Métricas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            {{-- Toneladas Extraídas (Mes) --}}
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-emerald-100 rounded-lg">
                        <flux:icon.scale class="size-6 text-emerald-600" />
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-600">Toneladas Extraídas (Mes)</h3>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($toneladasExtraidas, 1) }} <span class="text-lg font-normal text-slate-500">TN</span></p>
            </div>

            {{-- Cargas del Mes --}}
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <flux:icon.truck class="size-6 text-blue-600" />
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-600">Cargas del Mes</h3>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($cargasMes) }}</p>
            </div>

            {{-- Días Operativos (Mes) --}}
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-amber-100 rounded-lg">
                        <flux:icon.calendar class="size-6 text-amber-600" />
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-600">Días Operativos (Mes)</h3>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($diasOperativos) }}</p>
            </div>

            {{-- Costo Promedio por TN --}}
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-rose-100 rounded-lg">
                        <flux:icon.banknotes class="size-6 text-rose-600" />
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-600">Costo Prom. por TN (Mes)</h3>
                <p class="mt-2 text-3xl font-bold text-slate-900">${{ number_format($costoPromedioTn, 2) }}</p>
            </div>

            {{-- Precio Venta Promedio por TN --}}
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <flux:icon.tag class="size-6 text-green-600" />
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-600">Precio Venta Prom. por TN (Mes)</h3>
                <p class="mt-2 text-3xl font-bold text-slate-900">${{ number_format($precioVentaPromedioTn, 2) }}</p>
            </div>

            {{-- Mantenimientos Pendientes --}}
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <flux:icon.wrench class="size-6 text-purple-600" />
                    </div>
                </div>
                <h3 class="text-sm font-medium text-slate-600">Mantenimientos Pendientes</h3>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($mantenimientosPendientes) }}</p>
            </div>
        </div>

        {{-- Accesos Rápidos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="{{ route('maquinarias.index') }}" class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6 group">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition-colors">
                        <flux:icon.truck class="size-6 text-blue-600" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Gestión de Maquinaria</h3>
                        <p class="text-sm text-slate-600">Administrar equipos</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('insumos.index') }}" class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 p-6 group">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-amber-100 rounded-lg group-hover:bg-amber-200 transition-colors">
                        <flux:icon.cube class="size-6 text-amber-600" />
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
                        <flux:icon.clipboard-document-list class="size-6 text-emerald-600" />
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
                        <flux:icon.calculator class="size-6 text-rose-600" />
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
