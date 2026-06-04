<div>
    <div class="mx-auto max-w-7xl px-4 py-8" x-data="{ tab: 'listado' }">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-geo-alt"></i> Lotes
        </h1>
    </div>

    <x-flash-messages />

    <div class="mb-6 flex gap-0">
        @canany(['crear-lotes', 'editar-lotes'])
        <button type="button" @click="tab = 'nuevo'; $wire.$refresh()"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all"
            :class="tab === 'nuevo' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
            :style="tab === 'nuevo' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''">
            <i class="bi bi-plus-circle"></i> Nuevo Lote
        </button>
        @endcanany
        <button type="button" @click="tab = 'listado'; $wire.$refresh()"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all"
            :class="tab === 'listado' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
            :style="tab === 'listado' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''">
            <i class="bi bi-list-ul"></i> Listado de Lotes
        </button>
    </div>

    <div>
        @canany(['crear-lotes', 'editar-lotes'])
        <div x-show="tab === 'nuevo'" x-transition>
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-md">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-700">
                        <i class="bi bi-{{ $lote_id ? 'pencil-square' : 'plus-circle' }}"></i>
                        {{ $lote_id ? 'Modificar Lote' : 'Nuevo Lote' }}
                    </h5>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="guardar" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Propietario <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="propietario"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors {{ $errors->has('propietario') ? 'ring-2 ring-red-500' : '' }}"
                                    placeholder="Nombre del propietario">
                                @error('propietario') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Ubicación <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="ubicacion"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors {{ $errors->has('ubicacion') ? 'ring-2 ring-red-500' : '' }}"
                                    placeholder="Ubicación del lote">
                                @error('ubicacion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Especie</label>
                                <input type="text" wire:model="especie"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors {{ $errors->has('especie') ? 'ring-2 ring-red-500' : '' }}"
                                    placeholder="Especie de madera">
                                @error('especie') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Superficie (ha)</label>
                                <input type="number" wire:model="superficie" step="0.1" min="0"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors {{ $errors->has('superficie') ? 'ring-2 ring-red-500' : '' }}"
                                    placeholder="0.00">
                                @error('superficie') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Condición de compra</label>
                                <select wire:model="condicion_compra"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors {{ $errors->has('condicion_compra') ? 'ring-2 ring-red-500' : '' }}">
                                    <option value="">Seleccione...</option>
                                    <option value="propio">Vuelo Forestal</option>
                                    <option value="alquilado">Compra por tonelada</option>
                                </select>
                                @error('condicion_compra') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Estado</label>
                                <select wire:model="estado"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors {{ $errors->has('estado') ? 'ring-2 ring-red-500' : '' }}">
                                    <option value="activo">Activo</option>
                                    <option value="en_proceso">En Explotación</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="cerrado">Cerrado</option>
                                    <option value="baja">Baja</option>
                                </select>
                                @error('estado') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Tarea principal <span class="text-red-500">*</span></label>
                            <select wire:model="main_task_type"
                                class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors {{ $errors->has('main_task_type') ? 'ring-2 ring-red-500' : '' }}">
                                <option value="">Seleccione...</option>
                                @foreach($this->taskTypes as $tt)
                                    <option value="{{ $tt->value }}">{{ $tt->label() }}</option>
                                @endforeach
                            </select>
                            @error('main_task_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                            <div class="flex items-center gap-2 font-semibold">
                                <i class="bi bi-info-circle"></i>
                                Coordenadas GPS (Opcional)
                            </div>
                            <p class="mt-1 text-blue-700">
                                Agregue las coordenadas para habilitar pronóstico de lluvia y alertas climáticas.
                                <a href="https://www.google.com/maps" target="_blank" class="font-medium underline hover:text-blue-900">Buscar coordenadas</a>
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700"><i class="bi bi-geo"></i> Latitud</label>
                                <input type="number" wire:model="latitud" step="0.00000001" min="-90" max="90"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors {{ $errors->has('latitud') ? 'ring-2 ring-red-500' : '' }}"
                                    placeholder="-27.469771">
                                @error('latitud') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                <p class="mt-1 text-xs text-slate-500">Ejemplo: -27.469771 (entre -90 y 90)</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700"><i class="bi bi-geo-alt"></i> Longitud</label>
                                <input type="number" wire:model="longitud" step="0.00000001" min="-180" max="180"
                                    class="w-full rounded-lg border border-default py-3 px-4 shadow-sm focus:border-green-700 focus:ring-green-600 transition-colors {{ $errors->has('longitud') ? 'ring-2 ring-red-500' : '' }}"
                                    placeholder="-58.832443">
                                @error('longitud') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                <p class="mt-1 text-xs text-slate-500">Ejemplo: -58.832443 (entre -180 y 180)</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" wire:click="resetCampos"
                                class="lotes-form-btn lotes-form-btn--secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                            @canany(['crear-lotes', 'editar-lotes'])
                            <button type="submit"
                                class="lotes-form-btn lotes-form-btn--primary">
                                <i class="bi bi-check-circle"></i> {{ $lote_id ? 'Actualizar' : 'Guardar' }}
                            </button>
                            @endcanany
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcanany

        <div x-show="tab === 'listado'" x-transition>
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-md">
                <div class="border-b border-slate-200 bg-slate-50 p-6">
                    <div class="relative max-w-md">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" wire:model.live="busqueda"
                            class="block w-full rounded-lg border-slate-300 pl-10 focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            placeholder="Buscar por propietario, ubicación o especie...">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-3 py-4 text-center font-semibold">ID</th>
                                <th class="px-3 py-4 font-semibold">Propietario</th>
                                <th class="px-3 py-4 font-semibold">Ubicación</th>
                                <th class="px-3 py-4 font-semibold">Especie</th>
                                <th class="px-3 py-4 text-right font-semibold">Superficie (ha)</th>
                                <th class="px-3 py-4 font-semibold">Coordenadas GPS</th>
                                <th class="px-3 py-4 font-semibold">Condición</th>
                                <th class="px-3 py-4 font-semibold">Estado</th>
                                <th class="px-3 py-4 text-right font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($lotes as $lote)
                                <tr class="transition-colors hover:bg-slate-50">
                                    <td class="px-3 py-4 text-center">
                                        <span class="inline-block rounded bg-slate-100 px-2 py-1 font-mono text-xs text-slate-600">{{ $lote->id_lote }}</span>
                                    </td>
                                    <td class="px-3 py-4 font-medium text-slate-900">{{ $lote->propietario }}</td>
                                    <td class="px-3 py-4 text-slate-500">{{ $lote->ubicacion }}</td>
                                    <td class="px-3 py-4 text-slate-500">{{ $lote->especie ?? '-' }}</td>
                                    <td class="px-3 py-4 text-right tabular-nums">{{ number_format($lote->superficie ?? 0, 2) }}</td>
                                    <td class="px-3 py-4">
                                        @if($lote->latitud && $lote->longitud)
                                            <a href="https://www.google.com/maps?q={{ $lote->latitud }},{{ $lote->longitud }}" target="_blank"
                                                class="inline-flex items-center gap-2 text-sm text-blue-700 hover:text-blue-900">
                                                <i class="bi bi-geo-alt-fill"></i>
                                                <span class="tabular-nums">{{ number_format($lote->latitud, 6) }}, {{ number_format($lote->longitud, 6) }}</span>
                                            </a>
                                        @else
                                            <span class="text-slate-400"><i class="bi bi-geo"></i> Sin coordenadas</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4">
                                        @if($lote->condicion_compra)
                                            @php
                                                $condicionLabel = $lote->condicion_compra === 'propio'
                                                    ? 'Vuelo Forestal'
                                                    : 'Compra por tonelada';
                                            @endphp
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $lote->condicion_compra == 'propio' ? 'bg-green-50 border border-green-200' : 'bg-blue-100 text-blue-800' }}" style="{{ $lote->condicion_compra == 'propio' ? 'color: #2d7a4f' : '' }}">
                                                {{ $condicionLabel }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                            {{ $lote->estado === 'activo' ? 'bg-green-50 border border-green-200' : ($lote->estado === 'en_proceso' ? 'bg-amber-100 text-amber-800' : ($lote->estado === 'cerrado' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800')) }}"
                                            style="{{ $lote->estado === 'activo' ? 'color: #2d7a4f' : '' }}">
                                            @if($lote->estado === 'cerrado')
                                                <i class="bi bi-check-circle-fill me-1"></i>
                                            @endif
                                            {{ ucfirst(str_replace('_', ' ', $lote->estado)) }}
                                        </span>
                                    </td>
                                    <td class="pe-3 ps-3 py-4 text-right">
                                        @php
                                            $estadoRaw = $lote->estado;
                                            if (is_object($estadoRaw) && property_exists($estadoRaw, 'value')) {
                                                $estadoRaw = $estadoRaw->value;
                                            }
                                            $estado = strtolower(trim((string) $estadoRaw));
                                            $estado = preg_replace('/\s+/', '_', $estado);
                                            $estado = str_replace('-', '_', $estado);

                                            $esActivo = $estado === 'activo';
                                            $esInactivo = $estado === 'inactivo';
                                            $esCerrado = $estado === 'cerrado';
                                            $esEnProceso = $estado === 'en_proceso';

                                            $accionLabel = $esActivo ? 'Iniciar' : 'Ver';
                                            $accionIcon = $esActivo ? 'play-fill' : 'eye-fill';
                                            $accionClass = $esActivo
                                                ? 'lotes-accion-btn lotes-accion-btn--iniciar'
                                                : 'lotes-accion-btn lotes-accion-btn--ver';
                                        @endphp
                                        <div class="flex items-center justify-end gap-2">
                                            @if($esCerrado)
                                                <span class="inline-flex h-8 w-24 shrink-0 items-center justify-center whitespace-nowrap rounded-lg border text-[10px] font-bold uppercase bg-green-50" style="color: #2d7a4f; border-color: #2d7a4f;">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Finalizado
                                                </span>
                                            @elseif($esInactivo)
                                                <span class="inline-flex h-8 w-24 shrink-0 items-center justify-center whitespace-nowrap rounded-lg border border-slate-200 bg-slate-100 text-[10px] font-bold uppercase text-slate-400">
                                                    Pausado
                                                </span>
                                            @else
                                                @can('editar-lotes')
                                                <button type="button" 
                                                    wire:click="openLaunchpad({{ $lote->id_lote }})"
                                                    class="{{ $accionClass }}">
                                                    <i class="bi bi-{{ $accionIcon }} text-base"></i>
                                                    <span>{{ $accionLabel }}</span>
                                                </button>
                                                
                                                @if($esEnProceso)
                                                    <button type="button" 
                                                        wire:click="finalizarLote({{ $lote->id_lote }})"
                                                        onclick="return confirm('¿Finalizar este lote? Se liberarán todos los empleados y maquinarias asignadas.')"
                                                        class="inline-flex h-8 w-24 shrink-0 items-center justify-center gap-1.5 whitespace-nowrap rounded-lg border text-xs font-bold uppercase text-white transition-all hover:shadow-md"
                                                        style="background-color: #2d7a4f; border-color: #2d7a4f;"
                                                        onmouseover="this.style.backgroundColor='#245c3d'"
                                                        onmouseout="this.style.backgroundColor='#2d7a4f'">
                                                        <i class="bi bi-flag-fill"></i>
                                                        <span>Finalizar</span>
                                                    </button>
                                                @endif
                                                @endcan
                                            @endif

                                            {{-- Botón de opciones (tres puntos) --}}
                                            @canany(['editar-lotes', 'eliminar-lotes'])
                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                                                    <i class="bi bi-three-dots-vertical text-lg"></i>
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-transition
                                                    class="absolute right-0 z-20 mt-2 w-44 origin-top-right rounded-lg border border-slate-100 bg-white shadow-xl ring-1 ring-black ring-opacity-5">
                                                    <div class="py-1">
                                                        @can('editar-lotes')
                                                        <button wire:click="editar({{ $lote->id_lote }})" onclick="cambiarAPestanaFormulario()"
                                                            class="flex w-full items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                                            <i class="bi bi-pencil me-3"></i> Editar
                                                        </button>
                                                        @endcan
                                                        @can('eliminar-lotes')
                                                        <button wire:click="eliminar({{ $lote->id_lote }})" onclick="return confirm('¿Está seguro de eliminar este lote?')"
                                                            class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                            <i class="bi bi-trash me-3"></i> Eliminar
                                                        </button>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </div>
                                            @endcanany
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                        <i class="bi bi-inbox text-4xl mb-3 block"></i>
                                        <p class="mb-0">No hay lotes registrados.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>

{{-- Modal de recomendaciones - Componente reutilizable --}}
@if($mostrarModalRecomendaciones && $modalLoteId)
    <x-lotes.recomendaciones-modal 
        :recomendaciones="$recomendaciones"
        :recomendaciones-error="$recomendacionesError"
        :recomendaciones-mensaje="$recomendacionesMensaje"
        :modal-lote-id="$modalLoteId"
        :edit-proposal-id="$editProposalId"
        :edit-data="$editData"
        :expanded-proposal-id="$expandedProposalId"
        :editing-proposals="$editingProposals"
        :edit-proposed-maquinarias="$editProposedMaquinarias"
    />
@endif

@push('scripts')
<script>
    // Prevenir scroll del body cuando el modal está abierto
    const checkModal = () => {
        const modal = document.querySelector('.lotes-modal-overlay');
        if (modal) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    };
    
    // Verificar al cargar
    document.addEventListener('DOMContentLoaded', checkModal);
    
    // Verificar después de actualizaciones de Livewire
    document.addEventListener('livewire:navigated', checkModal);
    
    // Observar cambios en el DOM
    const observer = new MutationObserver(checkModal);
    observer.observe(document.body, { childList: true, subtree: true });
    
    // Función para cambiar a pestaña de formulario
    function cambiarAPestanaFormulario() {
        const event = new CustomEvent('cambiarTab', { detail: 'nuevo' });
        window.dispatchEvent(event);
    }
</script>
@endpush

</div>