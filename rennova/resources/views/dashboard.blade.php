@extends('layouts.app')

@section('content')
    <div class="w-full py-6 px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
                <flux:icon.squares-2x2 class="size-6 text-pino" />
                Panel de Control
            </h1>
            <p class="mt-1 text-sm text-tinta-suave">Bienvenido/a, {{ auth()->user()->name }}</p>
        </div>

        {{-- Pronóstico del tiempo por lote --}}
        <livewire:selector-lote />

        {{-- Proyección mensual vs punto de equilibrio --}}
        @if(!empty($proyeccion))
            <x-ui.card class="p-5 mb-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium text-tinta-suave">Proyección del mes (lun-vie)</p>
                        <p class="mt-1 text-2xl font-bold text-tinta">
                            {{ number_format($proyeccion['proyeccion_mensual_tn'], 1) }}
                            <span class="text-sm font-normal text-tinta-suave">/ {{ number_format($proyeccion['objetivo_mensual_tn'], 1) }} TN objetivo</span>
                        </p>
                    </div>
                    @if($proyeccion['cumple_objetivo'])
                        <x-ui.badge variant="success">Vas bien: alcanzás el objetivo</x-ui.badge>
                    @else
                        <x-ui.badge variant="danger">Vas corto {{ number_format($proyeccion['deficit_tn'], 1) }} TN este mes</x-ui.badge>
                    @endif
                </div>
                <p class="text-xs text-tinta-suave mt-2">Calculado sobre {{ $proyeccion['dias_habiles_mes'] }} días hábiles (lunes a viernes).</p>
            </x-ui.card>
        @endif

        {{-- Alerta si no hay lotes activos --}}
        @if(!$hayLotesActivos)
            <x-ui.alert variant="warning" class="mb-6">
                <div class="flex flex-col gap-3">
                    <div>
                        <h3 class="text-sm font-semibold">No hay lotes activos</h3>
                        <p class="text-xs mt-1">Creá un lote para comenzar a gestionar operaciones forestales.</p>
                    </div>
                    <x-ui.button href="{{ route('lotes.index') }}" icon="plus" size="sm" class="w-fit">
                        Crear lote
                    </x-ui.button>
                </div>
            </x-ui.alert>
        @endif

        {{-- Métricas --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            {{-- Toneladas Extraídas (Mes) --}}
            <x-ui.card class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="p-2.5 rounded-sm bg-musgo-suave text-musgo">
                        <flux:icon.scale class="size-5" />
                    </div>
                </div>
                <p class="text-xs font-medium text-tinta-suave">Toneladas Extraídas (Mes)</p>
                <p class="mt-1 text-2xl font-bold text-tinta">{{ number_format($toneladasExtraidas, 1) }} <span class="text-sm font-normal text-tinta-suave">TN</span></p>
            </x-ui.card>

            {{-- Cargas del Mes --}}
            <x-ui.card class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="p-2.5 rounded-sm bg-pino-suave text-pino">
                        <flux:icon.truck class="size-5" />
                    </div>
                </div>
                <p class="text-xs font-medium text-tinta-suave">Cargas del Mes</p>
                <p class="mt-1 text-2xl font-bold text-tinta">{{ number_format($cargasMes) }}</p>
            </x-ui.card>

            {{-- Días Operativos (Mes) --}}
            <x-ui.card class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="p-2.5 rounded-sm bg-resina-suave text-resina">
                        <flux:icon.calendar class="size-5" />
                    </div>
                </div>
                <p class="text-xs font-medium text-tinta-suave">Días Operativos (Mes)</p>
                <p class="mt-1 text-2xl font-bold text-tinta">{{ number_format($diasOperativos) }}</p>
            </x-ui.card>

            {{-- Costo Promedio por TN --}}
            <x-ui.card class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="p-2.5 rounded-sm bg-tierra-suave text-tierra">
                        <flux:icon.banknotes class="size-5" />
                    </div>
                </div>
                <p class="text-xs font-medium text-tinta-suave">Costo Prom. por TN (Mes)</p>
                <p class="mt-1 text-2xl font-bold text-tinta">${{ number_format($costoPromedioTn, 2) }}</p>
            </x-ui.card>

            {{-- Precio Venta Promedio por TN --}}
            <x-ui.card class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="p-2.5 rounded-sm bg-musgo-suave text-musgo">
                        <flux:icon.tag class="size-5" />
                    </div>
                </div>
                <p class="text-xs font-medium text-tinta-suave">Precio Venta Prom. por TN (Mes)</p>
                <p class="mt-1 text-2xl font-bold text-tinta">${{ number_format($precioVentaPromedioTn, 2) }}</p>
            </x-ui.card>

            {{-- Mantenimientos Pendientes --}}
            <x-ui.card class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="p-2.5 rounded-sm bg-resina-suave text-resina">
                        <flux:icon.wrench class="size-5" />
                    </div>
                </div>
                <p class="text-xs font-medium text-tinta-suave">Mantenimientos Pendientes</p>
                <p class="mt-1 text-2xl font-bold text-tinta">{{ number_format($mantenimientosPendientes) }}</p>
            </x-ui.card>
        </div>

        {{-- Accesos Rápidos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.card class="p-5 hover:bg-corteza-suave transition-colors">
                <a href="{{ route('maquinarias.index') }}" class="flex items-center gap-4 no-underline text-tinta">
                    <div class="p-2.5 rounded-sm bg-pino-suave text-pino">
                        <flux:icon.truck class="size-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm">Gestión de Maquinaria</h3>
                        <p class="text-xs text-tinta-suave">Administrar equipos</p>
                    </div>
                </a>
            </x-ui.card>

            <x-ui.card class="p-5 hover:bg-corteza-suave transition-colors">
                <a href="{{ route('insumos.index') }}" class="flex items-center gap-4 no-underline text-tinta">
                    <div class="p-2.5 rounded-sm bg-resina-suave text-resina">
                        <flux:icon.cube class="size-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm">Control de Inventario</h3>
                        <p class="text-xs text-tinta-suave">Gestionar insumos</p>
                    </div>
                </a>
            </x-ui.card>

            <x-ui.card class="p-5 hover:bg-corteza-suave transition-colors">
                <a href="{{ route('partes-diarios.index') }}" class="flex items-center gap-4 no-underline text-tinta">
                    <div class="p-2.5 rounded-sm bg-musgo-suave text-musgo">
                        <flux:icon.clipboard-document-list class="size-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm">Partes Diarios</h3>
                        <p class="text-xs text-tinta-suave">Registrar operaciones</p>
                    </div>
                </a>
            </x-ui.card>

            <x-ui.card class="p-5 hover:bg-corteza-suave transition-colors">
                <a href="{{ route('liquidacion-pagos.index') }}" class="flex items-center gap-4 no-underline text-tinta">
                    <div class="p-2.5 rounded-sm bg-tierra-suave text-tierra">
                        <flux:icon.calculator class="size-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm">Liquidaciones</h3>
                        <p class="text-xs text-tinta-suave">Gestionar pagos</p>
                    </div>
                </a>
            </x-ui.card>
        </div>
    </div>
@endsection
