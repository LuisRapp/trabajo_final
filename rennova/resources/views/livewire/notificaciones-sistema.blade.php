<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-inbox-fill text-primary" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $estadisticas['total'] }}</h3>
                    <p class="text-muted small mb-0">Total</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-envelope-fill text-info" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $estadisticas['no_leidas'] }}</h3>
                    <p class="text-muted small mb-0">No Leídas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-clock-fill text-warning" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $estadisticas['pendientes'] }}</h3>
                    <p class="text-muted small mb-0">Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0">{{ $estadisticas['vencidas'] }}</h3>
                    <p class="text-muted small mb-0">Vencidas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y acciones -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tipo de Notificación</label>
                    <select wire:model="filtroTipo" class="form-select">
                        <option value="todas">Todas</option>
                        <option value="umbral_alcanzado">Umbral Alcanzado</option>
                        <option value="stock_insuficiente">Stock Insuficiente</option>
                        <option value="recordatorio_programado">Recordatorio Programado</option>
                        <option value="mantenimiento_vencido">Mantenimiento Vencido</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Estado</label>
                    <select wire:model="filtroEstado" class="form-select">
                        <option value="todas">Todas</option>
                        <option value="no_leidas">No Leídas</option>
                        <option value="pendientes">Pendientes de Acción</option>
                        <option value="vencidas">Vencidas</option>
                        <option value="accionadas">Accionadas</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    @if($estadisticas['no_leidas'] > 0)
                        <button wire:click="marcarTodasComoLeidas" class="btn btn-primary w-100">
                            <i class="bi bi-check-all"></i> Marcar Todas como Leídas
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de notificaciones -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($notificaciones->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <tbody>
                            @foreach($notificaciones as $notificacion)
                                <tr style="background: {{ $notificacion->leida ? '#fff' : '#f8f9fa' }};">
                                    <td class="px-4 py-3" style="width: 60px;">
                                        <!-- Icono según tipo -->
                                        @if($notificacion->tipo === 'umbral_alcanzado')
                                            <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 1.5rem;"></i>
                                        @elseif($notificacion->tipo === 'stock_insuficiente')
                                            <i class="bi bi-box-seam text-danger" style="font-size: 1.5rem;"></i>
                                        @elseif($notificacion->tipo === 'recordatorio_programado')
                                            <i class="bi bi-calendar-check text-info" style="font-size: 1.5rem;"></i>
                                        @elseif($notificacion->tipo === 'mantenimiento_vencido')
                                            <i class="bi bi-x-circle-fill text-danger" style="font-size: 1.5rem;"></i>
                                        @else
                                            <i class="bi bi-bell text-secondary" style="font-size: 1.5rem;"></i>
                                        @endif
                                    </td>
                                    
                                    <td class="px-3 py-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 {{ !$notificacion->leida ? 'fw-bold' : 'fw-semibold' }}">
                                                    {{ $notificacion->titulo }}
                                                </h6>
                                                <p class="text-muted mb-2" style="font-size: 0.9rem;">
                                                    {{ $notificacion->mensaje }}
                                                </p>
                                                
                                                <!-- Badges de estado -->
                                                <div class="d-flex gap-2 align-items-center flex-wrap">
                                                    <span class="badge bg-secondary" style="font-size: 0.75rem;">
                                                        <i class="bi bi-clock"></i> {{ $notificacion->created_at->format('d/m/Y H:i') }}
                                                    </span>

                                                    @if($notificacion->fecha_limite)
                                                        @php
                                                            $diasRestantes = $notificacion->diasRestantes();
                                                        @endphp
                                                        @if($diasRestantes !== null)
                                                            @if($diasRestantes >= 0)
                                                                <span class="badge bg-warning text-dark" style="font-size: 0.75rem;">
                                                                    <i class="bi bi-hourglass-split"></i> {{ $diasRestantes }} día(s) restante(s)
                                                                </span>
                                                            @else
                                                                <span class="badge bg-danger" style="font-size: 0.75rem;">
                                                                    <i class="bi bi-exclamation-circle"></i> Vencida hace {{ abs($diasRestantes) }} día(s)
                                                                </span>
                                                            @endif
                                                        @endif
                                                    @endif

                                                    @if($notificacion->accionada)
                                                        <span class="badge bg-success" style="font-size: 0.75rem;">
                                                            <i class="bi bi-check-circle"></i> Accionada
                                                        </span>
                                                    @endif

                                                    @if(!$notificacion->leida)
                                                        <span class="badge bg-info" style="font-size: 0.75rem;">
                                                            <i class="bi bi-envelope-fill"></i> Nueva
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Acciones -->
                                            <div class="d-flex flex-column gap-2 ms-3">
                                                @if(!$notificacion->leida)
                                                    <button 
                                                        wire:click="marcarComoLeida({{ $notificacion->id }})"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        title="Marcar como leída"
                                                    >
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                @endif

                                                @if(!$notificacion->accionada && $notificacion->tipo === 'umbral_alcanzado')
                                                    <button 
                                                        wire:click="marcarComoAccionada({{ $notificacion->id }})"
                                                        class="btn btn-sm btn-outline-success"
                                                        title="Marcar como accionada"
                                                    >
                                                        <i class="bi bi-check-all"></i>
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
                <div class="p-3">
                    {{ $notificaciones->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <h5 class="mt-3 text-muted">No hay notificaciones que coincidan con los filtros</h5>
                </div>
            @endif
        </div>
    </div>
</div>
