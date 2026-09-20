<div>
    @if($es_dia_caido)
        <x-ui.card class="overflow-hidden mb-6">
            <div class="bg-pino text-blanco px-6 py-4">
                <h5 class="text-lg font-semibold mb-0 flex items-center gap-2">
                    <flux:icon.banknotes class="size-5" />
                    Asignación de Jornales
                </h5>
            </div>
            <div class="p-6">
                <x-ui.card class="overflow-hidden mb-4">
                    <div class="bg-corteza-suave border-b border-arena px-4 py-3">
                        <h6 class="font-semibold text-tinta flex items-center gap-2">
                            <flux:icon.user class="size-5" />
                            Agregar Empleado al Jornal
                        </h6>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-10 gap-4 items-end">
                            <div class="md:col-span-8">
                                <label class="block text-sm font-semibold text-tinta mb-2">Empleado <span class="text-tierra">*</span></label>
                                <select wire:model="jornal_id_empleado" class="form-input @error('jornal_id_empleado') border-tierra bg-tierra-suave @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($empleadosFiltrados as $emp)
                                        <option value="{{ $emp->id_empleado }}" wire:key="option-{{ $emp->id_empleado }}">
                                            {{ $emp->apellido }}, {{ $emp->nombre }} - {{ $emp->rolLaboral->nombre ?? 'Sin rol' }}
                                            @if(isset($jornal_por_empleado[$emp->id_empleado]))
                                                (Jornal: ${{ number_format($jornal_por_empleado[$emp->id_empleado], 2) }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('jornal_id_empleado') <div class="text-tierra text-sm mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <x-ui.button type="button" variant="primary" icon="plus" wire:click.prevent="agregarJornal" class="w-full justify-center" wire:loading.attr="disabled" wire:target="agregarJornal">
                                    <span wire:loading.remove wire:target="agregarJornal">Agregar</span>
                                    <span wire:loading wire:target="agregarJornal"><flux:icon.arrow-path class="size-4 animate-spin" /></span>
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                @if(count($jornales) > 0)
                    <x-ui.table-container>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Empleado</th>
                                    <th>Rol</th>
                                    <th>Jornal</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jornales as $index => $jornal)
                                    <tr wire:key="row-{{ $index }}">
                                        <td>{{ $jornal['nombre_completo'] ?? '-' }}</td>
                                        <td><x-ui.badge variant="neutral">{{ $jornal['rol'] ?? '-' }}</x-ui.badge></td>
                                        <td class="text-musgo font-bold">${{ number_format($jornal['jornal_diario'] ?? 0, 2) }}</td>
                                        <td class="text-center">
                                            <x-ui.button type="button" variant="ghost" size="sm" icon="trash"
                                                wire:click.prevent="eliminarJornal({{ $index }})"
                                                title="Eliminar" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-semibold bg-corteza-suave">
                                    <td colspan="3" class="text-right">Total Jornales:</td>
                                    <td class="text-center text-pino">${{ number_format(array_sum(array_column($jornales, 'jornal_diario')), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </x-ui.table-container>
                @else
                    <x-ui.alert variant="warning" dismissible="false">
                        Sin empleados asignados. Agregue al menos un empleado.
                    </x-ui.alert>
                @endif
            </div>
        </x-ui.card>
    @endif
</div>
