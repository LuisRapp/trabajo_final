@extends('layouts.app')

@section('content')
<div class="w-full">
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <div>
            <h4 class="text-lg font-bold text-tinta flex items-center gap-2">
                <flux:icon.sparkles class="size-5" />
                Sugerencias de asignación
            </h4>
            <p class="text-sm text-tinta-suave mt-1">Lote #{{ $loteId }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('lotes.index') }}"
                class="btn-secondary">
                <flux:icon.arrow-left class="size-4" />
                Volver a Lotes
            </a>
            <a href="{{ route('lotes.tareas', ['loteId' => $loteId]) }}"
                class="btn-primary">
                <flux:icon.clipboard-document-list class="size-4" />
                Planificar tareas
            </a>
        </div>
    </div>

    @livewire('allocation-proposals', ['loteId' => $loteId])
</div>
@endsection
