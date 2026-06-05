<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm" role="alert">
            <span class="text-emerald-600">✓</span>
            <span class="flex-1 text-sm font-medium">{{ session('message') }}</span>
            <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="open = false">✕</button>
        </div>
    @endif

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <span class="text-3xl">📥</span>
            <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ $estadisticas['total'] }}</h3>
            <p class="text-slate-500 text-sm">Total</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <span class="text-3xl">✉️</span>
            <h3 class="mt-2 text-2xl font-bold text-cyan-600">{{ $estadisticas['no_leidas'] }}</h3>
            <p class="text-slate-500 text-sm">No Leídas</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <span class="text-3xl">⏰</span>
            <h3 class="mt-2 text-2xl font-bold text-amber-500">{{ $estadisticas['pendientes'] }}</h3>
            <p class="text-slate-500 text-sm">Pendientes</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 text-center">
            <span class="text-3xl">⚠</span>
            <h3 class="mt-2 text-2xl font-bold text-red-500">{{ $estadisticas['vencidas'] }}</h3>
            <p class="text-slate-500 text-sm">Vencidas</p>
        </div>
    </div>

    <!-- Filtros y acciones -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipo de Notificación</label>
                    <select wire:model="filtroTipo"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                        <option value="todas">Todas</option>
                        <option value="umbral_alcanzado">Umbral Alcanzado</option>
                        <option value="stock_insuficiente">Stock Insuficiente</option>
                        <option value="recordatorio_programado">Recordatorio Programado</option>
                        <option value="mantenimiento_vencido">Mantenimiento Vencido</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estado</label>
                    <select wire:model="filtroEstado"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                        <option value="todas">Todas</option>
                        <option value="no_leidas">No Leídas</option>
                        <option value="pendientes">Pendientes de Acción</option>
                        <option value="vencidas">Vencidas</option>
                        <option value="accionadas">Accionadas</option>
                    </select>
                </div>
                <div>
                    @if($estadisticas['no_leidas'] > 0)
                        <button wire:click="marcarTodasComoLeidas"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors w-full justify-center">
                            ✓✓ Marcar Todas como Leídas
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de notificaciones -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-0">
            @if($notificaciones->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @foreach($notificaciones as $notificacion)
                                <tr class="{{ !$notificacion->leida ? 'bg-slate-50' : 'bg-white' }}" wire:key="row-{{ $notificacion->id }}">
                                    <td class="px-4 py-3 w-[60px]">
                                        <!-- Icono según tipo -->
                                        @if($notificacion->tipo === 'umbral_alcanzado')
                                            <span class="text-xl text-amber-500">⚠</span>
                                        @elseif($notificacion->tipo === 'stock_insuficiente')
                                            <span class="text-xl text-red-500">📦</span>
                                        @elseif($notificacion->tipo === 'recordatorio_programado')
                                            <span class="text-xl text-cyan-500">📅</span>
                                        @elseif($notificacion->tipo === 'mantenimiento_vencido')
                                            <span class="text-xl text-red-500">✖</span>
                                        @else
                                            <span class="text-xl text-slate-400">🔔</span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-3">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h6 class="mb-1 {{ !$notificacion->leida ? 'font-bold' : 'font-semibold' }} text-slate-800">
                                                    {{ $notificacion->titulo }}
                                                </h6>
                                                <p class="text-slate-500 mb-2 text-sm">
                                                    {{ $notificacion->mensaje }}
                                                </p>

                                                <!-- Badges de estado -->
                                                <div class="flex gap-2 items-center flex-wrap">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                                        🕐 {{ $notificacion->created_at->format('d/m/Y H:i') }}
                                                    </span>

                                                    @if($notificacion->fecha_limite)
                                                        @php
                                                            $diasRestantes = $notificacion->diasRestantes();
                                                        @endphp
                                                        @if($diasRestantes !== null)
                                                            @if($diasRestantes >= 0)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                                                    ⏳ {{ $diasRestantes }} día(s) restante(s)
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                                    ⚠ Vencida hace {{ abs($diasRestantes) }} día(s)
                                                                </span>
                                                            @endif
                                                        @endif
                                                    @endif

                                                    @if($notificacion->accionada)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                                            ✓ Accionada
                                                        </span>
                                                    @endif

                                                    @if(!$notificacion->leida)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-700">
                                                            ✉️ Nueva
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Acciones -->
                                            <div class="flex flex-col gap-2 ml-3">
                                                @if(!$notificacion->leida)
                                                    <button
                                                        wire:click="marcarComoLeida({{ $notificacion->id }})"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-xs font-medium hover:bg-slate-50 transition-colors"
                                                        title="Marcar como leída"
                                                    >
                                                        ✓
                                                    </button>
                                                @endif

                                                @if(!$notificacion->accionada && $notificacion->tipo === 'umbral_alcanzado')
                                                    <button
                                                        wire:click="marcarComoAccionada({{ $notificacion->id }})"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-emerald-300 bg-white text-emerald-600 rounded-lg text-xs font-medium hover:bg-emerald-50 transition-colors"
                                                        title="Marcar como accionada"
                                                    >
                                                        ✓✓
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="p-6">
                    {{ $notificaciones->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <div class="text-6xl text-slate-200 mb-4">📥</div>
                    <h5 class="text-slate-500">No hay notificaciones que coincidan con los filtros</h5>
                </div>
            @endif
        </div>
    </div>
</div>
