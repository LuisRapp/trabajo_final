@extends('layouts.app')

@section('content')
<div class="w-full">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2 mb-2">
            <flux:icon.users class="size-6" />
            Gestión de Personal
        </h1>
        <p class="text-tinta-suave">Administra empleados, choferes, pagos y asignaciones</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @can('ver-empleados')
        <x-ui.card class="p-6 text-center">
            <div class="text-pino mb-4">
                <flux:icon.user class="size-12 mx-auto" />
            </div>
            <h3 class="text-base font-bold text-tinta mb-4">Empleados</h3>
            <a href="{{ route('empleados.index') }}"
                class="btn-primary w-full">
                Gestionar
            </a>
        </x-ui.card>
        @endcan

        @can('ver-choferes')
        <x-ui.card class="p-6 text-center">
            <div class="text-pino mb-4">
                <flux:icon.identification class="size-12 mx-auto" />
            </div>
            <h3 class="text-base font-bold text-tinta mb-4">Choferes</h3>
            <a href="{{ route('choferes.index') }}"
                class="btn-primary w-full">
                Gestionar
            </a>
        </x-ui.card>
        @endcan

        @can('ver-adelantos')
        <x-ui.card class="p-6 text-center">
            <div class="text-pino mb-4">
                <flux:icon.banknotes class="size-12 mx-auto" />
            </div>
            <h3 class="text-base font-bold text-tinta mb-4">Adelantos</h3>
            <a href="{{ route('adelantos.index') }}"
                class="btn-primary w-full">
                Gestionar
            </a>
        </x-ui.card>
        @endcan

        @can('ver-recibos')
        <x-ui.card class="p-6 text-center">
            <div class="text-pino mb-4">
                <flux:icon.document-text class="size-12 mx-auto" />
            </div>
            <h3 class="text-base font-bold text-tinta mb-4">Recibos</h3>
            <a href="{{ route('recibos.index') }}"
                class="btn-primary w-full">
                Gestionar
            </a>
        </x-ui.card>
        @endcan

        @can('ver-liquidacion-pagos')
        <x-ui.card class="p-6 text-center">
            <div class="text-pino mb-4">
                <flux:icon.calculator class="size-12 mx-auto" />
            </div>
            <h3 class="text-base font-bold text-tinta mb-4">Liquidación de Pagos</h3>
            <a href="{{ route('liquidacion-pagos.index') }}"
                class="btn-primary w-full">
                Gestionar
            </a>
        </x-ui.card>
        @endcan

        @can('ver-asignaciones-lote')
        <x-ui.card class="p-6 text-center">
            <div class="text-pino mb-4">
                <flux:icon.link class="size-12 mx-auto" />
            </div>
            <h3 class="text-base font-bold text-tinta mb-4">Asignaciones por Lote</h3>
            <a href="{{ route('asignaciones-lote.index') }}"
                class="btn-primary w-full">
                Gestionar
            </a>
        </x-ui.card>
        @endcan

        @can('ver-roles-laborales')
        <x-ui.card class="p-6 text-center">
            <div class="text-pino mb-4">
                <flux:icon.shield-check class="size-12 mx-auto" />
            </div>
            <h3 class="text-base font-bold text-tinta mb-4">Roles Laborales</h3>
            <a href="{{ route('roles-laborales.index') }}"
                class="btn-primary w-full">
                Gestionar
            </a>
        </x-ui.card>
        @endcan

        @can('ver-roles-laborales')
        <x-ui.card class="p-6 text-center">
            <div class="text-pino mb-4">
                <flux:icon.clock class="size-12 mx-auto" />
            </div>
            <h3 class="text-base font-bold text-tinta mb-4">Histórico Roles</h3>
            <a href="{{ route('historico-roles-laborales.index') }}"
                class="btn-primary w-full">
                Ver Histórico
            </a>
        </x-ui.card>
        @endcan
    </div>
</div>
@endsection
