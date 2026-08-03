@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2 mb-1">
            <flux:icon.shield-exclamation class="size-6 text-green-700" />
            Administración
        </h1>
        <p class="text-slate-500">Gestión de usuarios, roles, permisos y configuraciones del sistema</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <!-- Usuarios -->
        @can('gestionar-usuarios')
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4 mb-3">
                <div class="rounded-full bg-green-100 p-3 shrink-0">
                    <flux:icon.users class="size-7 text-green-700" />
                </div>
                <h3 class="text-lg font-bold text-green-700">Usuarios</h3>
            </div>
            <p class="text-sm text-slate-500 mb-4">
                Gestión de cuentas de usuario y accesos al sistema.
            </p>
            <a href="{{ route('usuarios.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Roles y Permisos -->
        @can('gestionar-permisos')
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4 mb-3">
                <div class="rounded-full bg-green-100 p-3 shrink-0">
                    <flux:icon.shield-check class="size-7 text-green-700" />
                </div>
                <h3 class="text-lg font-bold text-green-700">Roles y Permisos</h3>
            </div>
            <p class="text-sm text-slate-500 mb-4">
                Configuración de roles y asignación de permisos.
            </p>
            <a href="{{ route('roles-permisos.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Gestionar
            </a>
        </div>
        @endcan

        <!-- Auditorías -->
        @can('ver-auditoria')
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4 mb-3">
                <div class="rounded-full bg-green-100 p-3 shrink-0">
                    <flux:icon.document-text class="size-7 text-green-700" />
                </div>
                <h3 class="text-lg font-bold text-green-700">Auditorías</h3>
            </div>
            <p class="text-sm text-slate-500 mb-4">
                Registro y consulta de actividades del sistema.
            </p>
            <a href="{{ route('auditorias.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Ver Historial
            </a>
        </div>
        @endcan

        <!-- Configuración de Mantenimiento -->
        @can('configurar-mantenimiento')
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4 mb-3">
                <div class="rounded-full bg-green-100 p-3 shrink-0">
                    <flux:icon.calendar-date-range class="size-7 text-green-700" />
                </div>
                <h3 class="text-lg font-bold text-green-700">Configuración Mantenimiento</h3>
            </div>
            <p class="text-sm text-slate-500 mb-4">
                Ajustes y parámetros para el módulo de mantenimiento.
            </p>
            <a href="{{ route('configuracion-mantenimiento.index') }}"
                class="inline-flex items-center justify-center w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                Configurar
            </a>
        </div>
        @endcan
    </div>
</div>
@endsection
