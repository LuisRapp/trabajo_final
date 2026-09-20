<div x-data="{
    currentPageInsumos: 1,
    currentPageHistorial: 1,
    itemsPerPage: 5
}" class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
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

    <div class="mb-6 flex gap-0">
        <button type="button" wire:click="$set('activeTab','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-sm transition-all {{ $activeTab === 'nuevo' ? 'text-white bg-pino border-pino' : 'bg-white text-tinta-suave border-arena hover:bg-corteza-suave' }}">
            @if($editando_kit)
                <flux:icon.pencil-square class="size-4" />
                Editar Kit
            @else
                <flux:icon.plus class="size-4" />
                Nuevo Kit
            @endif
        </button>
        <button type="button" wire:click="$set('activeTab','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-sm transition-all {{ $activeTab === 'listado' ? 'text-white bg-pino border-pino' : 'bg-white text-tinta-suave border-arena hover:bg-corteza-suave' }}">
            <flux:icon.list-bullet class="size-4" />
            Listado de Kits
        </button>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @if($activeTab === 'nuevo')
            <x-ui.card class="overflow-hidden">
                <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-tinta mb-0">
                        @if($editando_kit)
                            <flux:icon.pencil-square class="size-5" />
                            Editar Kit
                        @else
                            <flux:icon.plus class="size-5" />
                            Configurar Kit
                        @endif
                    </h5>
                </div>
                <div class="p-6">
                    <div class="mb-6">
                        <label for="maquinaria_select" class="block text-sm font-semibold text-tinta mb-2">
                            <flux:icon.truck class="size-4 inline mr-1" />
                            Maquinaria <span class="text-tierra">*</span>
                        </label>
                        <select wire:model.live="maquinaria_seleccionada" id="maquinaria_select" class="form-input @error('maquinaria_seleccionada') ring-2 ring-tierra @enderror" @if($editando_kit) disabled @endif>
                            <option value="">Seleccione una maquinaria</option>
                            @foreach ($maquinarias as $maq)
                                <option value="{{ $maq->id_maquinaria }}" wire:key="option-{{ $maq->id_maquinaria }}">{{ $maq->modelo }} ({{ $maq->tipoMaquinaria ? $maq->tipoMaquinaria->nombre : 'Sin tipo' }})</option>
                            @endforeach
                        </select>
                        @error('maquinaria_seleccionada') <p class="mt-1 text-sm text-tierra">{{ $message }}</p> @enderror
                    </div>

                    @if(!$maquinaria_seleccionada)
                        <div class="flex flex-col items-center justify-center py-12 rounded-sm bg-pino-suave border border-pino/30">
                            <flux:icon.arrow-up class="size-10 text-pino mb-3" />
                            <p class="text-pino font-medium">Seleccione una maquinaria para configurar su kit de mantenimiento preventivo</p>
                        </div>
                    @else
                        <div class="flex gap-2 mb-6">
                            <x-ui.button type="button" icon="plus" wire:click="abrirModalAgregar">
                                Agregar Insumo
                            </x-ui.button>
                            @if($editando_kit)
                                <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="limpiarKit">
                                    Cancelar Edicion
                                </x-ui.button>
                            @else
                                @if($items_count > 0)
                                    <x-ui.button type="button" icon="check" wire:click="registrarKit">
                                        Registrar Kit
                                    </x-ui.button>
                                    <x-ui.button type="button" variant="danger" icon="trash" wire:click="limpiarKit">
                                        Limpiar Todo
                                    </x-ui.button>
                                @endif
                            @endif
                        </div>

                        @if($kit_modificado && !$editando_kit && $items_count > 0)
                            <x-ui.alert variant="warning" class="mb-6" :dismissible="false">
                                Hay cambios sin guardar. Haga clic en <strong>Registrar Kit</strong> para confirmar.
                            </x-ui.alert>
                        @endif

                        <div class="mb-6">
                            <h6 class="mb-4 font-semibold text-tinta flex items-center gap-2">
                                <flux:icon.list-bullet class="size-4" />
                                Insumos del Kit
                                @if($items_count > 0)
                                    <x-ui.badge variant="info">{{ $items_count }}</x-ui.badge>
                                @endif
                            </h6>

                            @if(count($items) > 0)
                                <x-ui.table-container>
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 8%;">ID</th>
                                                <th style="width: 30%;">Insumo</th>
                                                <th style="width: 15%;">Cantidad</th>
                                                <th style="width: 15%;">Stock</th>
                                                <th style="width: 15%;">Tipo</th>
                                                <th style="width: 17%;" class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $item)
                                                @php
                                                    $ins = optional($item->insumo);
                                                    $stock = is_numeric($ins->stock ?? null) ? $ins->stock : 0;
                                                @endphp
                                                <tr wire:key="row-{{ $item->id_kit ?? $item->id }}">
                                                    <td><x-ui.badge variant="neutral">{{ $item->id_kit ?? $item->id }}</x-ui.badge></td>
                                                    <td class="font-semibold text-tinta">{{ $ins->nombre ?? '—' }}</td>
                                                    <td class="text-tinta-suave">{{ number_format($item->cantidad_requerida, 2) }}</td>
                                                    <td>
                                                        <x-ui.badge variant="{{ ($stock >= $item->cantidad_requerida) ? 'success' : 'danger' }}">{{ number_format($stock, 2) }}</x-ui.badge>
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
                                                            <x-ui.button size="sm" variant="secondary" icon="pencil-square" wire:click="abrirModalEditar({{ $item->id_kit ?? $item->id }})" title="Editar" />
                                                            <x-ui.button size="sm" variant="danger" icon="trash" wire:click="eliminar({{ $item->id_kit ?? $item->id }})" wire:confirm="¿Dar de baja este insumo del kit?" title="Dar de baja" />
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </x-ui.table-container>
                            @else
                                <div class="text-center py-8 rounded-sm bg-hueso border border-arena">
                                    <p class="text-tinta-suave font-medium mb-3">No hay insumos configurados para este kit</p>
                                    <x-ui.button type="button" icon="plus" wire:click="abrirModalAgregar">
                                        Agregar Primer Insumo
                                    </x-ui.button>
                                </div>
                            @endif

                            @if($items_count > 0)
                                <div class="mt-6 rounded-sm bg-hueso border border-arena p-4">
                                    <div class="grid grid-cols-4 gap-4 text-center">
                                        <div>
                                            <strong class="block text-tinta-suave text-xs mb-1">Total Insumos</strong>
                                            <span class="text-xl font-semibold text-tinta">{{ $items_count }}</span>
                                        </div>
                                        <div>
                                            <strong class="block text-tinta-suave text-xs mb-1">Obligatorios</strong>
                                            <span class="text-xl font-semibold text-tierra">{{ $items_obligatorios }}</span>
                                        </div>
                                        <div>
                                            <strong class="block text-tinta-suave text-xs mb-1">Opcionales</strong>
                                            <span class="text-xl font-semibold text-tinta-suave">{{ $items_opcionales }}</span>
                                        </div>
                                        <div>
                                            <strong class="block text-tinta-suave text-xs mb-1">Stock OK</strong>
                                            <x-ui.badge variant="{{ ($items_con_stock === $items_count) ? 'success' : 'warning' }}">{{ $items_con_stock }}/{{ $items_count }}</x-ui.badge>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="border-t border-arena pt-6">
                            <h6 class="mb-4 font-semibold text-tinta flex items-center gap-2">
                                <flux:icon.archive-box class="size-4" />
                                Historial de Bajas
                            </h6>

                            @if($historial->count() > 0)
                                <x-ui.table-container>
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 8%;">ID</th>
                                                <th style="width: 35%;">Insumo</th>
                                                <th style="width: 15%;">Cantidad</th>
                                                <th style="width: 20%;">Fecha de Baja</th>
                                                <th style="width: 22%;" class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($historial as $item)
                                                <tr class="bg-resina-suave hover:bg-resina-suave/80" wire:key="row-{{ $item->id_kit ?? $item->id }}">
                                                    <td><x-ui.badge variant="neutral">{{ $item->id_kit ?? $item->id }}</x-ui.badge></td>
                                                    <td>
                                                        <strong class="text-tinta">{{ optional($item->insumo)->nombre ?? '—' }}</strong>
                                                        @if($item->es_obligatorio)
                                                            <x-ui.badge variant="danger" class="ml-2">Obligatorio</x-ui.badge>
                                                        @endif
                                                    </td>
                                                    <td class="text-tinta-suave">{{ number_format($item->cantidad_requerida, 2) }}</td>
                                                    <td class="text-tinta-suave">
                                                        <flux:icon.clock class="size-3 inline text-arena-oscura" />
                                                        <small class="text-xs">{{ $item->deleted_at ? $item->deleted_at->format('d/m/Y H:i') : '—' }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <x-ui.button type="button" size="sm" icon="arrow-uturn-left" wire:click="restaurar({{ $item->id_kit ?? $item->id }})" title="Restaurar">
                                                            Restaurar
                                                        </x-ui.button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </x-ui.table-container>
                            @else
                                <div class="text-center py-8 rounded-sm bg-hueso border border-arena">
                                    <p class="text-tinta-suave font-medium">No hay bajas registradas para este kit</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </x-ui.card>
        @endif

        @if($activeTab === 'listado')
            <x-ui.card class="overflow-hidden">
                <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-tinta mb-0">
                        <flux:icon.list-bullet class="size-5" />
                        Kits Registrados por Maquinaria
                    </h5>
                </div>
                <div class="p-6">
                    @if(count($kits_registrados) > 0)
                        <div class="space-y-4">
                            @foreach($kits_registrados as $maqId => $kit)
                                <x-ui.card class="overflow-hidden" wire:key="card-{{ $maqId }}">
                                    <div class="flex items-center justify-between bg-hueso border-b border-arena px-6 py-4">
                                        <div>
                                            <h6 class="mb-1 font-semibold text-tinta flex items-center gap-2">
                                                <flux:icon.cog class="size-4 text-pino" />
                                                <strong>{{ optional($kit['maquinaria'])->modelo }}</strong>
                                                <span class="text-tinta-suave">({{ optional(optional($kit['maquinaria'])->tipoMaquinaria)->nombre }})</span>
                                            </h6>
                                            <small class="text-tinta-suave">ID: {{ optional($kit['maquinaria'])->id_maquinaria }}</small>
                                        </div>
                                        <div class="flex gap-2">
                                            <x-ui.button type="button" size="sm" variant="secondary" icon="pencil-square" wire:click="editarKit({{ $maqId }})" title="Editar kit">
                                                Editar
                                            </x-ui.button>
                                            <x-ui.button type="button" size="sm" variant="danger" icon="trash" wire:click="eliminarKit({{ $maqId }})" wire:confirm="¿Esta seguro de eliminar este kit completo?" title="Eliminar kit" />
                                        </div>
                                    </div>
                                    <div class="px-6 py-4">
                                        <div class="mb-4 flex gap-2">
                                            <x-ui.badge variant="info">{{ $kit['total_items'] }} Insumos</x-ui.badge>
                                            <x-ui.badge variant="danger">{{ $kit['obligatorios'] }} Obligatorios</x-ui.badge>
                                            <x-ui.badge variant="neutral">{{ $kit['opcionales'] }} Opcionales</x-ui.badge>
                                        </div>
                                        <x-ui.table-container>
                                            <table class="data-table">
                                                <thead>
                                                    <tr>
                                                        <th>Insumo</th>
                                                        <th>Cantidad</th>
                                                        <th>Stock</th>
                                                        <th>Tipo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($kit['items'] as $item)
                                                        @php
                                                            $ins = optional($item->insumo);
                                                            $stock = is_numeric($ins->stock ?? null) ? $ins->stock : 0;
                                                        @endphp
                                                        <tr wire:key="row-{{ $item->id_kit ?? $item->id }}">
                                                            <td class="font-semibold text-tinta">{{ $ins->nombre }}</td>
                                                            <td class="text-tinta-suave">{{ number_format($item->cantidad_requerida, 2) }}</td>
                                                            <td>
                                                                <x-ui.badge variant="{{ ($stock >= $item->cantidad_requerida) ? 'success' : 'danger' }}">
                                                                    {{ number_format($stock, 2) }}
                                                                </x-ui.badge>
                                                            </td>
                                                            <td>
                                                                <x-ui.badge variant="{{ $item->es_obligatorio ? 'danger' : 'neutral' }}">
                                                                    {{ $item->es_obligatorio ? 'Obligatorio' : 'Opcional' }}
                                                                </x-ui.badge>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </x-ui.table-container>
                                    </div>
                                </x-ui.card>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 rounded-sm bg-hueso border border-arena">
                            <p class="text-tinta-suave font-medium mb-4">No hay kits registrados aun</p>
                            <x-ui.button type="button" icon="plus" wire:click="$set('activeTab','nuevo')">
                                Crear Primer Kit
                            </x-ui.button>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        @endif
    </div>

    @if($modal_item)
        <div class="fixed inset-0 z-50 flex items-start justify-center bg-black/40 p-4 py-8" style="overflow-y: auto;">
            <div class="w-full max-w-md rounded-sm bg-white shadow-2xl border border-arena">
                <div class="flex items-center justify-between border-b border-arena px-6 py-4 bg-pino text-white">
                    <h5 class="font-semibold flex items-center gap-2">
                        @if($item_id)
                            <flux:icon.pencil-square class="size-5" />
                            Editar
                        @else
                            <flux:icon.plus class="size-5" />
                            Agregar
                        @endif
                        Insumo al Kit
                    </h5>
                    <button type="button" wire:click="cerrarModal" class="text-white hover:text-corteza-suave">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-tinta mb-2">Insumo <span class="text-tierra">*</span></label>
                        <select wire:model="insumo_id" class="form-input @error('insumo_id') ring-2 ring-tierra @enderror">
                            <option value="">Seleccionar insumo...</option>
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->id_insumo }}" wire:key="option-{{ $insumo->id_insumo }}">
                                    {{ $insumo->nombre }} (Stock: {{ number_format($insumo->stock ?? 0, 2) }})
                                </option>
                            @endforeach
                        </select>
                        @error('insumo_id') <p class="mt-1 text-sm text-tierra">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-tinta mb-2">Cantidad Requerida <span class="text-tierra">*</span></label>
                        <input type="number" wire:model="cantidad_requerida" class="form-input @error('cantidad_requerida') ring-2 ring-tierra @enderror" step="0.1" min="0.01" placeholder="Ej: 10.00">
                        @error('cantidad_requerida') <p class="mt-1 text-sm text-tierra">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="es_obligatorio" class="w-4 h-4 rounded border-arena text-pino focus:ring-pino/20">
                            <span class="text-sm font-semibold text-tinta">¿Es obligatorio?</span>
                        </label>
                        <small class="text-tinta-suave text-xs mt-1 block">Los insumos obligatorios deben estar disponibles para aprobar el mantenimiento</small>
                    </div>
                </div>
                <div class="flex gap-2 justify-end border-t border-arena px-6 py-4 bg-corteza-suave">
                    <x-ui.button type="button" variant="secondary" wire:click="cerrarModal">
                        Cancelar
                    </x-ui.button>
                    <x-ui.button type="button" icon="check" wire:click="guardar">
                        {{ $item_id ? 'Actualizar' : 'Agregar' }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif

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
