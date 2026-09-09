@extends('layouts.app')

@section('title', 'Asignaciones por Lote')

@section('content')
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <div>
            <h4 class="text-lg font-bold text-tinta flex items-center gap-2">
                <flux:icon.link class="size-5" />
                Asignaciones por Lote
            </h4>
            <p class="text-sm text-tinta-suave mt-1">Asignación manual de empleados y maquinarias por lote.</p>
        </div>
        @can('ver-propuestas-asignacion')
        <div class="flex gap-2">
            <a href="{{ route('propuestas-asignacion.index') }}" class="btn-secondary">
                <flux:icon.sparkles class="size-4" />
                Propuestas de asignación
            </a>
        </div>
        @endcan
    </div>

    @livewire('asignaciones-lote')
@endsection