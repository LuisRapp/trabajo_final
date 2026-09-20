<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta">Insumos</h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Insumo', 'icon' => 'plus', 'can' => auth()->user()->canAny(['crear-insumos', 'editar-insumos'])],
        ['value' => 'listado', 'label' => 'Listado de Insumos', 'icon' => 'list-bullet'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-insumos', 'editar-insumos'])
            <x-ui.card class="mb-6 overflow-hidden">
                <div class="px-4 py-3 border-b border-arena bg-hueso">
                    <h5 class="text-sm font-semibold text-tinta">
                        {{ $insumo_id ? 'Editar Insumo' : 'Nuevo Insumo' }}
                    </h5>
                </div>
                <div class="p-4">
                    <form wire:submit.prevent="guardar">
                        <div class="mb-4">
                            <label for="nombre" class="block text-xs font-semibold text-tinta mb-1.5">Nombre <span class="text-tierra">*</span></label>
                            <input type="text" id="nombre" wire:model="nombre"
                                class="form-input @error('nombre') border-tierra bg-tierra-suave @enderror"
                                placeholder="Nombre del insumo">
                            @error('nombre') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="id_unidad_medida" class="block text-xs font-semibold text-tinta mb-1.5">Unidad de Medida <span class="text-tierra">*</span></label>
                                <select id="id_unidad_medida" wire:model="id_unidad_medida"
                                    class="form-input @error('id_unidad_medida') border-tierra bg-tierra-suave @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($unidades as $unidad)
                                        <option value="{{ $unidad->id_unidad_medida }}" wire:key="option-{{ $unidad->id_unidad_medida }}">{{ $unidad->nombre }} ({{ $unidad->abreviatura }})</option>
                                    @endforeach
                                </select>
                                @error('id_unidad_medida') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="id_proveedor" class="block text-xs font-semibold text-tinta mb-1.5">Proveedor Principal <span class="text-tierra">*</span></label>
                                <select id="id_proveedor" wire:model="id_proveedor"
                                    class="form-input @error('id_proveedor') border-tierra bg-tierra-suave @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id_proveedor }}" wire:key="option-{{ $proveedor->id_proveedor }}">{{ $proveedor->razon_social }}</option>
                                    @endforeach
                                </select>
                                @error('id_proveedor') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="descripcion" class="block text-xs font-semibold text-tinta mb-1.5">Descripcion</label>
                                <textarea id="descripcion" wire:model="descripcion" rows="1"
                                    class="form-input resize-none @error('descripcion') border-tierra bg-tierra-suave @enderror"
                                    placeholder="Descripcion del insumo"></textarea>
                                @error('descripcion') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <x-ui.alert variant="info" dismissible="false" class="mb-4">
                            Precio promedio calculado automaticamente segun los lotes ingresados.
                        </x-ui.alert>

                        <div class="flex gap-2 justify-end">
                            @if ($insumo_id)
                                <x-ui.button variant="secondary" icon="x-mark" wire:click="resetCampos">
                                    Cancelar
                                </x-ui.button>
                            @endif
                            @canany(['crear-insumos', 'editar-insumos'])
                                <x-ui.button variant="primary" icon="check" type="submit">
                                    {{ $insumo_id ? 'Actualizar' : 'Guardar' }}
                                </x-ui.button>
                            @endcanany
                        </div>
                    </form>
                </div>
            </x-ui.card>
        @endcanany
    @elseif($tab_activo === 'listado')
        <x-ui.card>
            <div class="p-4">
                <x-search-input placeholder="Buscar por nombre, descripcion, proveedor, unidad o costo..." />

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripcion</th>
                                <th>Unidad</th>
                                <th>Proveedor</th>
                                <th class="text-right">Stock Actual</th>
                                <th class="text-right">Precio Promedio</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($insumos as $insumo)
                                <tr wire:key="row-{{ $insumo->id_insumo }}">
                                    <td><x-ui.badge variant="neutral">{{ $insumo->id_insumo }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $insumo->nombre }}</td>
                                    <td class="text-tinta-suave">{{ $insumo->descripcion ?? 'N/A' }}</td>
                                    <td>
                                        @if($insumo->unidadMedida)
                                            <span class="text-tinta-suave">{{ $insumo->unidadMedida->nombre }}</span>
                                            <x-ui.badge variant="info" class="ml-1">{{ $insumo->unidadMedida->abreviatura }}</x-ui.badge>
                                        @else
                                            <span class="text-arena-oscura">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-tinta-suave">{{ $insumo->proveedor?->razon_social ?? 'N/A' }}</td>
                                    <td class="text-right">
                                        <x-ui.badge variant="{{ $insumo->stock > 0 ? 'success' : 'warning' }}">
                                            {{ number_format($insumo->stock, 2) }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="text-right text-tinta-suave">
                                        @if($insumo->precio_promedio > 0)
                                            ${{ number_format($insumo->precio_promedio, 2, ',', '.') }}
                                        @else
                                            <span class="text-arena-oscura">-</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="flex gap-1 justify-end">
                                            @can('editar-insumos')
                                                <x-ui.button variant="secondary" size="sm" icon="pencil-square" wire:click="editar({{ $insumo->id_insumo }})" title="Editar" />
                                            @endcan
                                            @can('eliminar-insumos')
                                                <x-ui.button variant="danger" size="sm" icon="trash" wire:click="eliminar({{ $insumo->id_insumo }})" wire:confirm="¿Esta seguro de eliminar este insumo?" title="Eliminar" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="8" message="No hay insumos registrados." icon="archive-box" />
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $insumos->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
