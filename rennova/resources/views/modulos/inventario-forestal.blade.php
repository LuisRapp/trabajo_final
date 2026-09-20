@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2 mb-2">
            <flux:icon.map-pin class="size-6 text-pino" />
            Inventario Forestal
        </h1>
        <p class="text-tinta-suave text-sm">Gestiona lotes, clientes, ventas y productos forestales</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @can('ver-lotes')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.map-pin class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Lotes</h3>
                <a href="{{ route('lotes.index') }}" class="btn-primary w-full">
                    Gestionar
                </a>
            </x-ui.card>
        @endcan

        @can('ver-clientes')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.users class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Clientes</h3>
                <a href="{{ route('clientes.index') }}" class="btn-primary w-full">
                    Gestionar
                </a>
            </x-ui.card>
        @endcan

        @can('ver-ventas')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.document-text class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Ventas</h3>
                <a href="{{ route('ventas.index') }}" class="btn-primary w-full">
                    Gestionar
                </a>
            </x-ui.card>
        @endcan

        @can('ver-cargas')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.cube class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Cargas</h3>
                <a href="{{ route('cargas.index') }}" class="btn-primary w-full">
                    Gestionar
                </a>
            </x-ui.card>
        @endcan

        @can('ver-categorias-madera')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.tag class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Categorias de Madera</h3>
                <a href="{{ route('categorias-madera.index') }}" class="btn-primary w-full">
                    Gestionar
                </a>
            </x-ui.card>
        @endcan

        @can('ver-lista-precios')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.currency-dollar class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Lista de Precios</h3>
                <a href="{{ route('lista-precios.index') }}" class="btn-primary w-full">
                    Gestionar
                </a>
            </x-ui.card>
        @endcan
    </div>
</div>
@endsection
