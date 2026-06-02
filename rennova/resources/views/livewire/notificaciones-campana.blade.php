<li class="nav-item dropdown" id="notificaciones-dropdown">
    <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificaciones-toggle">
        <i class="bi bi-bell-fill" style="font-size: 1.3rem;"></i>
        @if($cantidadNoLeidas > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                {{ $cantidadNoLeidas > 9 ? '9+' : $cantidadNoLeidas }}
            </span>
        @endif
    </a>

    <ul class="dropdown-menu dropdown-menu-end" style="width: 380px; max-height: 500px; overflow-y: auto;">
        <!-- Header -->
        <li class="px-3 py-2 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Notificaciones</h6>
                <div class="d-flex gap-2">
                    @if($cantidadNoLeidas > 0)
                        <button 
                            wire:click="marcarTodasComoLeidas" 
                            class="btn btn-sm btn-link text-primary p-0"
                            style="font-size: 0.85rem;"
                            type="button"
                        >
                            Marcar todas como leídas
                        </button>
                    @endif
                    <a href="{{ route('notificaciones.index') }}" class="btn btn-sm btn-link text-secondary p-0" style="font-size: 0.85rem;">
                        <i class="bi bi-list-ul"></i> Historial
                    </a>
                </div>
            </div>
        </li>

        <!-- Lista de notificaciones -->
        @if($notificaciones->count() > 0)
            @foreach($notificaciones as $notificacion)
                <li>
                    <div 
                        wire:click="irANotificacion({{ $notificacion->id }})"
                        onclick="event.stopPropagation()"
                        class="dropdown-item px-3 py-2 border-bottom"
                        style="white-space: normal; background: {{ $notificacion->leida ? '#fff' : '#f8f9fa' }}; cursor: pointer;"
                    >
                        <div class="d-flex align-items-start">
                            <!-- Icono según tipo -->
                            <div class="me-2 mt-1" style="min-width: 30px;">
                                @if($notificacion->tipo === 'umbral_alcanzado')
                                    <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 1.2rem;"></i>
                                @elseif($notificacion->tipo === 'stock_insuficiente')
                                    <i class="bi bi-box-seam text-danger" style="font-size: 1.2rem;"></i>
                                @elseif($notificacion->tipo === 'recordatorio_programado')
                                    <i class="bi bi-calendar-check text-info" style="font-size: 1.2rem;"></i>
                                @else
                                    <i class="bi bi-bell text-secondary" style="font-size: 1.2rem;"></i>
                                @endif
                            </div>

                            <!-- Contenido -->
                            <div class="flex-grow-1">
                                <div class="fw-semibold" style="font-size: 0.9rem;">{{ $notificacion->titulo }}</div>
                                <div class="text-muted small mt-1" style="font-size: 0.82rem;">
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
                                                <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">
                                                    <i class="bi bi-clock"></i> {{ $diasRestantes }} dia(s) restante(s)
                                                </span>
                                            @else
                                                <span class="badge bg-danger" style="font-size: 0.7rem;">
                                                    <i class="bi bi-x-circle"></i> Vencida
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                @endif

                                <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                    {{ $notificacion->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach

            <!-- Ver todas -->
            <li class="text-center py-2">
                <a href="{{ route('notificaciones.index') }}" class="btn btn-sm btn-link text-primary">
                    Ver todas las notificaciones
                </a>
            </li>
        @else
            <li class="text-center py-4 text-muted">
                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                <p class="mb-0 mt-2">No tienes notificaciones nuevas</p>
            </li>
        @endif
    </ul>
</li>

<!-- Modal de Programación de Mantenimiento -->
@if($mostrarModalProgramacion && $notificacionSeleccionada && $mantenimientoSeleccionado)
<div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1" wire:click.self="cerrarModalProgramacion">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-check"></i> Programar Mantenimiento
                </h5>
                <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalProgramacion"></button>
            </div>
            <div class="modal-body">
                <!-- Información de la notificación -->
                <div class="alert alert-info mb-3">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle-fill me-2" style="font-size: 1.5rem;"></i>
                        <div>
                            <strong>{{ $notificacionSeleccionada->titulo }}</strong>
                            <p class="mb-0 mt-1 small">{{ $notificacionSeleccionada->mensaje }}</p>
                        </div>
                    </div>
                </div>

                <!-- Información del mantenimiento -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-3">
                            <i class="bi bi-tools"></i> Detalles del Mantenimiento
                        </h6>
                        <div class="row g-2">
                            <div class="col-12">
                                <small class="text-muted">Maquinaria:</small>
                                <div class="fw-semibold">{{ $mantenimientoSeleccionado->maquinaria->nombre ?? 'N/A' }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Tipo:</small>
                                <div class="fw-semibold">{{ $mantenimientoSeleccionado->tipoMantenimiento->nombre ?? 'N/A' }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Estado:</small>
                                <div>
                                    @if($mantenimientoSeleccionado->estado === 'pendiente')
                                        <span class="badge bg-warning">Pendiente</span>
                                    @elseif($mantenimientoSeleccionado->estado === 'programado')
                                        <span class="badge bg-info">Programado</span>
                                    @elseif($mantenimientoSeleccionado->estado === 'completado')
                                        <span class="badge bg-success">Completado</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($mantenimientoSeleccionado->estado) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de programación -->
                <form wire:submit.prevent="programarMantenimiento">
                    <div class="mb-3">
                        <label for="fechaProgramada" class="form-label fw-semibold">
                            <i class="bi bi-calendar3"></i> Fecha Programada
                        </label>
                        <input 
                            type="date" 
                            class="form-control @error('fechaProgramada') is-invalid @enderror" 
                            id="fechaProgramada" 
                            wire:model="fechaProgramada"
                            min="{{ $fechaMinima }}"
                            max="{{ $fechaMaxima }}"
                        >
                        @error('fechaProgramada')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> 
                            Debes programar el mantenimiento entre el 
                            <strong>{{ \Carbon\Carbon::parse($fechaMinima)->format('d/m/Y') }}</strong> y el 
                            <strong>{{ \Carbon\Carbon::parse($fechaMaxima)->format('d/m/Y') }}</strong>
                            (maximo 7 dias desde la notificacion).
                        </div>
                    </div>

                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModalProgramacion">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Confirmar Programación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initNotificacionesDropdown();
    });

    // Reinicializar después de cada actualización de Livewire
    document.addEventListener('livewire:update', function() {
        setTimeout(initNotificacionesDropdown, 100);
    });

    function initNotificacionesDropdown() {
        // Esperar a que Bootstrap esté disponible
        const checkBootstrap = setInterval(function() {
            if (window.bootstrap && window.bootstrap.Dropdown) {
                clearInterval(checkBootstrap);
                
                const dropdownElement = document.getElementById('notificaciones-toggle');
                if (dropdownElement) {
                    // Destruir instancia anterior si existe
                    const existingInstance = bootstrap.Dropdown.getInstance(dropdownElement);
                    if (existingInstance) {
                        existingInstance.dispose();
                    }
                    
                    // Crear nueva instancia
                    new bootstrap.Dropdown(dropdownElement);
                    
                    console.log('✓ Dropdown de notificaciones inicializado');
                }
            }
        }, 100);
        
        // Timeout de seguridad (5 segundos)
        setTimeout(() => clearInterval(checkBootstrap), 5000);
    }
</script>
@endpush