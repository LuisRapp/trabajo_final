<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.wrench-screwdriver class="size-6" />
            Kits de Mantenimiento Preventivo
        </h1>
        <p class="mt-1 text-sm text-tinta-suave">
            Definí qué insumos necesita cada maquinaria para su mantenimiento preventivo.
        </p>
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

    @if ($tab_activo === 'nuevo')
        @canany(['crear-kits-mantenimiento', 'editar-kits-mantenimiento'])
            <x-ui.card>
                <div class="flex items-center justify-between gap-3 border-b border-arena bg-hueso px-4 py-3">
                    <h5 class="text-sm font-semibold text-tinta">
                        @if ($editando_kit && $maquinaria_kit)
                            Kit de {{ $maquinaria_kit->modelo }} ({{ optional($maquinaria_kit->tipoMaquinaria)->nombre ?? 'Sin tipo' }})
                        @else
                            Configurar un kit nuevo
                        @endif
                    </h5>

                    @if ($maquinaria_seleccionada)
                        <span class="text-xs text-tinta-suave">
                            {{ $items_count }} {{ $items_count === 1 ? 'insumo' : 'insumos' }} en el kit
                        </span>
                    @endif
                </div>

                <div class="p-4">
                    <form autocomplete="off" wire:submit.prevent="guardar">
                        <div class="mb-4">
                            <label for="maquinaria_seleccionada" class="mb-1.5 block text-xs font-semibold text-tinta">
                                Maquinaria <span class="text-tierra">*</span>
                            </label>
                            <select
                                id="maquinaria_seleccionada"
                                wire:model.live="maquinaria_seleccionada"
                                class="form-input max-w-xl"
                            >
                                <option value="">Seleccione...</option>
                                @foreach ($maquinarias as $maquinaria)
                                    <option value="{{ $maquinaria->id_maquinaria }}" wire:key="maquinaria-{{ $maquinaria->id_maquinaria }}">
                                        {{ $maquinaria->modelo }} ({{ optional($maquinaria->tipoMaquinaria)->nombre ?? 'Sin tipo' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if (!$maquinaria_seleccionada)
                            <x-ui.alert variant="info" :dismissible="false">
                                Seleccioná una maquinaria para configurar su kit de mantenimiento preventivo.
                            </x-ui.alert>
                        @else
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label for="insumo_id" class="mb-1.5 block text-xs font-semibold text-tinta">
                                        Insumo <span class="text-tierra">*</span>
                                    </label>
                                    <select
                                        id="insumo_id"
                                        wire:model="insumo_id"
                                        class="form-input @error('insumo_id') border-tierra bg-tierra-suave @enderror"
                                    >
                                        <option value="">Seleccione...</option>
                                        @foreach ($insumos as $insumo)
                                            <option value="{{ $insumo->id_insumo }}" wire:key="insumo-{{ $insumo->id_insumo }}">
                                                {{ $insumo->nombre }} (Stock: {{ number_format($insumo->stock ?? 0, 2) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('insumo_id')
                                        <p class="mt-1 text-xs text-tierra">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="cantidad_requerida" class="mb-1.5 block text-xs font-semibold text-tinta">
                                        Cantidad requerida <span class="text-tierra">*</span>
                                    </label>
                                    <input
                                        autocomplete="off"
                                        type="number"
                                        id="cantidad_requerida"
                                        wire:model="cantidad_requerida"
                                        step="0.01"
                                        min="0.01"
                                        placeholder="Ej: 10.00"
                                        class="form-input @error('cantidad_requerida') border-tierra bg-tierra-suave @enderror"
                                    />
                                    @error('cantidad_requerida')
                                        <p class="mt-1 text-xs text-tierra">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <span class="mb-1.5 block text-xs font-semibold text-tinta">Tipo</span>
                                    <label class="flex cursor-pointer items-start gap-2.5 rounded-sm border border-arena px-3 py-2.5 transition-colors hover:bg-corteza-suave">
                                        <input
                                            type="checkbox"
                                            id="es_obligatorio"
                                            wire:model="es_obligatorio"
                                            class="mt-0.5 size-4 cursor-pointer rounded-sm border-arena accent-pino"
                                        />
                                        <span class="text-sm text-tinta">
                                            Obligatorio
                                            <span class="mt-0.5 block text-xs leading-snug text-tinta-suave">
                                                Se descuenta del stock al aprobar el mantenimiento.
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end gap-2">
                                @if ($item_id)
                                    <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="cancelarEdicionItem">
                                        Cancelar
                                    </x-ui.button>
                                @endif
                                <x-ui.button variant="primary" icon="check" type="submit">
                                    {{ $item_id ? 'Actualizar' : 'Guardar' }}
                                </x-ui.button>
                            </div>

                            <div class="mt-6 border-t border-arena pt-4">
                                <div class="mb-3 flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                    <h6 class="flex items-center gap-2 text-sm font-semibold text-tinta">
                                        <flux:icon.list-bullet class="size-4" />
                                        Insumos del kit
                                    </h6>

                                    @if ($items_count > 0)
                                        <p class="text-xs text-tinta-suave">
                                            {{ $items_count }} {{ $items_count === 1 ? 'insumo' : 'insumos' }}
                                            &middot; {{ $items_obligatorios }} obligatorios
                                            &middot; {{ $items_con_stock }} con stock suficiente
                                        </p>
                                    @endif
                                </div>

                                @if ($items_count > 0)
                                    <x-ui.table-container>
                                        <table class="data-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 38%;">Insumo</th>
                                                    <th style="width: 16%;" class="text-right">Requerido</th>
                                                    <th style="width: 20%;" class="text-right">Stock</th>
                                                    <th style="width: 14%;">Tipo</th>
                                                    <th style="width: 12%;" class="text-right">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($items as $item)
                                                    @php
                                                        $insumo = optional($item->insumo);
                                                        $stock = is_numeric($insumo->stock ?? null) ? (float) $insumo->stock : 0.0;
                                                        $faltante = max($item->cantidad_requerida - $stock, 0);
                                                    @endphp
                                                    <tr wire:key="item-{{ $item->id_kit }}">
                                                        <td class="font-medium text-tinta">
                                                            {{ $insumo->nombre ?? '—' }}
                                                        </td>
                                                        <td class="text-right text-tinta-suave">
                                                            {{ number_format($item->cantidad_requerida, 2) }}
                                                            <span class="text-arena-oscura">{{ optional($insumo->unidadMedida)->abreviatura ?? '' }}</span>
                                                        </td>
                                                        <td class="text-right">
                                                            <x-ui.badge variant="{{ $faltante > 0 ? 'danger' : 'success' }}">
                                                                {{ number_format($stock, 2) }}
                                                            </x-ui.badge>
                                                            @if ($faltante > 0)
                                                                <span class="ml-1 text-xs text-tierra">faltan {{ number_format($faltante, 2) }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <x-ui.badge variant="{{ $item->es_obligatorio ? 'danger' : 'neutral' }}">
                                                                {{ $item->es_obligatorio ? 'Obligatorio' : 'Opcional' }}
                                                            </x-ui.badge>
                                                        </td>
                                                        <td>
                                                            <div class="flex justify-end gap-1">
                                                                @can('editar-kits-mantenimiento')
                                                                    <x-ui.button size="sm" variant="secondary" icon="pencil-square" wire:click="editarItem({{ $item->id_kit }})" title="Editar" />
                                                                @endcan
                                                                @can('eliminar-kits-mantenimiento')
                                                                    <x-ui.confirmador
                                                                        accion="eliminar"
                                                                        :parametros="[$item->id_kit]"
                                                                        variante="danger"
                                                                        mensaje="¿Quitar «{{ $insumo->nombre }}» del kit?"
                                                                        detalle="El insumo deja de formar parte de este kit. No se borra del inventario."
                                                                        tituloBoton="Quitar del kit"
                                                                    />
                                                                @endcan
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </x-ui.table-container>
                                @else
                                    <p class="rounded-sm border border-arena bg-hueso py-6 text-center text-sm text-tinta-suave">
                                        El kit de esta maquinaria todavía no tiene insumos.
                                    </p>
                                @endif
                            </div>
                        @endif
                    </form>
                </div>
            </x-ui.card>
        @endcanany
    @elseif ($tab_activo === 'listado')
        <x-ui.card>
            <div class="flex items-center justify-between gap-3 border-b border-arena bg-hueso px-4 py-3">
                <h5 class="text-sm font-semibold text-tinta">Kits configurados</h5>

                @if (count($kits_registrados) > 0)
                    <x-ui.badge variant="neutral">{{ count($kits_registrados) }}</x-ui.badge>
                @endif
            </div>

            <div class="p-4">
                @if (count($kits_registrados) > 0)
                    <x-ui.table-container>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 36%;">Maquinaria</th>
                                    <th style="width: 16%;" class="text-right">Insumos</th>
                                    <th style="width: 16%;" class="text-right">Obligatorios</th>
                                    <th style="width: 16%;" class="text-right">Opcionales</th>
                                    <th style="width: 16%;" class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($kits_registrados as $maquinariaId => $kit)
                                    @php
                                        $maquinaria = $kit['maquinaria'];
                                    @endphp
                                    <tr wire:key="kit-{{ $maquinariaId }}">
                                        <td>
                                            <span class="font-medium text-tinta">{{ optional($maquinaria)->modelo ?? '—' }}</span>
                                            <span class="mt-0.5 block text-xs text-tinta-suave">
                                                {{ optional(optional($maquinaria)->tipoMaquinaria)->nombre ?? 'Sin tipo' }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <x-ui.badge variant="info">{{ $kit['total_items'] }}</x-ui.badge>
                                        </td>
                                        <td class="text-right">{{ $kit['obligatorios'] }}</td>
                                        <td class="text-right">{{ $kit['opcionales'] }}</td>
                                        <td>
                                            <div class="flex justify-end gap-1">
                                                @can('editar-kits-mantenimiento')
                                                    <x-ui.button size="sm" variant="secondary" icon="pencil-square" wire:click="editarKit({{ $maquinariaId }})" title="Editar kit" />
                                                @endcan
                                                @can('eliminar-kits-mantenimiento')
                                                    <x-ui.confirmador
                                                        accion="eliminarKit"
                                                        :parametros="[$maquinariaId]"
                                                        variante="danger"
                                                        mensaje="¿Eliminar el kit completo de «{{ optional($maquinaria)->modelo ?? 'la maquinaria' }}»?"
                                                        detalle="Se quitarán los {{ $kit['total_items'] }} insumos configurados. La maquinaria no se elimina."
                                                    />
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-ui.table-container>
                @else
                    <div class="rounded-sm border border-arena bg-hueso py-12 text-center">
                        <p class="font-medium text-tinta-suave">Todavía no hay kits configurados.</p>
                        <p class="mt-1 text-xs text-arena-oscura">Creá el primero para que el mantenimiento preventivo sepa qué insumos descontar.</p>
                    </div>
                @endif
            </div>
        </x-ui.card>
    @endif
</div>
