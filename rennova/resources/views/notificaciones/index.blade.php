@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <h1 class="text-xl font-bold text-brand mb-4 flex items-center gap-2">
        <flux:icon.bell class="size-5" />
        Mis Notificaciones
    </h1>

    @livewire('notificaciones-sistema')
</div>
@endsection
