@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <div>
            <h4 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <flux:icon.sparkles class="size-5" />
                Recomendaciones automáticas
            </h4>
            <p class="text-sm text-slate-500 mt-1">Lote #{{ $loteId }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('lotes.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                <flux:icon.arrow-left class="size-4" />
                Volver a Lotes
            </a>
            <a href="{{ route('lotes.tareas', ['loteId' => $loteId]) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                <flux:icon.clipboard-document-list class="size-4" />
                Planificar tareas
            </a>
        </div>
    </div>

    @livewire('allocation-proposals', ['loteId' => $loteId])
</div>
@endsection
