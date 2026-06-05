<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">🧾 Recibos</h1>
    </div>

    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm" role="alert">
            <span class="text-emerald-600">✓</span>
            <span class="flex-1 text-sm font-medium">{{ session('message') }}</span>
            <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="open = false">✕</button>
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Recibo', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-recibos', 'editar-recibos'])],
        ['value' => 'listado', 'label' => 'Listado de Recibos', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-recibos', 'editar-recibos'])
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $recibo_id ? '✏️ Editar Recibo' : '➕ Nuevo Recibo' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="md:col-span-2">
                            <label for="id_empleado" class="block text-sm font-semibold text-slate-700 mb-1.5">Empleado <span class="text-red-500">*</span></label>
                            <select id="id_empleado" wire:model="id_empleado"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('id_empleado') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($empleados as $emp)
                                    <option value="{{ $emp->id_empleado }}" wire:key="option-{{ $emp->id_empleado }}">{{ $emp->apellido }}, {{ $emp->nombre }}</option>
                                @endforeach
                            </select>
                            @error('id_empleado') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_emision" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Emisión <span class="text-red-500">*</span></label>
                            <input type="date" id="fecha_emision" wire:model="fecha_emision"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fecha_emision') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('fecha_emision') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="monto_bruto" class="block text-sm font-semibold text-slate-700 mb-1.5">Monto Bruto <span class="text-red-500">*</span></label>
                            <input type="number" id="monto_bruto" wire:model.live="monto_bruto" step="0.01"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('monto_bruto') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="0.00">
                            @error('monto_bruto') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="descuentos" class="block text-sm font-semibold text-slate-700 mb-1.5">Descuentos</label>
                            <input type="number" id="descuentos" wire:model.live="descuentos" step="0.01"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('descuentos') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="0.00" value="0">
                            @error('descuentos') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="monto" class="block text-sm font-semibold text-slate-700 mb-1.5">Monto Neto</label>
                            <input type="text" id="monto"
                                class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50 rounded-lg text-sm text-slate-600"
                                value="{{ isset($monto) ? '$' . number_format($monto, 2, ',', '.') : '' }}" readonly>
                        </div>
                        <div>
                            <label for="observaciones" class="block text-sm font-semibold text-slate-700 mb-1.5">Observaciones</label>
                            <textarea id="observaciones" wire:model="observaciones" rows="1"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('observaciones') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Observaciones"></textarea>
                            @error('observaciones') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($recibo_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @canany(['crear-recibos', 'editar-recibos'])
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ {{ $recibo_id ? 'Actualizar' : 'Guardar' }}
                        </button>
                        @endcanany
                    </div>
                </form>
            </div>
        </div>
        @endcanany
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6">
                <x-search-input placeholder="Buscar por empleado..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Empleado</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Bruto</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Descuentos</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Neto</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recibos as $recibo)
                                <tr wire:key="row-{{ $recibo->id_recibo }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $recibo->id_recibo }}</span></td>
                                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $recibo->empleado?->apellido }}, {{ $recibo->empleado?->nombre }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ \Carbon\Carbon::parse($recibo->fecha_emision)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2.5 text-right text-slate-600">${{ number_format($recibo->monto_bruto, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right text-slate-600">${{ number_format($recibo->descuentos ?? 0, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-slate-800">${{ number_format($recibo->monto, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <x-action-buttons
                                            editWireClick="editar({{ $recibo->id_recibo }})"
                                            deleteWireClick="eliminar({{ $recibo->id_recibo }})"
                                            deleteMessage="¿Está seguro de eliminar este recibo?"
                                            :canEdit="auth()->user()->can('editar-recibos')"
                                            :canDelete="auth()->user()->can('eliminar-recibos')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="7" message="No hay recibos registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $recibos->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
