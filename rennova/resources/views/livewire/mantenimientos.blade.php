<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            🔧 Mantenimientos
        </h1>
        <div class="flex items-center gap-2">
            <button type="button"
                wire:click="ejecutarFlujoPresentacion"
                class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-800 transition hover:bg-amber-200">
                ▶️ Ejecutar flujo presentacion
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 shadow-sm" role="alert">
            ✓
            <span class="flex-1 font-medium">{{ session('message') }}</span>
            <button type="button" class="text-green-600 hover:text-green-800" @click="open = false">
                ✕
            </button>
        </div>
    @endif

    <div class="mb-6 flex gap-0">
        @canany(['crear-mantenimientos', 'editar-mantenimientos'])
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all {{ $tab_activo === 'nuevo' ? 'text-white bg-brand border-brand' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            ➕ Nuevo Mantenimiento
        </button>
        @endcanany
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all {{ $tab_activo === 'listado' ? 'text-white bg-brand border-brand' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
            📋 Listado de Mantenimientos
        </button>
    </div>

    @if($tab_activo === 'nuevo')
        @canany(['crear-mantenimientos', 'editar-mantenimientos'])
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-800 mb-0">
                        {{ $mantenimiento_id ? '✏️ Editar Orden' : '➕ Nueva Orden de Mantenimiento' }}
                    </h5>
                </div>
                <div class="p-6">
                    <!-- Alerta para tipo preventivo -->
                    @if(count($kitPreventivo) > 0)
                        <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-700">
                            <h6 class="mb-2 flex items-center gap-2 font-semibold">
                                📦 Kit de Mantenimiento Preventivo
                            </h6>
                            <small>Se utilizarán los siguientes insumos del kit configurado:</small>
                            <ul class="mb-0 mt-2 list-inside space-y-1 text-sm">
                                @foreach($kitPreventivo as $item)
                                    <li wire:key="kit-{{ $loop->index }}">{{ $item['nombre'] ?? 'N/A' }}: {{ number_format($item['cantidad_requerida'], 2) }} unidades</li>
                                @endforeach
                            </ul>
                        </div>
                    @elseif($id_maquinaria && $id_tipo_mantenimiento)
                        @php
                            $tipoSeleccionado = $tipos->firstWhere('id_tipo_mantenimiento', $id_tipo_mantenimiento);
                            $esPreventivo = $tipoSeleccionado && str_contains(strtolower($tipoSeleccionado->nombre), 'preventivo');
                        @endphp
                        @if($esPreventivo)
                            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-700">
                                ⚠️ 
                                <strong>Advertencia:</strong> No hay kit de mantenimiento preventivo configurado para esta maquinaria.
                                <a href="/kits-mantenimiento" class="font-semibold hover:underline">Configurar kit</a>
                            </div>
                        @endif
                    @endif

                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Maquinaria <span class="text-red-500">*</span></label>
                                <select wire:model.live="id_maquinaria" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('id_maquinaria') ring-2 ring-red-500 @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($maquinarias as $maquinaria)
                                        <option value="{{ $maquinaria->id_maquinaria }}" wire:key="option-{{ $maquinaria->id_maquinaria }}">
                                            {{ $maquinaria->modelo }} - {{ $maquinaria->tipoMaquinaria?->nombre ?? 'N/A' }}
                                            @if($maquinaria->umbral_toneladas)
                                                ({{ number_format($maquinaria->toneladas_acumuladas ?? 0, 0) }}/{{ number_format($maquinaria->umbral_toneladas, 0) }} ton)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_maquinaria') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tipo de Mantenimiento <span class="text-red-500">*</span></label>
                                <select wire:model.live="id_tipo_mantenimiento" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('id_tipo_mantenimiento') ring-2 ring-red-500 @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($tipos as $tipo)
                                        <option value="{{ $tipo->id_tipo_mantenimiento }}" wire:key="option-{{ $tipo->id_tipo_mantenimiento }}">{{ $tipo->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('id_tipo_mantenimiento') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                @if($id_tipo_mantenimiento)
                                    @php
                                        $tipoSeleccionado = $tipos->firstWhere('id_tipo_mantenimiento', $id_tipo_mantenimiento);
                                    @endphp
                                    @if($tipoSeleccionado && str_contains(strtolower($tipoSeleccionado->nombre), 'preventivo'))
                                        <small class="text-blue-600 text-xs mt-1 block">
                                            ℹ️ Se utilizará el kit de mantenimiento preventivo
                                        </small>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Inicio <span class="text-red-500">*</span></label>
                                <input type="date" wire:model="fecha_inicio" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('fecha_inicio') ring-2 ring-red-500 @enderror">
                                @error('fecha_inicio') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Programada</label>
                                <input type="date" wire:model="fecha_programada" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('fecha_programada') ring-2 ring-red-500 @enderror">
                                @error('fecha_programada') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                <small class="text-slate-500 text-xs mt-1 block">
                                    ℹ️ Debe estar dentro de los próximos 7 días ({{ \Carbon\Carbon::now()->format('d/m/Y') }} - {{ \Carbon\Carbon::now()->addDays(7)->format('d/m/Y') }})
                                </small>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Estado <span class="text-red-500">*</span></label>
                            <select wire:model="estado" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('estado') ring-2 ring-red-500 @enderror">
                                <option value="programado">Programado</option>
                                <option value="en curso">En Curso</option>
                            </select>
                            @error('estado') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            <small class="text-slate-500 text-xs mt-1 block">La orden se completará desde el listado</small>
                        </div>

                        <div class="flex gap-2 justify-end">
                            @if ($mantenimiento_id)
                                <button type="button" wire:click="resetCampos" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium text-sm">
                                    ✕ Cancelar
                                </button>
                            @endif
                            @canany(['crear-mantenimientos', 'editar-mantenimientos'])
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm bg-brand hover:bg-brand-hover">
                                ✓ {{ $mantenimiento_id ? 'Actualizar' : 'Crear Orden' }}
                            </button>
                            @endcanany
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcanany
    @elseif($tab_activo === 'listado')
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                <div class="p-6">
                    <!-- Buscador -->
                    <div class="mb-6">
                        <div class="flex items-center gap-2 px-4 py-3 border border-slate-300 rounded-lg bg-slate-50">
                            🔍
                            <input type="text" wire:model.live="busqueda" placeholder="Buscar por maquinaria, tipo, estado o costo..." class="flex-1 bg-slate-50 border-0 focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400">
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50">
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Maquinaria</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Tipo</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Fecha Inicio</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Fecha Programada</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Fecha Fin</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase text-slate-600">Costo</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Estado</th>
                                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($mantenimientos as $mantenimiento)
                                    <tr class="hover:bg-slate-50 transition-colors" wire:key="row-{{ $mantenimiento->id_mantenimiento }}">
                                        <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $mantenimiento->id_mantenimiento }}</span></td>
                                        <td class="px-3 py-3 font-semibold text-slate-800">{{ $mantenimiento->maquinaria?->modelo ?? 'N/A' }}</td>
                                        <td class="px-3 py-3 text-slate-600">{{ $mantenimiento->tipoMantenimiento?->nombre ?? 'N/A' }}</td>
                                        <td class="px-3 py-3 text-slate-600">{{ $mantenimiento->fecha_inicio ? \Carbon\Carbon::parse($mantenimiento->fecha_inicio)->format('d/m/Y') : 'N/A' }}</td>
                                        <td class="px-3 py-3">
                                            @if($mantenimiento->fecha_programada)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                                    {{ \Carbon\Carbon::parse($mantenimiento->fecha_programada)->format('d/m/Y') }}
                                                </span>
                                                @if($mantenimiento->estado === 'programado' && \Carbon\Carbon::parse($mantenimiento->fecha_programada)->isToday())
                                                    ⚠️
                                                @endif
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-slate-600">{{ $mantenimiento->fecha_fin ? \Carbon\Carbon::parse($mantenimiento->fecha_fin)->format('d/m/Y') : 'N/A' }}</td>
                                        <td class="px-3 py-3 text-right text-slate-600">${{ number_format($mantenimiento->costo_total, 2, ',', '.') }}</td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $mantenimiento->estado == 'completado' ? 'bg-green-50 text-green-700 border border-green-200' : 
                                                   ($mantenimiento->estado == 'en curso' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 
                                                   ($mantenimiento->estado == 'vencido' ? 'bg-red-50 text-red-700 border border-red-200' : 
                                                   'bg-slate-100 text-slate-600 border border-slate-200')) }}">
                                                {{ ucfirst($mantenimiento->estado) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            @php 
                                                $isCompletado = strtolower(trim($mantenimiento->estado ?? '')) === 'completado';
                                                $isProgramado = strtolower(trim($mantenimiento->estado ?? '')) === 'programado';
                                                $isVencido = strtolower(trim($mantenimiento->estado ?? '')) === 'vencido';
                                            @endphp
                                            <div class="flex gap-1 justify-center">
                                                @if($isProgramado)
                                                    @can('confirmar-mantenimiento')
                                                    <button type="button" wire:click="confirmarMantenimiento({{ $mantenimiento->id_mantenimiento }})" title="Confirmar realización" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200">
                                                        ✓
                                                    </button>
                                                    @endcan
                                                @endif
                                                @if($isVencido)
                                                    @can('reprogramar-mantenimiento')
                                                    <button type="button" wire:click="reprogramarMantenimiento({{ $mantenimiento->id_mantenimiento }})" title="Reprogramar" class="inline-flex items-center px-2 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded transition-colors border border-amber-200">
                                                        📅
                                                    </button>
                                                    @endcan
                                                @endif
                                                @can('editar-mantenimientos')
                                                <button type="button" wire:click="abrirModalCompletar({{ $mantenimiento->id_mantenimiento }})" title="Completar" class="inline-flex items-center px-2 py-1 bg-green-50 text-green-700 hover:bg-green-100 rounded transition-colors border border-green-200 {{ ($isCompletado || $isVencido) ? 'opacity-50 cursor-not-allowed' : '' }}" @if($isCompletado || $isVencido) disabled @endif>
                                                    ✓
                                                </button>
                                                <button type="button" wire:click="editar({{ $mantenimiento->id_mantenimiento }})" title="Editar" class="inline-flex items-center px-2 py-1 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded transition-colors border border-purple-200">
                                                    ✏️
                                                </button>
                                                @endcan
                                                @can('eliminar-mantenimientos')
                                                <button type="button" wire:click="eliminar({{ $mantenimiento->id_mantenimiento }})" onclick="return confirm('¿Está seguro de eliminar este mantenimiento?')" title="Eliminar" class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200">
                                                    🗑️
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-3 py-8 text-center">
                                            📥
                                            <p class="text-slate-500 font-medium">No hay mantenimientos registrados.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de completar orden -->
    <livewire:completar-orden-modal />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.querySelector('[x-data*="open"]');
            if (successAlert && window.Alpine) {
                setTimeout(() => {
                    successAlert.remove?.();
                }, 3000);
            }
        });
    </script>
</div>
