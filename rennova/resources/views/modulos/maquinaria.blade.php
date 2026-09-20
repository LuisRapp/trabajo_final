@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    <div class="mb-8">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.truck class="size-6" />
            Gestion de Maquinaria
        </h1>
        <p class="text-tinta-suave mt-1">Administra tu flota de maquinaria, mantenimientos y costos operativos</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @can('ver-maquinarias')
        <x-ui.card class="p-6 text-center">
            <div class="text-pino mb-4 flex justify-center">
                <flux:icon.truck class="size-12" />
            </div>
            <h3 class="text-base font-bold text-tinta mb-4">Maquinarias</h3>
            <a href="{{ route('maquinarias.index') }}"
                class="btn-primary inline-flex w-full items-center justify-center">
                Gestionar
            </a>
        </x-ui.card>
        @endcan

        @can('ver-mantenimientos')
        <x-ui.card class="p-6 text-center">
            <div class="text-pino mb-4 flex justify-center">
                <flux:icon.wrench class="size-12" />
            </div>
            <h3 class="text-base font-bold text-tinta mb-4">Mantenimientos</h3>
            <a href="{{ route('mantenimientos.index') }}"
                class="btn-primary inline-flex w-full items-center justify-center">
                Gestionar
            </a>
        </x-ui.card>
        @endcan

        @can('ver-kits-mantenimiento')
        <x-ui.card class="p-6 text-center">
            <div class="text-pino mb-4 flex justify-center">
                <flux:icon.cog class="size-12" />
            </div>
            <h3 class="text-base font-bold text-tinta mb-4">Kits de Mantenimiento</h3>
            <a href="{{ route('kits-mantenimiento.index') }}"
                class="btn-primary inline-flex w-full items-center justify-center">
                Gestionar
            </a>
        </x-ui.card>
        @endcan

        @can('ver-historico-costos-maquinarias')
        <x-ui.card class="p-6 text-center">
            <div class="text-pino mb-4 flex justify-center">
                <flux:icon.chart-bar class="size-12" />
            </div>
            <h3 class="text-base font-bold text-tinta mb-4">Costos de Maquinaria</h3>
            <a href="{{ route('historico-costos-maquinarias.index') }}"
                class="btn-primary inline-flex w-full items-center justify-center">
                Ver Historico
            </a>
        </x-ui.card>
        @endcan

        @can('ver-tipos-maquinaria')
        <x-ui.card class="p-6 text-center">
            <div class="text-pino mb-4 flex justify-center">
                <flux:icon.cog-6-tooth class="size-12" />
            </div>
            <h3 class="text-base font-bold text-tinta mb-4">Tipos de Maquinaria</h3>
            <a href="{{ route('tipos-maquinaria.index') }}"
                class="btn-primary inline-flex w-full items-center justify-center">
                Gestionar
            </a>
        </x-ui.card>
        @endcan

        @can('configurar-notificaciones-mantenimiento')
        <x-ui.card class="p-6 text-center">
            <div class="text-pino mb-4 flex justify-center">
                <flux:icon.bell class="size-12" />
            </div>
            <h3 class="text-base font-bold text-tinta mb-4">Configuracion de Notificaciones</h3>
            <a href="{{ route('configuracion-notificaciones.index') }}"
                class="btn-primary inline-flex w-full items-center justify-center">
                Configurar
            </a>
        </x-ui.card>
        @endcan
    </div>
</div>
@endsection
