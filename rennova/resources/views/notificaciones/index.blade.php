@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-3 fw-bold" style="color: #2A6041;">
                <i class="bi bi-bell-fill"></i> Mis Notificaciones
            </h1>
        </div>
    </div>

    @livewire('notificaciones-sistema')
</div>
@endsection
