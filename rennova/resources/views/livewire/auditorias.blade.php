<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-slate-900">📄 Auditorías del Sistema</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-600 text-white px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <strong>🕐 Registro de Cambios</strong>
                </div>
                <div>
                    <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/20 text-white rounded-lg text-sm font-medium hover:bg-white/30 transition-colors" wire:click="toggleFiltros" aria-controls="filtrosAuditoria" aria-expanded="{{ $mostrarFiltros ? 'true' : 'false' }}">
                        🔍 Filtros
                    </button>
                </div>
            </div>
        </div>
        <div class="p-6">
            <!-- Filtros Colapsables -->
            @if($mostrarFiltros)
            <div id="filtrosAuditoria">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 pb-4 border-b border-slate-200">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Buscar</label>
                        <input type="text" wire:model.live.debounce.400ms="busqueda"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20"
                            placeholder="URL, IP o tag...">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Modelo</label>
                        <select wire:model.live="filtroModelo"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                            <option value="">Todos los modelos</option>
                            @foreach($modelos as $modelo)
                                <option value="{{ $modelo['value'] }}" wire:key="option-{{ $modelo['value'] }}">{{ $modelo['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Evento</label>
                        <select wire:model.live="filtroEvento"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                            <option value="">Todos</option>
                            <option value="created">Creado</option>
                            <option value="updated">Actualizado</option>
                            <option value="deleted">Eliminado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Usuario</label>
                        <select wire:model.live="filtroUsuario"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                            <option value="">Todos los usuarios</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario['id'] }}" wire:key="option-{{ $usuario['id'] }}">{{ $usuario['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Desde</label>
                        <input type="date" wire:model.live="filtroFechaDesde"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Hasta</label>
                        <input type="date" wire:model.live="filtroFechaHasta"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                    </div>
                    <div class="flex items-end">
                        <button type="button" wire:click="limpiarFiltros"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors w-full justify-center">
                            ✕ Limpiar
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Tabla de Auditorías -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Modelo / Registro</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Evento</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($auditorias as $auditoria)
                            <tr wire:key="row-{{ $auditoria->id }}" class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">#{{ $auditoria->id }}</span></td>
                                <td class="px-4 py-2.5">
                                    <strong>{{ class_basename($auditoria->auditable_type) }}</strong><br>
                                    <small class="text-slate-500">ID: {{ $auditoria->auditable_id }}</small>
                                </td>
                                <td class="px-4 py-2.5">
                                    @if($auditoria->event === 'created')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">➕ Creado</span>
                                    @elseif($auditoria->event === 'updated')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand/10 text-brand">✏️ Actualizado</span>
                                    @elseif($auditoria->event === 'deleted')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">🗑️ Eliminado</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ ucfirst($auditoria->event) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    @if($auditoria->user)
                                        <span class="text-brand">👤</span> {{ $auditoria->user->name }}<br>
                                        <small class="text-slate-500">{{ $auditoria->ip_address ?? 'N/A' }}</small>
                                    @else
                                        <span class="text-slate-500">🤖 Sistema</span><br>
                                        <small class="text-slate-500">{{ $auditoria->ip_address ?? 'N/A' }}</small>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    {{ $auditoria->created_at->format('d/m/Y H:i') }}<br>
                                    <small class="text-slate-500">{{ $auditoria->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-xs font-medium shadow-sm transition-colors"
                                        wire:click="$set('modalDetalle', {{ $auditoria->id }})">
                                        👁️ Ver
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-400">
                                    <div class="text-5xl mb-2">📥</div>
                                    <p>No hay auditorías registradas con los filtros aplicados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            {{ $auditorias->links() }}
        </div>
    </div>

    <!-- Modales de Detalles -->
    @foreach($auditorias as $auditoria)
        @if(isset($modalDetalle) && $modalDetalle == $auditoria->id)
        <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center" wire:key="modal-{{ $auditoria->id }}">
            <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 rounded-t-xl flex justify-between items-center">
                    <h5 class="text-lg font-semibold text-slate-800">
                        ℹ️ Detalles de Auditoría #{{ $auditoria->id }}
                    </h5>
                    <button type="button" class="text-slate-400 hover:text-slate-600" wire:click="$set('modalDetalle', null)">✕</button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 pb-4 border-b border-slate-200">
                        <div>
                            <p class="mb-2"><strong>Modelo:</strong> {{ class_basename($auditoria->auditable_type) }}</p>
                            <p class="mb-2"><strong>ID del Registro:</strong> #{{ $auditoria->auditable_id }}</p>
                            <p><strong>Evento:</strong>
                                @if($auditoria->event === 'created')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Creado</span>
                                @elseif($auditoria->event === 'updated')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-brand/10 text-brand">Actualizado</span>
                                @elseif($auditoria->event === 'deleted')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Eliminado</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="mb-2"><strong>Usuario:</strong> {{ $auditoria->user->name ?? 'Sistema' }}</p>
                            <p class="mb-2"><strong>IP:</strong> {{ $auditoria->ip_address ?? 'N/A' }}</p>
                            <p><strong>Fecha:</strong> {{ $auditoria->created_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    </div>

                    @if($auditoria->url)
                        <div class="mb-4">
                            <strong>URL:</strong> <code class="block bg-slate-100 p-3 rounded-lg mt-1 text-sm">{{ $auditoria->url }}</code>
                        </div>
                    @endif

                    @if($auditoria->event === 'updated' && $auditoria->old_values && $auditoria->new_values)
                        <h6 class="mb-3 font-semibold text-slate-800">↔️ Cambios Realizados</h6>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border border-slate-200 rounded-lg">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-[30%]">Campo</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-[35%]">Valor Anterior</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-[35%]">Valor Nuevo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($auditoria->new_values as $campo => $valorNuevo)
                                        @if(isset($auditoria->old_values[$campo]) && $auditoria->old_values[$campo] != $valorNuevo)
                                            <tr wire:key="field-{{ $campo }}">
                                                <td class="px-4 py-2.5"><strong>{{ $campo }}</strong></td>
                                                <td class="px-4 py-2.5">
                                                    <div class="break-all">
                                                        {{ is_array($auditoria->old_values[$campo]) ? json_encode($auditoria->old_values[$campo], JSON_UNESCAPED_UNICODE) : ($auditoria->old_values[$campo] ?? 'null') }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2.5">
                                                    <div class="break-all">
                                                        {{ is_array($valorNuevo) ? json_encode($valorNuevo, JSON_UNESCAPED_UNICODE) : ($valorNuevo ?? 'null') }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($auditoria->event === 'created' && $auditoria->new_values)
                        <h6 class="mb-3 font-semibold text-slate-800">➕ Datos Creados</h6>
                        <div class="bg-slate-100 p-4 rounded-lg">
                            <pre class="text-sm max-h-[400px] overflow-y-auto"><code>{{ json_encode($auditoria->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        </div>
                    @elseif($auditoria->event === 'deleted' && $auditoria->old_values)
                        <h6 class="mb-3 font-semibold text-slate-800">🗑️ Datos Eliminados</h6>
                        <div class="bg-slate-100 p-4 rounded-lg">
                            <pre class="text-sm max-h-[400px] overflow-y-auto"><code>{{ json_encode($auditoria->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        </div>
                    @endif
                </div>
                <div class="flex justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-200 rounded-b-xl">
                    <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors"
                        wire:click="$set('modalDetalle', null)">
                        ✕ Cerrar
                    </button>
                </div>
            </div>
        </div>
        @endif
    @endforeach
</div>
