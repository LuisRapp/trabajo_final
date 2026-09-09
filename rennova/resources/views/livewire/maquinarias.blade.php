<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.truck class="size-6" />
            Maquinarias
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <div class="mb-6 flex gap-0">
        @canany(['crear-maquinarias', 'editar-maquinarias'])
        <button type="button" wire:click="$set('tab_activo', 'nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 text-sm font-semibold border border-r-0 transition-all rounded-l-sm {{ $tab_activo === 'nuevo' ? 'bg-pino text-white border-pino' : 'bg-white text-tinta-suave border-arena hover:bg-corteza-suave' }}">
            <flux:icon.plus class="size-4" />
            Nueva Maquinaria
        </button>
        @endcanany
        <button type="button" wire:click="$set('tab_activo', 'listado')"
            class="inline-flex items-center gap-2 px-4 py-3 text-sm font-semibold border transition-all {{ $tab_activo === 'listado' ? 'bg-pino text-white border-pino' : 'bg-white text-tinta-suave border-arena hover:bg-corteza-suave' }} {{ !auth()->user()->canAny(['crear-maquinarias', 'editar-maquinarias']) ? 'rounded-l-sm' : '' }} rounded-r-sm">
            <flux:icon.list-bullet class="size-4" />
            Listado de Maquinarias
        </button>
    </div>

    @if($tab_activo === 'nuevo')
        @canany(['crear-maquinarias', 'editar-maquinarias'])
        <x-ui.card class="mb-6 overflow-hidden">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="flex items-center gap-2 text-lg font-semibold text-tinta">
                    @if($maquinaria_id)
                        <flux:icon.pencil-square class="size-5" />
                        Editar Maquinaria
                    @else
                        <flux:icon.plus class="size-5" />
                        Nueva Maquinaria
                    @endif
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="id_tipo_maquinaria" class="block text-sm font-semibold text-tinta mb-1.5">Tipo de Maquinaria <span class="text-tierra">*</span></label>
                            <select id="id_tipo_maquinaria" wire:model="id_tipo_maquinaria"
                                class="form-input @error('id_tipo_maquinaria') border-tierra bg-tierra-suave @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo->id_tipo_maquinaria }}" wire:key="option-{{ $tipo->id_tipo_maquinaria }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            @error('id_tipo_maquinaria') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="modelo" class="block text-sm font-semibold text-tinta mb-1.5">Modelo <span class="text-tierra">*</span></label>
                            <input type="text" id="modelo" wire:model="modelo"
                                class="form-input @error('modelo') border-tierra bg-tierra-suave @enderror"
                                placeholder="Modelo de la maquinaria">
                            @error('modelo') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="estado" class="block text-sm font-semibold text-tinta mb-1.5">Estado <span class="text-tierra">*</span></label>
                            <select id="estado" wire:model="estado"
                                class="form-input @error('estado') border-tierra bg-tierra-suave @enderror">
                                <option value="">Seleccione...</option>
                                <option value="disponible">Disponible</option>
                                <option value="asignada">Asignada</option>
                                <option value="en_mantenimiento">En Mantenimiento</option>
                            </select>
                            @error('estado') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="es_alquilada" class="block text-sm font-semibold text-tinta mb-1.5">Es alquilada?</label>
                            <select id="es_alquilada" wire:model="es_alquilada" class="form-input">
                                <option value="0">No</option>
                                <option value="1">Si</option>
                            </select>
                        </div>
                        <div>
                            <label for="fecha_inicio_actividades" class="block text-sm font-semibold text-tinta mb-1.5">Fecha Inicio Actividades <span class="text-tierra">*</span></label>
                            <input type="date" id="fecha_inicio_actividades" wire:model="fecha_inicio_actividades"
                                class="form-input @error('fecha_inicio_actividades') border-tierra bg-tierra-suave @enderror">
                            @error('fecha_inicio_actividades') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="umbral_toneladas" class="block text-sm font-semibold text-tinta mb-1.5">Toneladas Mantenimiento</label>
                            <input type="number" id="umbral_toneladas" wire:model="umbral_toneladas" step="0.01"
                                class="form-input @error('umbral_toneladas') border-tierra bg-tierra-suave @enderror"
                                placeholder="Ej: 1000">
                            @error('umbral_toneladas') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            <small class="text-tinta-suave text-xs mt-1 block">
                                Opcional. Al alcanzar este acumulado de toneladas se generara una orden de mantenimiento preventivo.
                            </small>
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($maquinaria_id)
                            <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="resetCampos">
                                Cancelar
                            </x-ui.button>
                        @endif
                        @canany(['crear-maquinarias', 'editar-maquinarias'])
                        <x-ui.button type="submit" icon="check">
                            {{ $maquinaria_id ? 'Actualizar' : 'Guardar' }}
                        </x-ui.button>
                        @endcanany
                    </div>
                </form>
            </div>
        </x-ui.card>
        @endcanany
    @elseif($tab_activo === 'listado')
        <x-ui.card>
            <div class="p-6">
                <div class="mb-6">
                    <div class="flex items-center gap-2 px-4 py-2.5 border border-arena rounded-sm bg-hueso">
                        <flux:icon.magnifying-glass class="size-4 text-arena-oscura" />
                        <input type="text" wire:model.live="busqueda" placeholder="Buscar por tipo, modelo o estado..."
                            class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-sm text-tinta placeholder-arena-oscura">
                    </div>
                </div>

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Modelo</th>
                                <th>Estado</th>
                                <th>Alquilada</th>
                                <th class="text-right">Toneladas Mantenimiento (t)</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($maquinarias as $maquinaria)
                                <tr wire:key="row-{{ $maquinaria->id_maquinaria }}">
                                    <td><x-ui.badge variant="neutral">{{ $maquinaria->id_maquinaria }}</x-ui.badge></td>
                                    <td class="text-tinta-suave">{{ $maquinaria->tipoMaquinaria->nombre ?? 'N/A' }}</td>
                                    <td class="font-medium text-tinta">{{ $maquinaria->modelo }}</td>
                                    <td>
                                        @php
                                            $estadoVariant = match($maquinaria->estado) {
                                                'disponible' => 'success',
                                                'asignada' => 'info',
                                                'en_mantenimiento' => 'warning',
                                                default => 'neutral',
                                            };
                                        @endphp
                                        <x-ui.badge variant="{{ $estadoVariant }}">{{ ucfirst($maquinaria->estado) }}</x-ui.badge>
                                    </td>
                                    <td class="text-tinta-suave">{{ $maquinaria->es_alquilada ? 'Si' : 'No' }}</td>
                                    <td class="text-right text-tinta-suave">{{ number_format($maquinaria->umbral_toneladas, 2, ',', '.') }}</td>
                                    <td class="text-right">
                                        <div class="flex gap-1 justify-end">
                                            @can('editar-maquinarias')
                                                <x-ui.button size="sm" variant="secondary" icon="pencil-square" wire:click="editar({{ $maquinaria->id_maquinaria }})" title="Editar" />
                                            @endcan
                                            @can('eliminar-maquinarias')
                                                <x-ui.button size="sm" variant="danger" icon="trash" wire:click="eliminar({{ $maquinaria->id_maquinaria }})" wire:confirm="¿Esta seguro de eliminar esta maquinaria?" title="Eliminar" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-tinta-suave">
                                        No hay maquinarias registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $maquinarias->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
