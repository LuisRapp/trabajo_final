@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2 mb-2">
            <flux:icon.shield-exclamation class="size-6 text-pino" />
            Administracion
        </h1>
        <p class="text-tinta-suave text-sm">Gestion de usuarios, roles, permisos y configuraciones del sistema</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @can('gestionar-usuarios')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.users class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Usuarios</h3>
                <a href="{{ route('usuarios.index') }}" class="btn-primary w-full">
                    Gestionar
                </a>
            </x-ui.card>
        @endcan

        @can('gestionar-permisos')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.shield-check class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Roles y Permisos</h3>
                <a href="{{ route('roles-permisos.index') }}" class="btn-primary w-full">
                    Gestionar
                </a>
            </x-ui.card>
        @endcan

        @can('ver-auditoria')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.document-text class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Auditorias</h3>
                <a href="{{ route('auditorias.index') }}" class="btn-primary w-full">
                    Ver Historial
                </a>
            </x-ui.card>
        @endcan

        @can('configurar-mantenimiento')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.calendar-date-range class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Configuracion Mantenimiento</h3>
                <a href="{{ route('configuracion-mantenimiento.index') }}" class="btn-primary w-full">
                    Configurar
                </a>
            </x-ui.card>
        @endcan

        @can('ver-categorias-madera')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.tag class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Categorías de madera</h3>
                <a href="{{ route('categorias-madera.index') }}" class="btn-primary w-full">
                    Gestionar
                </a>
            </x-ui.card>
        @endcan

        @can('ver-unidades-medida')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.scale class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Unidades de medida</h3>
                <a href="{{ route('unidades-medida.index') }}" class="btn-primary w-full">
                    Gestionar
                </a>
            </x-ui.card>
        @endcan

        @can('ver-lista-precios')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.currency-dollar class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Lista de precios</h3>
                <a href="{{ route('lista-precios.index') }}" class="btn-primary w-full">
                    Gestionar
                </a>
            </x-ui.card>
        @endcan

        @can('ver-tipos-maquinaria')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.cog-6-tooth class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Tipos de maquinaria</h3>
                <a href="{{ route('tipos-maquinaria.index') }}" class="btn-primary w-full">
                    Gestionar
                </a>
            </x-ui.card>
        @endcan

        @can('ver-roles-laborales')
            <x-ui.card class="p-6 text-center">
                <div class="text-pino mb-4 flex justify-center">
                    <flux:icon.briefcase class="size-12" />
                </div>
                <h3 class="text-base font-bold text-tinta mb-4">Roles laborales</h3>
                <a href="{{ route('roles-laborales.index') }}" class="btn-primary w-full">
                    Gestionar
                </a>
            </x-ui.card>
        @endcan
    </div>
</div>
@endsection
