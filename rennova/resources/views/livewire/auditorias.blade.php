<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.clipboard-document-list class="size-6 text-pino" />
            Auditorías del Sistema
        </h1>
    </div>

    <x-ui.card class="overflow-hidden">
        <div class="bg-corteza-suave border-b border-arena px-6 py-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-tinta flex items-center gap-2">
                    <flux:icon.clock class="size-4" />
                    Registro de Cambios
                </h2>
                <x-ui.button type="button" variant="secondary" size="sm" icon="magnifying-glass"
                    wire:click="toggleFiltros" aria-controls="filtrosAuditoria" aria-expanded="{{ $mostrarFiltros ? 'true' : 'false' }}">
                    Filtros
                </x-ui.button>
            </div>
        </div>
        <div class="p-6">
            <!-- Filtros Colapsables -->
            @if($mostrarFiltros)
            <div id="filtrosAuditoria">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 pb-4 border-b border-arena">
                    <div>
                        <label class="block text-xs font-semibold text-tinta mb-1.5">Buscar</label>
                        <div class="flex items-center gap-2 px-3 py-2 border border-arena rounded-sm bg-white">
                            <flux:icon.magnifying-glass class="size-4 text-tinta-suave" />
                            <input type="text" wire:model.live.debounce.400ms="busqueda"
                                class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-sm text-tinta placeholder:text-tinta-suave"
                                placeholder="URL, IP o tag...">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-tinta mb-1.5">Modelo</label>
                        <select wire:model.live="filtroModelo" class="form-input">
                            <option value="">Todos los modelos</option>
                            @foreach($modelos as $modelo)
                                <option value="{{ $modelo['value'] }}" wire:key="option-{{ $modelo['value'] }}">{{ $modelo['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-tinta mb-1.5">Evento</label>
                        <select wire:model.live="filtroEvento" class="form-input">
                            <option value="">Todos</option>
                            <option value="created">Creado</option>
                            <option value="updated">Actualizado</option>
                            <option value="deleted">Eliminado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-tinta mb-1.5">Usuario</label>
                        <select wire:model.live="filtroUsuario" class="form-input">
                            <option value="">Todos los usuarios</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario['id'] }}" wire:key="option-{{ $usuario['id'] }}">{{ $usuario['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-tinta mb-1.5">Desde</label>
                        <input type="date" wire:model.live="filtroFechaDesde" class="form-input">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-tinta mb-1.5">Hasta</label>
                        <input type="date" wire:model.live="filtroFechaHasta" class="form-input">
                    </div>
                    <div class="flex items-end">
                        <x-ui.button type="button" variant="secondary" size="sm" icon="arrow-uturn-left"
                            wire:click="limpiarFiltros" class="w-full justify-center">
                            Limpiar
                        </x-ui.button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Tabla de Auditorías -->
            <x-ui.table-container>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Modelo / Registro</th>
                            <th>Evento</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditorias as $auditoria)
                            <tr wire:key="row-{{ $auditoria->id }}">
                                <td><x-ui.badge variant="neutral">#{{ $auditoria->id }}</x-ui.badge></td>
                                <td>
                                    <strong class="text-tinta">{{ class_basename($auditoria->auditable_type) }}</strong><br>
                                    <small class="text-tinta-suave">ID: {{ $auditoria->auditable_id }}</small>
                                </td>
                                <td>
                                    @if($auditoria->event === 'created')
                                        <x-ui.badge variant="success">Creado</x-ui.badge>
                                    @elseif($auditoria->event === 'updated')
                                        <x-ui.badge variant="info">Actualizado</x-ui.badge>
                                    @elseif($auditoria->event === 'deleted')
                                        <x-ui.badge variant="danger">Eliminado</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="neutral">{{ ucfirst($auditoria->event) }}</x-ui.badge>
                                    @endif
                                </td>
                                <td>
                                    @if($auditoria->user)
                                        <span class="inline-flex items-center gap-1 text-tinta">
                                            <flux:icon.user class="size-4 text-pino" />
                                            {{ $auditoria->user->name }}
                                        </span><br>
                                        <small class="text-tinta-suave">{{ $auditoria->ip_address ?? 'N/A' }}</small>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-tinta-suave">
                                            <flux:icon.computer-desktop class="size-4" />
                                            Sistema
                                        </span><br>
                                        <small class="text-tinta-suave">{{ $auditoria->ip_address ?? 'N/A' }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $auditoria->created_at->format('d/m/Y H:i') }}<br>
                                    <small class="text-tinta-suave">{{ $auditoria->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-center">
                                    <x-ui.button type="button" size="sm" icon="eye"
                                        wire:click="$set('modalDetalle', {{ $auditoria->id }})">
                                        Ver
                                    </x-ui.button>
                                </td>
                            </tr>
                        @empty
                            <x-empty-state :colspan="6" message="No hay auditorías registradas con los filtros aplicados." icon="document-text" />
                        @endforelse
                    </tbody>
                </table>
            </x-ui.table-container>
        </div>
        <div class="px-6 py-4 bg-corteza-suave border-t border-arena">
            {{ $auditorias->links() }}
        </div>
    </x-ui.card>

    <!-- Modales de Detalles -->
    @foreach($auditorias as $auditoria)
        @if(isset($modalDetalle) && $modalDetalle == $auditoria->id)
        <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center" wire:key="modal-{{ $auditoria->id }}">
            <div class="bg-white rounded-sm border border-arena shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="bg-corteza-suave border-b border-arena px-6 py-4 flex justify-between items-center">
                    <h5 class="text-lg font-semibold text-tinta flex items-center gap-2">
                        <flux:icon.information-circle class="size-5 text-pino" />
                        Detalles de Auditoría #{{ $auditoria->id }}
                    </h5>
                    <button type="button" class="text-tinta-suave hover:text-tinta" wire:click="$set('modalDetalle', null)">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 pb-4 border-b border-arena">
                        <div>
                            <p class="mb-2 text-sm"><strong class="text-tinta">Modelo:</strong> {{ class_basename($auditoria->auditable_type) }}</p>
                            <p class="mb-2 text-sm"><strong class="text-tinta">ID del Registro:</strong> #{{ $auditoria->auditable_id }}</p>
                            <p class="text-sm"><strong class="text-tinta">Evento:</strong>
                                @if($auditoria->event === 'created')
                                    <x-ui.badge variant="success">Creado</x-ui.badge>
                                @elseif($auditoria->event === 'updated')
                                    <x-ui.badge variant="info">Actualizado</x-ui.badge>
                                @elseif($auditoria->event === 'deleted')
                                    <x-ui.badge variant="danger">Eliminado</x-ui.badge>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="mb-2 text-sm"><strong class="text-tinta">Usuario:</strong> {{ $auditoria->user->name ?? 'Sistema' }}</p>
                            <p class="mb-2 text-sm"><strong class="text-tinta">IP:</strong> {{ $auditoria->ip_address ?? 'N/A' }}</p>
                            <p class="text-sm"><strong class="text-tinta">Fecha:</strong> {{ $auditoria->created_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    </div>

                    @if($auditoria->url)
                        <div class="mb-4">
                            <strong class="text-sm text-tinta">URL:</strong>
                            <code class="block bg-corteza-suave p-3 rounded-sm mt-1 text-sm text-tinta break-all">{{ $auditoria->url }}</code>
                        </div>
                    @endif

                    @if($auditoria->event === 'updated' && $auditoria->old_values && $auditoria->new_values)
                        <h6 class="mb-3 text-sm font-semibold text-tinta flex items-center gap-2">
                            <flux:icon.arrow-right class="size-4 text-pino" />
                            Cambios Realizados
                        </h6>
                        <x-ui.table-container>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">Campo</th>
                                        <th style="width: 35%;">Valor Anterior</th>
                                        <th style="width: 35%;">Valor Nuevo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($auditoria->new_values as $campo => $valorNuevo)
                                        @if(isset($auditoria->old_values[$campo]) && $auditoria->old_values[$campo] != $valorNuevo)
                                            <tr wire:key="field-{{ $campo }}">
                                                <td><strong>{{ $campo }}</strong></td>
                                                <td>
                                                    <div class="break-all">
                                                        {{ is_array($auditoria->old_values[$campo]) ? json_encode($auditoria->old_values[$campo], JSON_UNESCAPED_UNICODE) : ($auditoria->old_values[$campo] ?? 'null') }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="break-all">
                                                        {{ is_array($valorNuevo) ? json_encode($valorNuevo, JSON_UNESCAPED_UNICODE) : ($valorNuevo ?? 'null') }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </x-ui.table-container>
                    @elseif($auditoria->event === 'created' && $auditoria->new_values)
                        <h6 class="mb-3 text-sm font-semibold text-tinta flex items-center gap-2">
                            <flux:icon.arrow-right class="size-4 text-pino" />
                            Datos Creados
                        </h6>
                        <div class="bg-corteza-suave p-4 rounded-sm">
                            <pre class="text-sm max-h-[400px] overflow-y-auto text-tinta"><code>{{ json_encode($auditoria->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        </div>
                    @elseif($auditoria->event === 'deleted' && $auditoria->old_values)
                        <h6 class="mb-3 text-sm font-semibold text-tinta flex items-center gap-2">
                            <flux:icon.arrow-right class="size-4 text-pino" />
                            Datos Eliminados
                        </h6>
                        <div class="bg-corteza-suave p-4 rounded-sm">
                            <pre class="text-sm max-h-[400px] overflow-y-auto text-tinta"><code>{{ json_encode($auditoria->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        </div>
                    @endif
                </div>
                <div class="flex justify-end gap-2 px-6 py-4 bg-corteza-suave border-t border-arena">
                    <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="$set('modalDetalle', null)">
                        Cerrar
                    </x-ui.button>
                </div>
            </div>
        </div>
        @endif
    @endforeach
</div>
