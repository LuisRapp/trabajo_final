@extends('layouts.app')

@section('content')
<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.queue-list class="size-6 text-pino" />
            Estado de procesos
        </h1>
        <p class="mt-1 text-sm text-tinta-suave">
            Monitoreo de los procesos automatizados: qué hace cada uno, cómo se dispara y en qué estado están.
        </p>
    </div>

    @livewire('estado-procesos')
</div>
@endsection
