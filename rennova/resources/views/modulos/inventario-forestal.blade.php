@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-slate-700 flex items-center justify-center gap-2 mb-2">
            <flux:icon.map-pin class="size-6" />
            Inventario Forestal
        </h1>
        <p class="text-slate-500">Gestiona lotes, clientes, ventas y productos forestales</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <!-- Lotes -->
        @can('ver-lotes')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.map-pin class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Lotes</h3>
            <a href="{{ route('lotes.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Clientes -->
        @can('ver-clientes')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.users class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Clientes</h3>
            <a href="{{ route('clientes.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Ventas -->
        @can('ver-ventas')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.document class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Ventas</h3>
            <a href="{{ route('ventas.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Cargas -->
        @can('ver-cargas')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.cube class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Cargas</h3>
            <a href="{{ route('cargas.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Categorías -->
        @can('ver-categorias-madera')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.tag class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Categorías de Madera</h3>
            <a href="{{ route('categorias-madera.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Lista de Precios -->
        @can('ver-lista-precios')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.tag class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Lista de Precios</h3>
            <a href="{{ route('lista-precios.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan
    </div>
</div>
@endsection
