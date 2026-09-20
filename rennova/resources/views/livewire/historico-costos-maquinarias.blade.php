<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.chart-bar class="size-6" />
            Historico de Costos de Maquinarias
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <div class="mb-6 flex gap-0">
        @canany(['crear-historico-costos-maquinarias', 'editar-historico-costos-maquinarias'])
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-sm transition-all {{ $tab_activo === 'nuevo' ? 'bg-pino text-white border-pino' : 'bg-white text-tinta-suave border-arena hover:bg-corteza-suave' }}">
            <flux:icon.plus class="size-4" />
            Nuevo Historico
        </button>
        @endcanany
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-sm transition-all {{ $tab_activo === 'listado' ? 'bg-pino text-white border-pino' : 'bg-white text-tinta-suave border-arena hover:bg-corteza-suave' }} {{ !auth()->user()->canAny(['crear-historico-costos-maquinarias', 'editar-historico-costos-maquinarias']) ? 'rounded-l-sm' : '' }}">
            <flux:icon.list-bullet class="size-4" />
            Listado de Historicos
        </button>
    </div>

    @if($tab_activo === 'nuevo')
        @canany(['crear-historico-costos-maquinarias', 'editar-historico-costos-maquinarias'])
        <x-ui.card class="mb-6 overflow-hidden">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="text-lg font-semibold text-tinta">
                    @if($historico_id)
                        <flux:icon.pencil-square class="size-5 inline" />
                        Editar Historico
                    @else
                        <flux:icon.plus class="size-5 inline" />
                        Nuevo Historico
                    @endif
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="id_maquinaria" class="block text-sm font-semibold text-tinta mb-1.5">Maquinaria <span class="text-tierra">*</span></label>
                            <select id="id_maquinaria" wire:model="id_maquinaria"
                                class="form-input @error('id_maquinaria') border-tierra bg-tierra-suave @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($maquinarias as $maq)
                                    <option value="{{ $maq->id_maquinaria }}" wire:key="option-{{ $maq->id_maquinaria }}">{{ $maq->modelo }}</option>
                                @endforeach
                            </select>
                            @error('id_maquinaria') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="costo_por_tonelada" class="block text-sm font-semibold text-tinta mb-1.5">Costo por Tonelada <span class="text-tierra">*</span></label>
                            <input type="number" id="costo_por_tonelada" wire:model="costo_por_tonelada" step="0.01"
                                class="form-input @error('costo_por_tonelada') border-tierra bg-tierra-suave @enderror"
                                placeholder="0.00">
                            @error('costo_por_tonelada') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="fecha_inicio_vigencia" class="block text-sm font-semibold text-tinta mb-1.5">Fecha Inicio Vigencia <span class="text-tierra">*</span></label>
                            <input type="date" id="fecha_inicio_vigencia" wire:model="fecha_inicio_vigencia"
                                class="form-input @error('fecha_inicio_vigencia') border-tierra bg-tierra-suave @enderror">
                            @error('fecha_inicio_vigencia') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_fin_vigencia" class="block text-sm font-semibold text-tinta mb-1.5">Fecha Fin Vigencia</label>
                            <input type="date" id="fecha_fin_vigencia" wire:model="fecha_fin_vigencia"
                                class="form-input @error('fecha_fin_vigencia') border-tierra bg-tierra-suave @enderror">
                            @error('fecha_fin_vigencia') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            <small class="text-tinta-suave text-xs mt-1 block">Opcional — dejar en blanco si esta vigente actualmente</small>
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($historico_id)
                            <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="resetCampos">
                                Cancelar
                            </x-ui.button>
                        @endif
                        @canany(['crear-historico-costos-maquinarias', 'editar-historico-costos-maquinarias'])
                        <x-ui.button type="submit" icon="check">
                            {{ $historico_id ? 'Actualizar' : 'Guardar' }}
                        </x-ui.button>
                        @endcanany
                    </div>
                </form>
            </div>
        </x-ui.card>
        @endcanany
    @else
        <x-ui.card>
            <div class="p-6">
                <div class="mb-6">
                    <div class="flex items-center gap-2 px-4 py-2.5 border border-arena rounded-sm bg-hueso">
                        <flux:icon.magnifying-glass class="size-4 text-arena-oscura" />
                        <input type="text" wire:model.live="busqueda" placeholder="Buscar por modelo de maquinaria..."
                            class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-sm text-tinta placeholder-arena-oscura">
                    </div>
                </div>

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Maquinaria</th>
                                <th class="text-right">Costo/Ton</th>
                                <th>Inicio Vig.</th>
                                <th>Fin Vig.</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historicos as $historico)
                                <tr wire:key="row-{{ $historico->id_costo }}">
                                    <td><x-ui.badge variant="neutral">{{ $historico->id_costo }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $historico->maquinaria->modelo ?? 'N/A' }}</td>
                                    <td class="text-right text-tinta-suave">${{ number_format($historico->costo_por_tonelada, 2, ',', '.') }}</td>
                                    <td class="text-tinta-suave">{{ $historico->fecha_inicio_vigencia ? \Carbon\Carbon::parse($historico->fecha_inicio_vigencia)->format('d/m/Y') : '-' }}</td>
                                    <td class="text-tinta-suave">{{ $historico->fecha_fin_vigencia ? \Carbon\Carbon::parse($historico->fecha_fin_vigencia)->format('d/m/Y') : 'Vigente' }}</td>
                                    <td class="text-right">
                                        <div class="flex gap-1 justify-end">
                                            @can('editar-historico-costos-maquinarias')
                                                <x-ui.button size="sm" variant="secondary" icon="pencil-square" wire:click="editar({{ $historico->id_costo }})" title="Editar" />
                                            @endcan
                                            @can('eliminar-historico-costos-maquinarias')
                                                <x-ui.button size="sm" variant="danger" icon="trash" wire:click="eliminar({{ $historico->id_costo }})" wire:confirm="¿Eliminar este historico?" title="Eliminar" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-tinta-suave">
                                        No hay historicos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $historicos->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
