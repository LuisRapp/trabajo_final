<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.wrench-screwdriver class="size-6" />
            Kits de Mantenimiento Preventivo
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    @if (session()->has('error'))
        <x-ui.alert variant="danger" class="mb-6">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => $editando_kit ? 'Editar Kit' : 'Nuevo Kit', 'icon' => $editando_kit ? 'pencil-square' : 'plus', 'can' => auth()->user()->canAny(['crear-kits-mantenimiento', 'editar-kits-mantenimiento'])],
        ['value' => 'listado', 'label' => 'Listado de Kits', 'icon' => 'list-bullet'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-kits-mantenimiento', 'editar-kits-mantenimiento'])
            <x-ui.card class="mb-6 overflow-hidden">
                <div class="px-4 py-3 border-b border-arena bg-hueso">
                    <h5 class="text-sm font-semibold text-tinta">
                        {{ $editando_kit ? 'Editar Kit' : 'Configurar Kit' }}
                    </h5>
                </div>
                <div class="p-4">
                    <form autocomplete="off" wire:submit.prevent="guardar">
                        <div class="mb-4">
                            <label for="maquinaria_seleccionada" class="block text-xs font-semibold text-tinta mb-1.5">Maquinaria <span class="text-tierra">*</span></label>
                            <select id="maquinaria_seleccionada" wire:model.live="maquinaria_seleccionada"
                                class="form-input max-w-xl">
                                <option value="">Seleccione...</option>
                                @foreach($maquinarias as $maquinaria)
                                    <option value="{{ $maquinaria->id_maquinaria }}" wire:key="maquinaria-{{ $maquinaria->id_maquinaria }}">
                                        {{ $maquinaria->modelo }} ({{ optional($maquinaria->tipoMaquinaria)->nombre ?? 'Sin tipo' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if(!$maquinaria_seleccionada)
                            <x-ui.alert variant="info" :dismissible="false">
                                Seleccione una maquinaria para configurar su kit de mantenimiento preventivo.
                            </x-ui.alert>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label for="insumo_id" class="block text-xs font-semibold text-tinta mb-1.5">Insumo <span class="text-tierra">*</span></label>
                                    <select id="insumo_id" wire:model="insumo_id"
                                        class="form-input @error('insumo_id') border-tierra bg-tierra-suave @enderror">
                                        <option value="">Seleccione...</option>
                                        @foreach($insumos as $insumo)
                                            <option value="{{ $insumo->id_insumo }}" wire:key="insumo-{{ $insumo->id_insumo }}">
                                                {{ $insumo->nombre }} (Stock: {{ number_format($insumo->stock ?? 0, 2) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('insumo_id') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="cantidad_requerida" class="block text-xs font-semibold text-tinta mb-1.5">Cantidad Requerida <span class="text-tierra">*</span></label>
                                    <input autocomplete="off" type="number" id="cantidad_requerida" wire:model="cantidad_requerida" step="0.01" min="0.01"
                                        class="form-input @error('cantidad_requerida') border-tierra bg-tierra-suave @enderror"
                                        placeholder="Ej: 10.00">
                                    @error('cantidad_requerida') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="es_obligatorio" class="block text-xs font-semibold text-tinta mb-1.5">Tipo</label>
                                    <select id="es_obligatorio" wire:model="es_obligatorio" class="form-input">
                                        <option value="1">Obligatorio</option>
                                        <option value="0">Opcional</option>
                                    </select>
                                    <small class="text-tinta-suave text-xs mt-1 block">
                                        Los insumos obligatorios deben tener stock para aprobar el mantenimiento.
                                    </small>
                                </div>
                            </div>

                            <div class="flex gap-2 justify-end">
                                @if ($item_id)
                                    <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="cancelarEdicionItem">
                                        Cancelar
                                    </x-ui.button>
                                @endif
                                <x-ui.button variant="primary" icon="check" type="submit">
                                    {{ $item_id ? 'Actualizar' : 'Guardar' }}
                                </x-ui.button>
                            </div>
                        @endif
                    </form>

                    @if($maquinaria_seleccionada)
                        <div class="mt-6 border-t border-arena pt-4">
                            <h6 class="mb-3 flex items-center gap-2 text-sm font-semibold text-tinta">
                                <flux:icon.list-bullet class="size-4" />
                                Insumos del Kit
                                @if($items_count > 0)
                                    <x-ui.badge variant="info">{{ $items_count }}</x-ui.badge>
                                    <x-ui.badge variant="danger">{{ $items_obligatorios }} obligatorios</x-ui.badge>
                                    <x-ui.badge variant="{{ $items_con_stock === $items_count ? 'success' : 'warning' }}">Stock OK {{ $items_con_stock }}/{{ $items_count }}</x-ui.badge>
                                @endif
                            </h6>

                            @if(count($items) > 0)
                                <x-ui.table-container>
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 8%;">ID</th>
                                                <th style="width: 34%;">Insumo</th>
                                                <th style="width: 14%;">Cantidad</th>
                                                <th style="width: 16%;">Stock</th>
                                                <th style="width: 14%;">Tipo</th>
                                                <th style="width: 14%;" class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $item)
                                                @php
                                                    $insumo = optional($item->insumo);
                                                    $stock = is_numeric($insumo->stock ?? null) ? $insumo->stock : 0;
                                                @endphp
                                                <tr wire:key="item-{{ $item->id_kit }}">
                                                    <td><x-ui.badge variant="neutral">{{ $item->id_kit }}</x-ui.badge></td>
                                                    <td class="font-semibold text-tinta">{{ $insumo->nombre ?? '—' }}</td>
                                                    <td class="text-tinta-suave">{{ number_format($item->cantidad_requerida, 2) }}</td>
                                                    <td>
                                                        <x-ui.badge variant="{{ $stock >= $item->cantidad_requerida ? 'success' : 'danger' }}">
                                                            {{ number_format($stock, 2) }}
                                                        </x-ui.badge>
                                                        @if($stock < $item->cantidad_requerida)
                                                            <br><small class="text-tierra text-xs">Faltan {{ number_format($item->cantidad_requerida - $stock, 2) }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <x-ui.badge variant="{{ $item->es_obligatorio ? 'danger' : 'neutral' }}">
                                                            {{ $item->es_obligatorio ? 'Obligatorio' : 'Opcional' }}
                                                        </x-ui.badge>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="flex gap-1 justify-center">
                                                            @can('editar-kits-mantenimiento')
                                                                <x-ui.button size="sm" variant="secondary" icon="pencil-square" wire:click="editarItem({{ $item->id_kit }})" title="Editar" />
                                                            @endcan
                                                            @can('eliminar-kits-mantenimiento')
                                                                <x-ui.button size="sm" variant="danger" icon="trash" wire:click="eliminar({{ $item->id_kit }})" wire:confirm="¿Quitar este insumo del kit?" title="Quitar" />
                                                            @endcan
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </x-ui.table-container>
                            @else
                                <p class="py-6 text-center text-sm text-tinta-suave rounded-sm bg-hueso border border-arena">
                                    El kit de esta maquinaria todavía no tiene insumos.
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </x-ui.card>
        @endcanany
    @elseif($tab_activo === 'listado')
        <x-ui.card>
            <div class="p-4">
                @if(count($kits_registrados) > 0)
                    <x-ui.table-container>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Maquinaria</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Insumos</th>
                                    <th class="text-center">Obligatorios</th>
                                    <th class="text-center">Opcionales</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kits_registrados as $maquinariaId => $kit)
                                    <tr wire:key="kit-{{ $maquinariaId }}">
                                        <td class="font-semibold text-tinta">{{ optional($kit['maquinaria'])->modelo ?? '—' }}</td>
                                        <td class="text-tinta-suave">{{ optional(optional($kit['maquinaria'])->tipoMaquinaria)->nombre ?? '—' }}</td>
                                        <td class="text-center"><x-ui.badge variant="info">{{ $kit['total_items'] }}</x-ui.badge></td>
                                        <td class="text-center"><x-ui.badge variant="danger">{{ $kit['obligatorios'] }}</x-ui.badge></td>
                                        <td class="text-center"><x-ui.badge variant="neutral">{{ $kit['opcionales'] }}</x-ui.badge></td>
                                        <td class="text-center">
                                            <div class="flex gap-1 justify-center">
                                                @can('editar-kits-mantenimiento')
                                                    <x-ui.button size="sm" variant="secondary" icon="pencil-square" wire:click="editarKit({{ $maquinariaId }})" title="Editar kit" />
                                                @endcan
                                                @can('eliminar-kits-mantenimiento')
                                                    <x-ui.button size="sm" variant="danger" icon="trash" wire:click="eliminarKit({{ $maquinariaId }})" wire:confirm="¿Eliminar el kit completo de esta maquinaria?" title="Eliminar kit" />
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-ui.table-container>
                @else
                    <div class="py-12 text-center rounded-sm bg-hueso border border-arena">
                        <p class="mb-4 font-medium text-tinta-suave">No hay kits configurados todavía</p>
                        @canany(['crear-kits-mantenimiento', 'editar-kits-mantenimiento'])
                            <x-ui.button type="button" icon="plus" wire:click="$set('tab_activo','nuevo')">
                                Crear Primer Kit
                            </x-ui.button>
                        @endcanany
                    </div>
                @endif
            </div>
        </x-ui.card>
    @endif
</div>
