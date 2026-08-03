@extends('layouts.app')

@section('content')

@php
    $alerta = $pronosticoData['alerta'] ?? 'NORMAL';
    $accionRecomendada = $pronosticoData['accion_recomendada'] ?? null;
    $recomendacionDetallada = $pronosticoData['recomendacionDetallada'] ?? '';

    $mapaAcciones = [
        'AUMENTAR_PRODUCCION' => ['label' => 'Aumentar producción', 'colorClass' => 'bg-amber-100 text-amber-800', 'icon' => 'bolt'],
        'MANTENIMIENTO_PREVENTIVO' => ['label' => 'Mantenimiento preventivo', 'colorClass' => 'bg-blue-100 text-blue-800', 'icon' => 'wrench'],
        'SUSPENSION_JORNADA' => ['label' => 'Suspender jornada', 'colorClass' => 'bg-red-100 text-red-800', 'icon' => 'x-circle'],
        'OPERACION_NORMAL' => ['label' => 'Operación normal', 'colorClass' => 'bg-green-100 text-green-800', 'icon' => 'check-circle'],
    ];
    $mapaAlertas = [
        'ACELERAR' => ['label' => 'Aumentar producción', 'colorClass' => 'bg-amber-100 text-amber-800', 'icon' => 'bolt'],
        'SUSPENDER' => ['label' => 'Suspender operaciones', 'colorClass' => 'bg-red-100 text-red-800', 'icon' => 'x-circle'],
        'NORMAL' => ['label' => 'Operación normal', 'colorClass' => 'bg-green-100 text-green-800', 'icon' => 'check-circle'],
    ];
    $alertaInfo = $accionRecomendada && isset($mapaAcciones[$accionRecomendada])
        ? $mapaAcciones[$accionRecomendada]
        : ($mapaAlertas[$alerta] ?? $mapaAlertas['NORMAL']);
@endphp

<div class="max-w-7xl mx-auto px-4 py-4 space-y-4">

    {{-- ALERTAS DEL SISTEMA --}}
    @if(session('status'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="flex items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm">
        <span class="flex items-center gap-2 text-sm"><flux:icon.check-circle class="size-4" /> {{ session('status') }}</span>
        <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-700">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="flex items-center justify-between gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-red-800 shadow-sm">
        <span class="flex items-center gap-2 text-sm"><flux:icon.exclamation-triangle class="size-4" /> {{ session('error') }}</span>
        <button type="button" @click="show = false" class="text-red-500 hover:text-red-700">&times;</button>
    </div>
    @endif

    {{-- HEADER + SELECTOR --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
            <div>
                <h1 class="text-lg font-bold text-slate-800 mb-0.5">Panel de Control</h1>
                <p class="text-sm text-slate-500">Gestión Forestal Rennova</p>
            </div>
            <div>
                @include('partials.selector-lote', ['lotes' => $lotes ?? collect(), 'loteSeleccionado' => $loteSeleccionado ?? null])
            </div>
        </div>
    </div>

    {{-- ERRORES DE CLIMA --}}
    @if(isset($pronosticoError) && $pronosticoError)
    <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-3 text-amber-800 shadow-sm">
        <flux:icon.exclamation-triangle class="size-4 shrink-0" />
        <span class="text-sm">{{ $pronosticoError }}</span>
    </div>
    @endif

    @if(isset($pronosticoData) && !empty($pronosticoData))
        {{-- KPIs --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            {{-- Días Perdidos --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Días Perdidos</p>
                        <p class="text-3xl font-bold text-slate-900">{{ $pronosticoData['analisisImpacto']['diasPerdidos'] ?? 0 }}</p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-100">
                        <flux:icon.exclamation-circle class="size-5 text-red-500" />
                    </div>
                </div>
                @php $dias = $pronosticoData['analisisImpacto']['diasPerdidos'] ?? 0; @endphp
                <div class="pt-3 border-t border-slate-100">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-400">Últimos 7 días</span>
                        @if($dias > 5)
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">Alto</span>
                        @elseif($dias > 2)
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Moderado</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">Normal</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Déficit TN --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Déficit TN</p>
                        <p class="text-3xl font-bold text-slate-900">{{ $pronosticoData['analisisImpacto']['deficitTn'] ?? 0 }}</p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-amber-100">
                        <flux:icon.chart-bar class="size-5 text-amber-500 rotate-180" />
                    </div>
                </div>
                @if(isset($pronosticoData['analisisImpacto']['accionPorcentaje']) && $pronosticoData['analisisImpacto']['accionPorcentaje'] > 0)
                <div class="pt-3 border-t border-slate-100">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate-400">Aumentar producción</span>
                        <span class="text-sm font-bold text-amber-600">+{{ $pronosticoData['analisisImpacto']['accionPorcentaje'] }}%</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- CAMINO DE RECOMENDACIÓN --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-2 mb-3">
                <h2 class="text-sm font-bold text-slate-800">Camino recomendado</h2>
                <span class="inline-flex items-center gap-1 rounded-full {{ $alertaInfo['colorClass'] }} px-2.5 py-0.5 text-xs font-semibold">
                    <flux:icon.{{ $alertaInfo['icon'] }} class="size-3" />
                    {{ $alertaInfo['label'] }}
                </span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="rounded-xl bg-{{ $accionRecomendada === 'SUSPENSION_JORNADA' ? 'red' : ($accionRecomendada === 'AUMENTAR_PRODUCCION' ? 'amber' : ($accionRecomendada === 'MANTENIMIENTO_PREVENTIVO' ? 'blue' : 'green')) }}-50 p-3">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Estrategia</p>
                    <p class="text-base font-bold text-slate-800">{{ $alertaInfo['label'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">Basado en clima y operatividad del lote seleccionado.</p>
                </div>
                <div class="md:col-span-2 rounded-xl bg-slate-50 p-3">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Detalle de recomendación</p>
                    <p class="text-xs text-slate-500 whitespace-pre-wrap">{{ $recomendacionDetallada ?: 'Sin detalle disponible.' }}</p>
                </div>
            </div>
        </div>

        {{-- PRONÓSTICO DE OPERATIVIDAD --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-sm font-bold text-slate-800">Pronóstico de Operatividad</h2>
                <span class="text-xs text-slate-400">Próximos {{ count($pronosticoData['pronostico'] ?? []) }} días</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-3">
                @foreach(($pronosticoData['pronostico'] ?? []) as $dia)
                    @php
                        $esOperativo = strtoupper($dia['estado'] ?? 'OPERATIVO') === 'OPERATIVO';
                        $esFinDeSemana = isset($dia['suelo']) && stripos($dia['suelo'], 'fin de semana') !== false;
                        $labelParts = explode('(', $dia['label'] ?? 'Día');
                        $diaNombre = trim($labelParts[0] ?? 'Día');
                        $fecha = isset($labelParts[1]) ? trim(str_replace(')', '', $labelParts[1])) : '';
                        $textoEstado = $esFinDeSemana ? 'No laboral' : ($esOperativo ? 'Operativo' : ($dia['suelo'] ?? 'Inactivo'));
                    @endphp
                    <div class="text-center p-2">
                        <p class="text-xs font-semibold text-slate-700">{{ $diaNombre }}</p>
                        <p class="text-xs text-slate-400 mb-2">{{ $fecha }}</p>
                        @if($esFinDeSemana)
                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center mx-auto mb-2">
                                <flux:icon.calendar class="size-5 text-slate-400" />
                            </div>
                            <div class="h-0.5 bg-slate-300 rounded mb-1"></div>
                            <p class="text-xs font-medium text-slate-400">{{ $textoEstado }}</p>
                        @elseif($esOperativo)
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mx-auto mb-2">
                                <flux:icon.sun class="size-5 text-green-500" />
                            </div>
                            <div class="h-0.5 bg-green-300 rounded mb-1"></div>
                            <p class="text-xs font-medium text-green-600">{{ $textoEstado }}</p>
                        @else
                            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center mx-auto mb-2">
                                <flux:icon.cloud class="size-5 text-red-500" />
                            </div>
                            <div class="h-0.5 bg-red-300 rounded mb-1"></div>
                            <p class="text-xs font-medium text-red-600">{{ $textoEstado }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ACCESOS DIRECTOS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-3">
        {{-- Maquinaria --}}
        <a href="{{ route('modulos.maquinaria') }}" class="block bg-white rounded-xl shadow-sm border border-slate-200 p-3 text-center hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mx-auto mb-2">
                <flux:icon.truck class="size-5 text-blue-500" />
            </div>
            <h5 class="text-sm font-bold text-slate-800 mb-0.5">Maquinaria</h5>
            <p class="text-xs text-slate-400">Gestión de equipos</p>
        </a>

        {{-- Inventario --}}
        <a href="{{ route('modulos.inventario-forestal') }}" class="block bg-white rounded-xl shadow-sm border border-slate-200 p-3 text-center hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center mx-auto mb-2" style="background: rgba(139, 92, 246, 0.1);">
                <flux:icon.cube class="size-5" style="color: #8B5CF6;" />
            </div>
            <h5 class="text-sm font-bold text-slate-800 mb-0.5">Inventario</h5>
            <p class="text-xs text-slate-400">Control de stock</p>
        </a>

        {{-- Personal --}}
        <a href="{{ route('modulos.personal') }}" class="block bg-white rounded-xl shadow-sm border border-slate-200 p-3 text-center hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center mx-auto mb-2" style="background: rgba(249, 115, 22, 0.1);">
                <flux:icon.users class="size-5" style="color: #F97316;" />
            </div>
            <h5 class="text-sm font-bold text-slate-800 mb-0.5">Personal</h5>
            <p class="text-xs text-slate-400">Gestión de empleados</p>
        </a>

        {{-- Registrar Operaciones - CTA --}}
        <a href="{{ route('modulos.operaciones') }}" class="block relative overflow-hidden rounded-xl shadow-sm border border-brand p-3 text-center hover:shadow-md hover:-translate-y-0.5 transition-all" style="background: linear-gradient(135deg, #2d7a4f 0%, #1e5631 100%);">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center mx-auto mb-2" style="background: rgba(255,255,255,0.2);">
                <flux:icon.plus class="size-5 text-white" />
            </div>
            <h5 class="text-sm font-bold text-white mb-0.5">Registrar Operaciones</h5>
            <p class="text-xs text-white/60">Nueva operación</p>
            <span class="absolute top-2 right-2 text-xs text-white/40">★</span>
        </a>

        {{-- Reporte de Lluvias - PDF --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3">
            <div class="text-center mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center mx-auto mb-2" style="background: rgba(59, 130, 246, 0.12);">
                    <flux:icon.cloud class="size-5" style="color: #3B82F6;" />
                </div>
                <h5 class="text-sm font-bold text-slate-800 mb-0.5">Reporte de Lluvias</h5>
                <p class="text-xs text-slate-400">Exportar PDF</p>
            </div>
            <form action="{{ route('reportes.clima-lluvias.pdf') }}" method="GET" class="space-y-2">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-0.5">Lote</label>
                    <select name="id_lote" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-800 shadow-sm focus:border-brand focus:ring-1 focus:ring-brand">
                        <option value="">Todos</option>
                        @foreach($lotes as $lote)
                            <option value="{{ $lote->id_lote }}">{{ $lote->propietario }} - {{ $lote->ubicacion }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-0.5">Desde</label>
                    <input type="date" name="desde" max="{{ \Carbon\Carbon::now()->toDateString() }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-800 shadow-sm focus:border-brand focus:ring-1 focus:ring-brand"
                        value="{{ \Carbon\Carbon::now()->subDays(30)->toDateString() }}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-0.5">Hasta</label>
                    <input type="date" name="hasta" max="{{ \Carbon\Carbon::now()->toDateString() }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-800 shadow-sm focus:border-brand focus:ring-1 focus:ring-brand"
                        value="{{ \Carbon\Carbon::now()->toDateString() }}">
                </div>
                <button type="submit"
                    class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                    Descargar PDF
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
