<div class="relative" x-data="{ open: false }" @click.outside="open = false" id="notificaciones-dropdown">
    <a class="relative cursor-pointer inline-flex items-center gap-1.5 px-3 py-2 text-white/80 hover:text-white transition-colors" @click="open = !open" id="notificaciones-toggle">
        <flux:icon.bell class="size-5" />
        @if($cantidadNoLeidas > 0)
            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[0.65rem] font-bold bg-tierra text-white leading-none">
                {{ $cantidadNoLeidas > 9 ? '9+' : $cantidadNoLeidas }}
            </span>
        @endif
    </a>

    <div x-show="open" x-transition
        class="absolute right-0 mt-2 w-[380px] max-h-[500px] overflow-y-auto bg-white rounded-sm shadow-lg border border-arena z-50">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-arena">
            <div class="flex justify-between items-center">
                <h6 class="font-bold text-tinta text-sm">Notificaciones</h6>
                <div class="flex gap-2">
                    @if($cantidadNoLeidas > 0)
                        <button
                            wire:click="marcarTodasComoLeidas"
                            class="text-pino text-xs hover:underline p-0 bg-transparent border-0 cursor-pointer"
                            type="button"
                        >
                            Marcar todas como leídas
                        </button>
                    @endif
                    <a href="{{ route('notificaciones.index') }}" class="text-tinta-suave text-xs hover:underline">
                        Historial
                    </a>
                </div>
            </div>
        </div>

        <!-- Lista de notificaciones -->
        @if($notificaciones->count() > 0)
            @foreach($notificaciones as $notificacion)
                <div wire:key="notif-{{ $notificacion->id }}">
                    <div
                        wire:click="irANotificacion({{ $notificacion->id }})"
                        onclick="event.stopPropagation()"
                        class="px-4 py-3 border-b border-arena cursor-pointer {{ $notificacion->leida ? 'bg-white' : 'bg-pino-suave' }} hover:bg-corteza-suave transition-colors"
                    >
                        <div class="flex items-start">
                            <!-- Icono según tipo -->
                            <div class="mr-2 mt-1 shrink-0">
                                @if($notificacion->tipo === 'umbral_alcanzado')
                                    <flux:icon.exclamation-triangle class="size-4 text-resina" />
                                @elseif($notificacion->tipo === 'stock_insuficiente')
                                    <flux:icon.cube class="size-4 text-tierra" />
                                @elseif($notificacion->tipo === 'recordatorio_programado')
                                    <flux:icon.calendar class="size-4 text-pino" />
                                @else
                                    <flux:icon.bell class="size-4 text-tinta-suave" />
                                @endif
                            </div>

                            <!-- Contenido -->
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-sm text-tinta">{{ $notificacion->titulo }}</div>
                                <div class="text-tinta-suave text-xs mt-1">
                                    {{ Str::limit($notificacion->mensaje, 100) }}
                                </div>

                                <!-- Info de fecha limite y dias restantes -->
                                @if($notificacion->fecha_limite)
                                    @php
                                        $diasRestantes = $notificacion->diasRestantes();
                                    @endphp
                                    <div class="mt-1">
                                        @if($diasRestantes !== null)
                                            @if($diasRestantes >= 0)
                                                <x-ui.badge variant="warning">
                                                    <flux:icon.clock class="size-3 mr-1" /> {{ $diasRestantes }} día(s) restante(s)
                                                </x-ui.badge>
                                            @else
                                                <x-ui.badge variant="danger">
                                                    <flux:icon.x-mark class="size-3 mr-1" /> Vencida
                                                </x-ui.badge>
                                            @endif
                                        @endif
                                    </div>
                                @endif

                                <div class="text-arena-oscura text-xs mt-1">
                                    {{ $notificacion->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Ver todas -->
            <div class="text-center py-3">
                <a href="{{ route('notificaciones.index') }}" class="text-pino text-sm hover:underline">
                    Ver todas las notificaciones
                </a>
            </div>
        @else
            <div class="text-center py-8 text-tinta-suave">
                <flux:icon.inbox class="size-8 mx-auto mb-2 text-arena-oscura" />
                <p class="text-sm">No tienes notificaciones nuevas</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal de Programación de Mantenimiento -->
@if($mostrarModalProgramacion && $notificacionSeleccionada && $mantenimientoSeleccionado)
<div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center" wire:click.self="cerrarModalProgramacion">
    <div class="bg-white rounded-sm shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto relative">
        <div class="bg-brand text-white px-6 py-4 rounded-t-sm">
            <h5 class="text-lg font-semibold flex items-center gap-2">
                <flux:icon.calendar class="size-5" />
                Programar Mantenimiento
            </h5>
            <button type="button" class="absolute top-4 right-4 text-white/80 hover:text-white bg-transparent border-0 cursor-pointer" wire:click="cerrarModalProgramacion">
                <flux:icon.x-mark class="size-5" />
            </button>
        </div>
        <div class="p-6">
            <!-- Información de la notificación -->
            <x-ui.alert variant="info" class="mb-4">
                <div>
                    <strong class="text-sm">{{ $notificacionSeleccionada->titulo }}</strong>
                    <p class="mt-1 text-xs">{{ $notificacionSeleccionada->mensaje }}</p>
                </div>
            </x-ui.alert>

            <!-- Información del mantenimiento -->
            <x-ui.card class="overflow-hidden mb-4">
                <div class="p-6">
                    <h6 class="text-pino font-semibold mb-3 flex items-center gap-2 text-sm">
                        <flux:icon.wrench class="size-4" />
                        Detalles del Mantenimiento
                    </h6>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="col-span-2">
                            <small class="text-tinta-suave text-xs">Maquinaria:</small>
                            <div class="font-semibold text-sm text-tinta">{{ $mantenimientoSeleccionado->maquinaria->nombre ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <small class="text-tinta-suave text-xs">Tipo:</small>
                            <div class="font-semibold text-sm text-tinta">{{ $mantenimientoSeleccionado->tipoMantenimiento->nombre ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <small class="text-tinta-suave text-xs">Estado:</small>
                            <div>
                                @if($mantenimientoSeleccionado->estado === 'pendiente')
                                    <x-ui.badge variant="warning">Pendiente</x-ui.badge>
                                @elseif($mantenimientoSeleccionado->estado === 'programado')
                                    <x-ui.badge variant="info">Programado</x-ui.badge>
                                @elseif($mantenimientoSeleccionado->estado === 'completado')
                                    <x-ui.badge variant="success">Completado</x-ui.badge>
                                @else
                                    <x-ui.badge variant="neutral">{{ ucfirst($mantenimientoSeleccionado->estado) }}</x-ui.badge>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <!-- Formulario de programación -->
            <form wire:submit.prevent="programarMantenimiento">
                <div class="mb-4">
                    <label for="fechaProgramada" class="block text-sm font-semibold text-tinta mb-1.5">
                        Fecha Programada
                    </label>
                    <input
                        type="date"
                        class="form-input @error('fechaProgramada') border-tierra bg-tierra-suave @enderror"
                        id="fechaProgramada"
                        wire:model="fechaProgramada"
                        min="{{ $fechaMinima }}"
                        max="{{ $fechaMaxima }}"
                    >
                    @error('fechaProgramada')
                        <p class="text-tierra text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <small class="text-tinta-suave text-xs mt-1 block">
                        Debes programar el mantenimiento entre el
                        <strong>{{ \Carbon\Carbon::parse($fechaMinima)->format('d/m/Y') }}</strong> y el
                        <strong>{{ \Carbon\Carbon::parse($fechaMaxima)->format('d/m/Y') }}</strong>
                        (máximo 7 días desde la notificación).
                    </small>
                </div>

                <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-arena">
                    <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="cerrarModalProgramacion">
                        Cancelar
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" icon="check">
                        Confirmar Programación
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
