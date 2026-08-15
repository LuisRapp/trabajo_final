<div>
    @if($es_dia_caido)
        <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden border border-slate-200">
            <div class="bg-yellow-500 text-slate-900 px-6 py-4">
                <h5 class="text-lg font-semibold mb-0">💰 Asignación de Jornales</h5>
            </div>
            <div class="p-6">
                <div class="border border-slate-300 rounded-lg p-4 mb-4 bg-slate-50">
                    <h6 class="font-semibold mb-4 text-slate-900">👤 Agregar Empleado al Jornal</h6>
                    <div class="grid grid-cols-1 md:grid-cols-10 gap-4 items-end">
                        <div class="md:col-span-8">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Empleado <span class="text-red-500">*</span></label>
                            <select wire:model="jornal_id_empleado" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none @error('jornal_id_empleado') ring-2 ring-red-500 @enderror">
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
                            @error('jornal_id_empleado') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <button type="button" wire:click.prevent="agregarJornal" class="w-full px-6 py-3 bg-yellow-500 text-slate-900 rounded-lg font-semibold hover:bg-yellow-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="agregarJornal">
                                <span wire:loading.remove wire:target="agregarJornal">➕ Agregar</span>
                                <span wire:loading wire:target="agregarJornal"><svg class="inline-block w-4 h-4 animate-spin mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>
                            </button>
                        </div>
                    </div>
                </div>

                @if(count($jornales) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-sm">
                            <thead class="bg-slate-100 border-b border-slate-300">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold text-slate-900">Empleado</th>
                                    <th class="px-4 py-2 text-left font-semibold text-slate-900">Rol</th>
                                    <th class="px-4 py-2 text-left font-semibold text-slate-900">Jornal</th>
                                    <th class="px-4 py-2 text-center font-semibold text-slate-900">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jornales as $index => $jornal)
                                    <tr class="border-b border-slate-200 hover:bg-slate-50" wire:key="row-{{ $index }}">
                                        <td class="px-4 py-2">{{ $jornal['nombre_completo'] ?? '-' }}</td>
                                        <td class="px-4 py-2"><span class="inline-block px-3 py-1 bg-slate-200 text-slate-800 text-xs font-medium rounded">{{ $jornal['rol'] ?? '-' }}</span></td>
                                        <td class="px-4 py-2 text-green-600 font-bold">${{ number_format($jornal['jornal_diario'] ?? 0, 2) }}</td>
                                        <td class="px-4 py-2 text-center">
                                            <button type="button" wire:click.prevent="eliminarJornal({{ $index }})" class="px-3 py-1 border border-red-500 text-red-600 rounded text-sm hover:bg-red-50 transition-colors">
                                                🗑️
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-100 border-t border-slate-300 font-semibold">
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-right">Total Jornales:</td>
                                    <td class="px-4 py-2 text-center text-yellow-600">${{ number_format(array_sum(array_column($jornales, 'jornal_diario')), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800 text-sm">
                        ℹ️ Sin empleados asignados. Agregue al menos un empleado.
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
