@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0"><i class="bi bi-magic"></i> Recomendaciones automáticas</h4>
                <div class="text-muted small">Lote #{{ $loteId }}</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('lotes.index') }}">
                    <i class="bi bi-arrow-left"></i> Volver a Lotes
                </a>
                <a class="btn btn-outline-primary" href="{{ route('lotes.tareas', ['loteId' => $loteId]) }}">
                    <i class="bi bi-list-check"></i> Planificar tareas
                </a>
            </div>
        </div>
    </div>

    @livewire('allocation-proposals', ['loteId' => $loteId])
@endsection
