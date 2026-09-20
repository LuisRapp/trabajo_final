@extends('layouts.app')

@section('content')
<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.bell class="size-6 text-pino" />
            Mis Notificaciones
        </h1>
    </div>

    @livewire('notificaciones-sistema')
</div>
@endsection
