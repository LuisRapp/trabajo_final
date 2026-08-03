@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-slate-700 flex items-center justify-center gap-2 mb-2">
            <flux:icon.users class="size-6" />
            Gestión de Personal
        </h1>
        <p class="text-slate-500">Administra empleados, choferes, pagos y asignaciones</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <!-- Empleados -->
        @can('ver-empleados')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.user class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Empleados</h3>
            <a href="{{ route('empleados.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Choferes -->
        @can('ver-choferes')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.identification class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Choferes</h3>
            <a href="{{ route('choferes.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Adelantos -->
        @can('ver-adelantos')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.banknotes class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Adelantos</h3>
            <a href="{{ route('adelantos.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Recibos -->
        @can('ver-recibos')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.document-text class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Recibos</h3>
            <a href="{{ route('recibos.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Liquidación -->
        @can('ver-liquidacion-pagos')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.calculator class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Liquidación de Pagos</h3>
            <a href="{{ route('liquidacion-pagos.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Asignaciones -->
        @can('ver-asignaciones-lote')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.link class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Asignaciones por Lote</h3>
            <a href="{{ route('asignaciones-lote.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Roles -->
        @can('ver-roles-laborales')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.shield-check class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Roles Laborales</h3>
            <a href="{{ route('roles-laborales.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Histórico Roles -->
        @can('ver-roles-laborales')
        <div class="bg-white rounded-xl border border-slate-200 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
            <div class="text-brand mb-4">
                <flux:icon.clock class="size-12" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-4">Histórico Roles</h3>
            <a href="{{ route('historico-roles-laborales.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Ver Histórico
            </a>
        </div>
        @endcan
    </div>
</div>
@endsection
