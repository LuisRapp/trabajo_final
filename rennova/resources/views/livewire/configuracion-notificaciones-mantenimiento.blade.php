<div>
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="fas fa-bell"></i> Configuración de Notificaciones de Mantenimiento
            </h5>
        </div>
        <div class="card-body">
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <p class="text-muted mb-4">
                Seleccione qué usuarios recibirán notificaciones por email para cada tipo de evento de mantenimiento.
            </p>

            <div class="row">
                <!-- Notificaciones de Umbral Alcanzado -->
                <div class="col-md-4 mb-4">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-gauge-high"></i> Umbral Alcanzado</h6>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted">
                                Notifica cuando una maquinaria alcanza su umbral de toneladas y se genera una orden automática.
                            </p>
                            <div class="form-group">
                                <label class="form-label fw-bold">Usuarios a notificar:</label>
                                @foreach($usuarios as $usuario)
                                    <div class="form-check" wire:key="user-{{ $usuario->id }}">
                                        <input class="form-check-input" type="checkbox" 
                                               wire:model="usuariosUmbral" 
                                               value="{{ $usuario->id }}"
                                               id="umbral_{{ $usuario->id }}">
                                        <label class="form-check-label" for="umbral_{{ $usuario->id }}">
                                            {{ $usuario->name }} <small class="text-muted">({{ $usuario->email }})</small>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notificaciones de Stock Insuficiente -->
                <div class="col-md-4 mb-4">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-box-open"></i> Stock Insuficiente</h6>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted">
                                Notifica cuando se crea una orden pero faltan insumos en el kit de mantenimiento preventivo.
                            </p>
                            <div class="form-group">
                                <label class="form-label fw-bold">Usuarios a notificar:</label>
                                @foreach($usuarios as $usuario)
                                    <div class="form-check" wire:key="user-{{ $usuario->id }}">
                                        <input class="form-check-input" type="checkbox" 
                                               wire:model="usuariosStock" 
                                               value="{{ $usuario->id }}"
                                               id="stock_{{ $usuario->id }}">
                                        <label class="form-check-label" for="stock_{{ $usuario->id }}">
                                            {{ $usuario->name }} <small class="text-muted">({{ $usuario->email }})</small>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notificaciones de Recordatorio -->
                <div class="col-md-4 mb-4">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-calendar-check"></i> Recordatorio Diario</h6>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted">
                                Notifica diariamente sobre mantenimientos programados para el día actual que deben confirmarse.
                            </p>
                            <div class="form-group">
                                <label class="form-label fw-bold">Usuarios a notificar:</label>
                                @foreach($usuarios as $usuario)
                                    <div class="form-check" wire:key="user-{{ $usuario->id }}">
                                        <input class="form-check-input" type="checkbox" 
                                               wire:model="usuariosRecordatorio" 
                                               value="{{ $usuario->id }}"
                                               id="recordatorio_{{ $usuario->id }}">
                                        <label class="form-check-label" for="recordatorio_{{ $usuario->id }}">
                                            {{ $usuario->name }} <small class="text-muted">({{ $usuario->email }})</small>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button wire:click="guardarConfiguracion" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> Guardar Configuración
                </button>
            </div>

            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle"></i> <strong>Nota:</strong> Los comandos programados deben estar configurados en el cron del servidor:
                <ul class="mb-0 mt-2">
                    <li><code>php artisan mantenimiento:check-umbrales</code> - Verificación de umbrales (ejecutar periódicamente)</li>
                    <li><code>php artisan mantenimiento:check-programados</code> - Verificación diaria (ejecutar 1 vez al día)</li>
                </ul>
            </div>
        </div>
    </div>
</div>
