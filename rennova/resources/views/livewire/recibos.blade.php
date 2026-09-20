<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.document-text class="size-6" />
            Recibos
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <div class="flex border-b border-arena mb-6" role="tablist">
        @canany(['crear-recibos', 'editar-recibos'])
        <button type="button" role="tab"
            wire:click="$set('tab_activo', 'nuevo')"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $tab_activo === 'nuevo' ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}">
            <flux:icon.plus class="size-4 inline mr-1" />
            Nuevo Recibo
        </button>
        @endcanany
        <button type="button" role="tab"
            wire:click="$set('tab_activo', 'listado')"
            class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors {{ $tab_activo === 'listado' ? 'border-pino text-pino' : 'border-transparent text-tinta-suave hover:text-tinta' }}">
            <flux:icon.list-bullet class="size-4 inline mr-1" />
            Listado de Recibos
        </button>
    </div>

    @if($tab_activo === 'nuevo')
        @canany(['crear-recibos', 'editar-recibos'])
        <x-ui.card class="overflow-hidden mb-6">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="text-lg font-semibold text-tinta flex items-center gap-2">
                    @if($recibo_id)
                        <flux:icon.pencil-square class="size-5" />
                        Editar Recibo
                    @else
                        <flux:icon.plus class="size-5" />
                        Nuevo Recibo
                    @endif
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="md:col-span-2">
                            <label for="id_empleado" class="block text-sm font-semibold text-tinta mb-1.5">Empleado <span class="text-tierra">*</span></label>
                            <select id="id_empleado" wire:model="id_empleado"
                                class="form-input @error('id_empleado') border-tierra bg-tierra-suave @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($empleados as $emp)
                                    <option value="{{ $emp->id_empleado }}" wire:key="option-{{ $emp->id_empleado }}">{{ $emp->apellido }}, {{ $emp->nombre }}</option>
                                @endforeach
                            </select>
                            @error('id_empleado') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_emision" class="block text-sm font-semibold text-tinta mb-1.5">Fecha Emisión <span class="text-tierra">*</span></label>
                            <input type="date" id="fecha_emision" wire:model="fecha_emision"
                                class="form-input @error('fecha_emision') border-tierra bg-tierra-suave @enderror">
                            @error('fecha_emision') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="monto_bruto" class="block text-sm font-semibold text-tinta mb-1.5">Monto Bruto <span class="text-tierra">*</span></label>
                            <input type="number" id="monto_bruto" wire:model.live="monto_bruto" step="0.01"
                                class="form-input @error('monto_bruto') border-tierra bg-tierra-suave @enderror"
                                placeholder="0.00">
                            @error('monto_bruto') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="descuentos" class="block text-sm font-semibold text-tinta mb-1.5">Descuentos</label>
                            <input type="number" id="descuentos" wire:model.live="descuentos" step="0.01"
                                class="form-input @error('descuentos') border-tierra bg-tierra-suave @enderror"
                                placeholder="0.00" value="0">
                            @error('descuentos') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="monto" class="block text-sm font-semibold text-tinta mb-1.5">Monto Neto</label>
                            <input type="text" id="monto"
                                class="form-input bg-corteza-suave text-tinta-suave border-arena"
                                value="{{ isset($monto) ? '$' . number_format($monto, 2, ',', '.') : '' }}" readonly>
                        </div>
                        <div>
                            <label for="observaciones" class="block text-sm font-semibold text-tinta mb-1.5">Observaciones</label>
                            <textarea id="observaciones" wire:model="observaciones" rows="1"
                                class="form-input @error('observaciones') border-tierra bg-tierra-suave @enderror"
                                placeholder="Observaciones"></textarea>
                            @error('observaciones') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($recibo_id)
                            <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="resetCampos">
                                Cancelar
                            </x-ui.button>
                        @endif
                        @canany(['crear-recibos', 'editar-recibos'])
                        <x-ui.button type="submit" variant="primary" icon="check">
                            {{ $recibo_id ? 'Actualizar' : 'Guardar' }}
                        </x-ui.button>
                        @endcanany
                    </div>
                </form>
            </div>
        </x-ui.card>
        @endcanany
    @else
        <x-ui.card class="overflow-hidden">
            <div class="p-6">
                <div class="mb-6">
                    <div class="flex items-center gap-2 px-3 py-2 border border-arena rounded-sm bg-corteza-suave">
                        <flux:icon.magnifying-glass class="size-4 text-tinta-suave" />
                        <input type="text"
                            class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-sm text-tinta placeholder:text-tinta-suave"
                            placeholder="Buscar por empleado..."
                            wire:model.live="busqueda">
                    </div>
                </div>

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Empleado</th>
                                <th>Fecha</th>
                                <th class="text-right">Bruto</th>
                                <th class="text-right">Descuentos</th>
                                <th class="text-right">Neto</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recibos as $recibo)
                                <tr wire:key="row-{{ $recibo->id_recibo }}">
                                    <td><x-ui.badge variant="neutral">{{ $recibo->id_recibo }}</x-ui.badge></td>
                                    <td class="font-medium text-tinta">{{ $recibo->empleado?->apellido }}, {{ $recibo->empleado?->nombre }}</td>
                                    <td class="text-tinta-suave">{{ \Carbon\Carbon::parse($recibo->fecha_emision)->format('d/m/Y') }}</td>
                                    <td class="text-right text-tinta-suave">${{ number_format($recibo->monto_bruto, 2, ',', '.') }}</td>
                                    <td class="text-right text-tinta-suave">${{ number_format($recibo->descuentos ?? 0, 2, ',', '.') }}</td>
                                    <td class="text-right font-semibold text-tinta">${{ number_format($recibo->monto, 2, ',', '.') }}</td>
                                    <td class="text-right">
                                        <div class="inline-flex rounded-sm shadow-xs">
                                            @can('editar-recibos')
                                            <x-ui.button variant="ghost" size="sm" icon="pencil-square"
                                                wire:click="editar({{ $recibo->id_recibo }})"
                                                title="Editar"
                                                class="rounded-r-none border-r-0" />
                                            @endcan
                                            @can('eliminar-recibos')
                                            <x-ui.button variant="ghost" size="sm" icon="trash"
                                                wire:click="eliminar({{ $recibo->id_recibo }})"
                                                wire:confirm="¿Está seguro de eliminar este recibo?"
                                                title="Eliminar"
                                                class="rounded-l-none" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-12 text-tinta-suave">
                                        No hay recibos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                <div class="mt-4">
                    {{ $recibos->links() }}
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
