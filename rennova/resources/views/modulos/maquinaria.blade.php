@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-slate-700 flex items-center justify-center gap-2 mb-2">
            <flux:icon.truck class="size-6" />
            Gestión de Maquinaria
        </h1>
        <p class="text-slate-500">Administra tu flota de maquinaria, mantenimientos y costos operativos</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <!-- Maquinarias -->
        @can('ver-maquinarias')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.truck class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Maquinarias</h3>
            <a href="{{ route('maquinarias.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Mantenimientos -->
        @can('ver-mantenimientos')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.wrench class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Mantenimientos</h3>
            <a href="{{ route('mantenimientos.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Kits -->
        @can('ver-kits-mantenimiento')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.cog class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Kits de Mantenimiento</h3>
            <a href="{{ route('kits-mantenimiento.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Costos -->
        @can('ver-historico-costos-maquinarias')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.chart-bar class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Costos de Maquinaria</h3>
            <a href="{{ route('historico-costos-maquinarias.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Ver Histórico
            </a>
        </div>
        @endcan

        <!-- Tipos -->
        @can('ver-tipos-maquinaria')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.cog-6-tooth class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Tipos de Maquinaria</h3>
            <a href="{{ route('tipos-maquinaria.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Notificaciones -->
        @can('configurar-notificaciones-mantenimiento')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.bell class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Configuración de Notificaciones</h3>
            <a href="{{ route('configuracion-notificaciones.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Configurar
            </a>
        </div>
        @endcan
    </div>
</div>
@endsection
