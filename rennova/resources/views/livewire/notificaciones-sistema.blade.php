<div class="w-full">
    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-ui.card class="p-5 text-center">
            <div class="text-pino mb-2 flex justify-center">
                <flux:icon.inbox class="size-8" />
            </div>
            <h3 class="text-2xl font-bold text-tinta">{{ $estadisticas['total'] }}</h3>
            <p class="text-tinta-suave text-xs">Total</p>
        </x-ui.card>
        <x-ui.card class="p-5 text-center">
            <div class="text-pino mb-2 flex justify-center">
                <flux:icon.bell-alert class="size-8" />
            </div>
            <h3 class="text-2xl font-bold text-pino">{{ $estadisticas['no_leidas'] }}</h3>
            <p class="text-tinta-suave text-xs">No Leídas</p>
        </x-ui.card>
        <x-ui.card class="p-5 text-center">
            <div class="text-resina mb-2 flex justify-center">
                <flux:icon.clock class="size-8" />
            </div>
            <h3 class="text-2xl font-bold text-resina">{{ $estadisticas['pendientes'] }}</h3>
            <p class="text-tinta-suave text-xs">Pendientes</p>
        </x-ui.card>
        <x-ui.card class="p-5 text-center">
            <div class="text-tierra mb-2 flex justify-center">
                <flux:icon.exclamation-triangle class="size-8" />
            </div>
            <h3 class="text-2xl font-bold text-tierra">{{ $estadisticas['vencidas'] }}</h3>
            <p class="text-tinta-suave text-xs">Vencidas</p>
        </x-ui.card>
    </div>

    <!-- Filtros y acciones -->
    <x-ui.card class="overflow-hidden mb-6">
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-tinta mb-1.5">Tipo de Notificación</label>
                    <select wire:model="filtroTipo"
                        class="form-input">
                        <option value="todas">Todas</option>
                        <option value="umbral_alcanzado">Umbral Alcanzado</option>
                        <option value="stock_insuficiente">Stock Insuficiente</option>
                        <option value="recordatorio_programado">Recordatorio Programado</option>
                        <option value="mantenimiento_vencido">Mantenimiento Vencido</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-tinta mb-1.5">Estado</label>
                    <select wire:model="filtroEstado"
                        class="form-input">
                        <option value="todas">Todas</option>
                        <option value="no_leidas">No Leídas</option>
                        <option value="pendientes">Pendientes de Acción</option>
                        <option value="vencidas">Vencidas</option>
                        <option value="accionadas">Accionadas</option>
                    </select>
                </div>
                <div>
                    @if($estadisticas['no_leidas'] > 0)
                        <x-ui.button variant="primary" icon="check" wire:click="marcarTodasComoLeidas" class="w-full justify-center">
                            Marcar Todas como Leídas
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </div>
    </x-ui.card>

    <!-- Lista de notificaciones -->
    <x-ui.card class="overflow-hidden">
        <div class="p-0">
            @if($notificaciones->count() > 0)
                <x-ui.table-container>
                    <table class="data-table">
                        <tbody class="divide-y divide-arena">
                            @foreach($notificaciones as $notificacion)
                                <tr class="{{ !$notificacion->leida ? 'bg-pino-suave' : 'bg-white' }}" wire:key="row-{{ $notificacion->id }}">
                                    <td class="px-4 py-3 w-[60px]">
                                        <!-- Icono según tipo -->
                                        @if($notificacion->tipo === 'umbral_alcanzado')
                                            <flux:icon.exclamation-triangle class="size-5 text-resina" />
                                        @elseif($notificacion->tipo === 'stock_insuficiente')
                                            <flux:icon.cube class="size-5 text-tierra" />
                                        @elseif($notificacion->tipo === 'recordatorio_programado')
                                            <flux:icon.calendar class="size-5 text-pino" />
                                        @elseif($notificacion->tipo === 'mantenimiento_vencido')
                                            <flux:icon.x-circle class="size-5 text-tierra" />
                                        @else
                                            <flux:icon.bell class="size-5 text-tinta-suave" />
                                        @endif
                                    </td>

                                    <td class="px-3 py-3">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h6 class="mb-1 {{ !$notificacion->leida ? 'font-bold' : 'font-semibold' }} text-sm text-tinta">
                                                    {{ $notificacion->titulo }}
                                                </h6>
                                                <p class="text-tinta-suave mb-2 text-xs">
                                                    {{ $notificacion->mensaje }}
                                                </p>

                                                <!-- Badges de estado -->
                                                <div class="flex gap-2 items-center flex-wrap">
                                                    <x-ui.badge variant="neutral">
                                                        <flux:icon.clock class="size-3 mr-1" /> {{ $notificacion->created_at->format('d/m/Y H:i') }}
                                                    </x-ui.badge>

                                                    @if($notificacion->fecha_limite)
                                                        @php
                                                            $diasRestantes = $notificacion->diasRestantes();
                                                        @endphp
                                                        @if($diasRestantes !== null)
                                                            @if($diasRestantes >= 0)
                                                                <x-ui.badge variant="warning">
                                                                    <flux:icon.clock class="size-3 mr-1" /> {{ $diasRestantes }} día(s) restante(s)
                                                                </x-ui.badge>
                                                            @else
                                                                <x-ui.badge variant="danger">
                                                                    <flux:icon.exclamation-triangle class="size-3 mr-1" /> Vencida hace {{ abs($diasRestantes) }} día(s)
                                                                </x-ui.badge>
                                                            @endif
                                                        @endif
                                                    @endif

                                                    @if($notificacion->accionada)
                                                        <x-ui.badge variant="success">
                                                            <flux:icon.check class="size-3 mr-1" /> Accionada
                                                        </x-ui.badge>
                                                    @endif

                                                    @if(!$notificacion->leida)
                                                        <x-ui.badge variant="info">
                                                            <flux:icon.bell-alert class="size-3 mr-1" /> Nueva
                                                        </x-ui.badge>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Acciones -->
                                            <div class="flex flex-col gap-2 ml-3">
                                                @if(!$notificacion->leida)
                                                    <x-ui.button
                                                        variant="secondary"
                                                        size="sm"
                                                        icon="check"
                                                        wire:click="marcarComoLeida({{ $notificacion->id }})"
                                                        title="Marcar como leída"
                                                    ></x-ui.button>
                                                @endif

                                                @if(!$notificacion->accionada && $notificacion->tipo === 'umbral_alcanzado')
                                                    <x-ui.button
                                                        variant="primary"
                                                        size="sm"
                                                        icon="check"
                                                        wire:click="marcarComoAccionada({{ $notificacion->id }})"
                                                        title="Marcar como accionada"
                                                    ></x-ui.button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-ui.table-container>

                <!-- Paginación -->
                <div class="p-4">
                    {{ $notificaciones->links() }}
                </div>
            @else
                <x-empty-state message="No hay notificaciones que coincidan con los filtros" icon="inbox" />
            @endif
        </div>
    </x-ui.card>
</div>
