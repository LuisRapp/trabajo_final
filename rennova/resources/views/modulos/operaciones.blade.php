@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2 mb-1">
            <flux:icon.clipboard-document-check class="size-6 text-green-700" />
            Operaciones Diarias
        </h1>
        <p class="text-slate-500">Gestiona proveedores, insumos y partes diarios de trabajo</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <!-- Proveedores -->
        @can('ver-proveedores')
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4 mb-3">
                <div class="rounded-full bg-green-100 p-3 shrink-0">
                    <flux:icon.truck class="size-7 text-green-700" />
                </div>
                <h3 class="text-lg font-bold text-green-700">Proveedores</h3>
            </div>
            <p class="text-sm text-slate-500 mb-4">
                Administración de proveedores y gestión de compras.
            </p>
            <a href="{{ route('proveedores.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Insumos -->
        @can('ver-insumos')
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4 mb-3">
                <div class="rounded-full bg-green-100 p-3 shrink-0">
                    <flux:icon.cube class="size-7 text-green-700" />
                </div>
                <h3 class="text-lg font-bold text-green-700">Insumos</h3>
            </div>
            <p class="text-sm text-slate-500 mb-4">
                Control de inventario de insumos y materiales.
            </p>
            <a href="{{ route('insumos.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Gestión Stock (FIFO) -->
        @can('ver-gestion-stock')
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4 mb-3">
                <div class="rounded-full bg-green-100 p-3 shrink-0">
                    <flux:icon.square-3-stack-3d class="size-7 text-green-700" />
                </div>
                <h3 class="text-lg font-bold text-green-700">Gestión Stock (FIFO)</h3>
            </div>
            <p class="text-sm text-slate-500 mb-4">
                Gestión avanzada de stock utilizando el método FIFO.
            </p>
            <a href="{{ route('modulos.operaciones.gestionstock') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Abrir
            </a>
        </div>
        @endcan

        <!-- Partes Diarios -->
        @can('ver-partes-diarios')
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4 mb-3">
                <div class="rounded-full bg-green-100 p-3 shrink-0">
                    <flux:icon.clipboard-document-check class="size-7 text-green-700" />
                </div>
                <h3 class="text-lg font-bold text-green-700">Partes Diarios</h3>
            </div>
            <p class="text-sm text-slate-500 mb-4">
                Registro y control de partes diarios de trabajo.
            </p>
            <a href="{{ route('partes-diarios.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Unidades de Medida -->
        @can('ver-unidades-medida')
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4 mb-3">
                <div class="rounded-full bg-green-100 p-3 shrink-0">
                    <flux:icon.scale class="size-7 text-green-700" />
                </div>
                <h3 class="text-lg font-bold text-green-700">Unidades de Medida</h3>
            </div>
            <p class="text-sm text-slate-500 mb-4">
                Configuración de unidades de medida del sistema.
            </p>
            <a href="{{ route('unidades-medida.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan
    </div>
</div>
@endsection
