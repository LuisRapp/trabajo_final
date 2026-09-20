@extends('layouts.app')

@section('content')
<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2 mb-1">
            <flux:icon.clipboard-document-check class="size-6 text-pino" />
            Operaciones Diarias
        </h1>
        <p class="text-tinta-suave">Gestioná proveedores, insumos y partes diarios de trabajo</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <!-- Proveedores -->
        @can('ver-proveedores')
        <x-ui.card class="p-5">
            <div class="flex items-center gap-4 mb-3">
                <div class="rounded-full bg-pino-suave p-3 shrink-0">
                    <flux:icon.truck class="size-7 text-pino" />
                </div>
                <h3 class="text-lg font-bold text-pino">Proveedores</h3>
            </div>
            <p class="text-sm text-tinta-suave mb-4">
                Administración de proveedores y gestión de compras.
            </p>
            <a href="{{ route('proveedores.index') }}"
                class="btn-primary w-full">
                Gestionar
            </a>
        </x-ui.card>
        @endcan

        <!-- Insumos -->
        @can('ver-insumos')
        <x-ui.card class="p-5">
            <div class="flex items-center gap-4 mb-3">
                <div class="rounded-full bg-pino-suave p-3 shrink-0">
                    <flux:icon.cube class="size-7 text-pino" />
                </div>
                <h3 class="text-lg font-bold text-pino">Insumos</h3>
            </div>
            <p class="text-sm text-tinta-suave mb-4">
                Control de inventario de insumos y materiales.
            </p>
            <a href="{{ route('insumos.index') }}"
                class="btn-primary w-full">
                Gestionar
            </a>
        </x-ui.card>
        @endcan

        <!-- Control de Stock -->
        @can('ver-gestion-stock')
        <x-ui.card class="p-5">
            <div class="flex items-center gap-4 mb-3">
                <div class="rounded-full bg-pino-suave p-3 shrink-0">
                    <flux:icon.square-3-stack-3d class="size-7 text-pino" />
                </div>
                <h3 class="text-lg font-bold text-pino">Control de Stock</h3>
            </div>
            <p class="text-sm text-tinta-suave mb-4">
                Gestión de stock de insumos con trazabilidad de lotes y costos.
            </p>
            <a href="{{ route('modulos.operaciones.gestionstock') }}"
                class="btn-primary w-full">
                Abrir
            </a>
        </x-ui.card>
        @endcan

        <!-- Partes Diarios -->
        @can('ver-partes-diarios')
        <x-ui.card class="p-5">
            <div class="flex items-center gap-4 mb-3">
                <div class="rounded-full bg-pino-suave p-3 shrink-0">
                    <flux:icon.clipboard-document-check class="size-7 text-pino" />
                </div>
                <h3 class="text-lg font-bold text-pino">Partes Diarios</h3>
            </div>
            <p class="text-sm text-tinta-suave mb-4">
                Registro y control de partes diarios de trabajo.
            </p>
            <a href="{{ route('partes-diarios.index') }}"
                class="btn-primary w-full">
                Gestionar
            </a>
        </x-ui.card>
        @endcan

        <!-- Unidades de Medida -->
        @can('ver-unidades-medida')
        <x-ui.card class="p-5">
            <div class="flex items-center gap-4 mb-3">
                <div class="rounded-full bg-pino-suave p-3 shrink-0">
                    <flux:icon.scale class="size-7 text-pino" />
                </div>
                <h3 class="text-lg font-bold text-pino">Unidades de Medida</h3>
            </div>
            <p class="text-sm text-tinta-suave mb-4">
                Configuración de unidades de medida del sistema.
            </p>
            <a href="{{ route('unidades-medida.index') }}"
                class="btn-primary w-full">
                Gestionar
            </a>
        </x-ui.card>
        @endcan
    </div>
</div>
@endsection
