<div>
    <div class="w-full" x-data="{ tab: 'listado' }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.map-pin class="size-6" />
            Lotes
        </h1>
    </div>

    <x-flash-messages />

    <div class="mb-6 flex gap-0">
        @canany(['crear-lotes', 'editar-lotes'])
        <button type="button" @click="tab = 'nuevo'; $wire.$refresh()"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-sm transition-all"
            :class="tab === 'nuevo' ? 'text-blanco bg-pino border-pino' : 'bg-blanco text-tinta border-arena hover:bg-corteza-suave'">
            <flux:icon.plus class="size-4" />
            Nuevo Lote
        </button>
        @endcanany
        <button type="button" @click="tab = 'listado'; $wire.$refresh()"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-sm transition-all"
            :class="tab === 'listado' ? 'text-blanco bg-pino border-pino' : 'bg-blanco text-tinta border-arena hover:bg-corteza-suave'">
            <flux:icon.list-bullet class="size-4" />
            Listado de Lotes
        </button>
    </div>

    <div>
        @canany(['crear-lotes', 'editar-lotes'])
        <div x-show="tab === 'nuevo'" x-transition>
            <x-ui.card class="overflow-hidden">
                <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-tinta">
                        @if($lote_id)
                            <flux:icon.pencil-square class="size-5" />
                            Modificar Lote
                        @else
                            <flux:icon.plus class="size-5" />
                            Nuevo Lote
                        @endif
                    </h5>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="guardar" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-tinta">Propietario <span class="text-tierra">*</span></label>
                                <input type="text" wire:model="propietario"
                                    class="form-input {{ $errors->has('propietario') ? 'ring-2 ring-tierra' : '' }}"
                                    placeholder="Nombre del propietario">
                                @error('propietario') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-tinta">Ubicación <span class="text-tierra">*</span></label>
                                <input type="text" wire:model="ubicacion"
                                    class="form-input {{ $errors->has('ubicacion') ? 'ring-2 ring-tierra' : '' }}"
                                    placeholder="Ubicación del lote">
                                @error('ubicacion') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-tinta">Especie</label>
                                <input type="text" wire:model="especie"
                                    class="form-input {{ $errors->has('especie') ? 'ring-2 ring-tierra' : '' }}"
                                    placeholder="Especie de madera">
                                @error('especie') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-tinta">Superficie (ha)</label>
                                <input type="number" wire:model="superficie" step="0.1" min="0"
                                    class="form-input {{ $errors->has('superficie') ? 'ring-2 ring-tierra' : '' }}"
                                    placeholder="0.00">
                                @error('superficie') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-tinta">Condición de compra</label>
                                <select wire:model="condicion_compra"
                                    class="form-input {{ $errors->has('condicion_compra') ? 'ring-2 ring-tierra' : '' }}">
                                    <option value="">Seleccione...</option>
                                    <option value="propio">Vuelo Forestal</option>
                                    <option value="alquilado">Compra por tonelada</option>
                                </select>
                                @error('condicion_compra') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-tinta">Estado</label>
                                <select wire:model="estado"
                                    class="form-input {{ $errors->has('estado') ? 'ring-2 ring-tierra' : '' }}">
                                    <option value="activo">Activo</option>
                                    <option value="en_proceso">En Explotación</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="cerrado">Cerrado</option>
                                    <option value="baja">Baja</option>
                                </select>
                                @error('estado') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-tinta">Tarea principal <span class="text-tierra">*</span></label>
                            <select wire:model="main_task_type"
                                class="form-input {{ $errors->has('main_task_type') ? 'ring-2 ring-tierra' : '' }}">
                                <option value="">Seleccione...</option>
                                @foreach($this->taskTypes as $tt)
                                    <option value="{{ $tt->value }}" wire:key="option-{{ $tt->value }}">{{ $tt->label() }}</option>
                                @endforeach
                            </select>
                            @error('main_task_type') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                        </div>

                        <x-ui.alert variant="info" dismissible="false">
                            <strong>Coordenadas GPS (Opcional)</strong>
                            <p class="mt-1">
                                Agregue las coordenadas para habilitar pronóstico de lluvia y alertas climáticas.
                                <a href="https://www.google.com/maps" target="_blank" class="font-medium underline hover:text-pino-oscuro">Buscar coordenadas</a>
                            </p>
                        </x-ui.alert>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-tinta">Latitud</label>
                                <input type="number" wire:model="latitud" step="0.00000001" min="-90" max="90"
                                    class="form-input {{ $errors->has('latitud') ? 'ring-2 ring-tierra' : '' }}"
                                    placeholder="-27.469771">
                                @error('latitud') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                                <p class="mt-1 text-xs text-tinta-suave">Ejemplo: -27.469771 (entre -90 y 90)</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-tinta">Longitud</label>
                                <input type="number" wire:model="longitud" step="0.00000001" min="-180" max="180"
                                    class="form-input {{ $errors->has('longitud') ? 'ring-2 ring-tierra' : '' }}"
                                    placeholder="-58.832443">
                                @error('longitud') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                                <p class="mt-1 text-xs text-tinta-suave">Ejemplo: -58.832443 (entre -180 y 180)</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <x-ui.button variant="secondary" icon="x-mark" type="button" wire:click="resetCampos">
                                Cancelar
                            </x-ui.button>
                            @canany(['crear-lotes', 'editar-lotes'])
                            <x-ui.button variant="primary" icon="check" type="submit">
                                {{ $lote_id ? 'Actualizar' : 'Guardar' }}
                            </x-ui.button>
                            @endcanany
                        </div>
                    </form>
                </div>
            </x-ui.card>
        </div>
        @endcanany

        <div x-show="tab === 'listado'" x-transition>
            <x-ui.card class="overflow-hidden">
                <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                    <div class="relative max-w-md">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-tinta-suave">
                            <flux:icon.magnifying-glass class="size-4" />
                        </span>
                        <input type="text" wire:model.live="busqueda"
                            class="form-input pl-10"
                            placeholder="Buscar por propietario, ubicación o especie...">
                    </div>
                </div>

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th>Propietario</th>
                                <th>Ubicación</th>
                                <th>Especie</th>
                                <th class="text-right">Superficie (ha)</th>
                                <th>Coordenadas GPS</th>
                                <th>Condición</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lotes as $lote)
                                <tr wire:key="row-{{ $lote->id_lote }}">
                                    <td class="text-center">
                                        <x-ui.badge variant="neutral">{{ $lote->id_lote }}</x-ui.badge>
                                    </td>
                                    <td class="font-medium text-tinta">{{ $lote->propietario }}</td>
                                    <td class="text-tinta-suave">{{ $lote->ubicacion }}</td>
                                    <td class="text-tinta-suave">{{ $lote->especie ?? '-' }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($lote->superficie ?? 0, 2) }}</td>
                                    <td>
                                        @if($lote->latitud && $lote->longitud)
                                            <a href="https://www.google.com/maps?q={{ $lote->latitud }},{{ $lote->longitud }}" target="_blank"
                                                class="inline-flex items-center gap-2 text-sm text-pino hover:text-pino-oscuro">
                                                <flux:icon.map-pin class="size-4" />
                                                <span class="tabular-nums">{{ number_format($lote->latitud, 6) }}, {{ number_format($lote->longitud, 6) }}</span>
                                            </a>
                                        @else
                                            <span class="text-tinta-suave">Sin coordenadas</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lote->condicion_compra)
                                            @php
                                                $condicionLabel = $lote->condicion_compra === 'propio'
                                                    ? 'Vuelo Forestal'
                                                    : 'Compra por tonelada';
                                            @endphp
                                            <x-ui.badge variant="{{ $lote->condicion_compra == 'propio' ? 'success' : 'info' }}">
                                                {{ $condicionLabel }}
                                            </x-ui.badge>
                                        @else
                                            <span class="text-tinta-suave">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $estadoRaw = $lote->estado;
                                            if (is_object($estadoRaw) && property_exists($estadoRaw, 'value')) {
                                                $estadoRaw = $estadoRaw->value;
                                            }
                                            $estado = strtolower(trim((string) $estadoRaw));
                                            $estado = preg_replace('/\s+/', '_', $estado);
                                            $estado = str_replace('-', '_', $estado);

                                            $estadoVariant = match($estado) {
                                                'activo' => 'success',
                                                'en_proceso' => 'warning',
                                                'cerrado' => 'info',
                                                default => 'neutral',
                                            };

                                            $esActivo = $estado === 'activo';
                                            $esInactivo = $estado === 'inactivo';
                                            $esCerrado = $estado === 'cerrado';
                                            $esEnProceso = $estado === 'en_proceso';
                                        @endphp
                                        <x-ui.badge variant="{{ $estadoVariant }}">
                                            {{ ucfirst(str_replace('_', ' ', $lote->estado)) }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($esCerrado)
                                                <x-ui.badge variant="success" class="h-8 items-center">Finalizado</x-ui.badge>
                                            @elseif($esInactivo)
                                                <x-ui.badge variant="neutral" class="h-8 items-center">Pausado</x-ui.badge>
                                            @else
                                                @can('editar-lotes')
                                                <x-ui.button variant="{{ $esActivo ? 'primary' : 'secondary' }}" size="sm" icon="sparkles"
                                                    href="{{ route('lotes.recomendaciones', ['loteId' => $lote->id_lote]) }}"
                                                    class="w-24">
                                                    Propuestas
                                                </x-ui.button>

                                                @if($esEnProceso)
                                                    <x-ui.button variant="primary" size="sm" icon="flag"
                                                        wire:click="finalizarLote({{ $lote->id_lote }})"
                                                        onclick="return confirm('¿Finalizar este lote? Se liberarán todos los empleados y maquinarias asignadas.')"
                                                        class="w-24">
                                                        Finalizar
                                                    </x-ui.button>
                                                @endif
                                                @endcan
                                            @endif

                                            {{-- Botón de opciones (tres puntos) --}}
                                            @canany(['editar-lotes', 'eliminar-lotes'])
                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open" class="flex h-8 w-8 items-center justify-center rounded-sm text-tinta-suave hover:bg-corteza-suave transition-colors">
                                                    <flux:icon.ellipsis-vertical class="size-5" />
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-transition
                                                    class="absolute right-0 z-20 mt-2 w-44 origin-top-right rounded-sm border border-arena bg-blanco shadow-xl ring-1 ring-black ring-opacity-5">
                                                    <div class="py-1">
                                                        @can('editar-lotes')
                                                        <button wire:click="editar({{ $lote->id_lote }})" onclick="cambiarAPestanaFormulario()"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-tinta hover:bg-corteza-suave">
                                                            <flux:icon.pencil-square class="size-4" /> Editar
                                                        </button>
                                                        @endcan
                                                        @can('eliminar-lotes')
                                                        <button wire:click="eliminar({{ $lote->id_lote }})" onclick="return confirm('¿Está seguro de eliminar este lote?')"
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-tierra hover:bg-tierra-suave">
                                                            <flux:icon.trash class="size-4" /> Eliminar
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
                                    <td colspan="9" class="text-center py-12 text-tinta-suave">
                                        No hay lotes registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                </table>
            </x-ui.table-container>

            <div class="p-4">
                {{ $lotes->links() }}
            </div>
            </x-ui.card>
        </div>
    </div>
    </div>

@push('scripts')
<script>
    // Función para cambiar a pestaña de formulario
    function cambiarAPestanaFormulario() {
        const event = new CustomEvent('cambiarTab', { detail: 'nuevo' });
        window.dispatchEvent(event);
    }
</script>
@endpush

</div>
