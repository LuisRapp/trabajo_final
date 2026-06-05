<li class="relative" x-data="{ open: false }" @click.outside="open = false" id="notificaciones-dropdown">
    <a class="relative cursor-pointer inline-flex items-center gap-1.5 px-3 py-2 text-slate-600 hover:text-slate-800 transition-colors" @click="open = !open" id="notificaciones-toggle">
        <span class="text-xl">🔔</span>
        @if($cantidadNoLeidas > 0)
            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[0.65rem] font-bold bg-red-500 text-white leading-none">
                {{ $cantidadNoLeidas > 9 ? '9+' : $cantidadNoLeidas }}
            </span>
        @endif
    </a>

    <ul x-show="open" x-transition
        class="absolute right-0 mt-2 w-[380px] max-h-[500px] overflow-y-auto bg-white rounded-xl shadow-lg border border-slate-200 z-50">
        <!-- Header -->
        <li class="px-4 py-3 border-b border-slate-200">
            <div class="flex justify-between items-center">
                <h6 class="font-bold text-slate-800">Notificaciones</h6>
                <div class="flex gap-2">
                    @if($cantidadNoLeidas > 0)
                        <button
                            wire:click="marcarTodasComoLeidas"
                            class="text-brand text-sm hover:underline p-0 bg-transparent border-0"
                            type="button"
                        >
                            Marcar todas como leídas
                        </button>
                    @endif
                    <a href="{{ route('notificaciones.index') }}" class="text-slate-500 text-sm hover:underline">
                        📋 Historial
                    </a>
                </div>
            </div>
        </li>

        <!-- Lista de notificaciones -->
        @if($notificaciones->count() > 0)
            @foreach($notificaciones as $notificacion)
                <li wire:key="notif-{{ $notificacion->id }}">
                    <div
                        wire:click="irANotificacion({{ $notificacion->id }})"
                        onclick="event.stopPropagation()"
                        class="px-4 py-3 border-b border-slate-100 cursor-pointer {{ $notificacion->leida ? 'bg-white' : 'bg-slate-50' }} hover:bg-slate-100 transition-colors"
                    >
                        <div class="flex items-start">
                            <!-- Icono según tipo -->
                            <div class="mr-2 mt-1 shrink-0">
                                @if($notificacion->tipo === 'umbral_alcanzado')
                                    <span class="text-lg text-amber-500">⚠</span>
                                @elseif($notificacion->tipo === 'stock_insuficiente')
                                    <span class="text-lg text-red-500">📦</span>
                                @elseif($notificacion->tipo === 'recordatorio_programado')
                                    <span class="text-lg text-cyan-500">📅</span>
                                @else
                                    <span class="text-lg text-slate-400">🔔</span>
                                @endif
                            </div>

                            <!-- Contenido -->
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-sm text-slate-800">{{ $notificacion->titulo }}</div>
                                <div class="text-slate-500 text-xs mt-1">
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
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.7rem] font-medium bg-amber-100 text-amber-700">
                                                    🕐 {{ $diasRestantes }} dia(s) restante(s)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.7rem] font-medium bg-red-100 text-red-700">
                                                    ✖ Vencida
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                @endif

                                <div class="text-slate-400 text-xs mt-1">
                                    {{ $notificacion->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach

            <!-- Ver todas -->
            <li class="text-center py-3">
                <a href="{{ route('notificaciones.index') }}" class="text-brand text-sm hover:underline">
                    Ver todas las notificaciones
                </a>
            </li>
        @else
            <li class="text-center py-8 text-slate-400">
                <div class="text-3xl mb-2">📥</div>
                <p>No tienes notificaciones nuevas</p>
            </li>
        @endif
    </ul>
</li>

<!-- Modal de Programación de Mantenimiento -->
@if($mostrarModalProgramacion && $notificacionSeleccionada && $mantenimientoSeleccionado)
<div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center" wire:click.self="cerrarModalProgramacion">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="bg-brand text-white px-6 py-4 rounded-t-xl">
            <h5 class="text-lg font-semibold">
                📅 Programar Mantenimiento
            </h5>
            <button type="button" class="absolute top-4 right-4 text-white/80 hover:text-white" wire:click="cerrarModalProgramacion">✕</button>
        </div>
        <div class="p-6">
            <!-- Información de la notificación -->
            <div class="flex items-start gap-3 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl px-5 py-3 text-sm mb-4">
                <span class="text-2xl">ℹ️</span>
                <div>
                    <strong>{{ $notificacionSeleccionada->titulo }}</strong>
                    <p class="mt-1 text-xs">{{ $notificacionSeleccionada->mensaje }}</p>
                </div>
            </div>

            <!-- Información del mantenimiento -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
                <div class="p-6">
                    <h6 class="text-brand font-semibold mb-3">
                        🔧 Detalles del Mantenimiento
                    </h6>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="col-span-2">
                            <small class="text-slate-500">Maquinaria:</small>
                            <div class="font-semibold">{{ $mantenimientoSeleccionado->maquinaria->nombre ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <small class="text-slate-500">Tipo:</small>
                            <div class="font-semibold">{{ $mantenimientoSeleccionado->tipoMantenimiento->nombre ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <small class="text-slate-500">Estado:</small>
                            <div>
                                @if($mantenimientoSeleccionado->estado === 'pendiente')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pendiente</span>
                                @elseif($mantenimientoSeleccionado->estado === 'programado')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-700">Programado</span>
                                @elseif($mantenimientoSeleccionado->estado === 'completado')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Completado</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ ucfirst($mantenimientoSeleccionado->estado) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de programación -->
            <form wire:submit.prevent="programarMantenimiento">
                <div class="mb-4">
                    <label for="fechaProgramada" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        📅 Fecha Programada
                    </label>
                    <input
                        type="date"
                        class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fechaProgramada') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                        id="fechaProgramada"
                        wire:model="fechaProgramada"
                        min="{{ $fechaMinima }}"
                        max="{{ $fechaMaxima }}"
                    >
                    @error('fechaProgramada')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <small class="text-slate-500 text-xs mt-1 block">
                        ℹ️
                        Debes programar el mantenimiento entre el
                        <strong>{{ \Carbon\Carbon::parse($fechaMinima)->format('d/m/Y') }}</strong> y el
                        <strong>{{ \Carbon\Carbon::parse($fechaMaxima)->format('d/m/Y') }}</strong>
                        (maximo 7 dias desde la notificacion).
                    </small>
                </div>

                <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-slate-200">
                    <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors" wire:click="cerrarModalProgramacion">
                        ✕ Cancelar
                    </button>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                        ✓ Confirmar Programación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
