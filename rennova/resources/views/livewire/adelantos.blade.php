<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">💰 Adelantos</h1>
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
        ['value' => 'nuevo', 'label' => 'Nuevo Adelanto', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-adelantos', 'editar-adelantos'])],
        ['value' => 'listado', 'label' => 'Listado de Adelantos', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-adelantos', 'editar-adelantos'])
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $adelanto_id ? '✏️ Editar Adelanto' : '➕ Nuevo Adelanto' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="id_empleado" class="block text-sm font-semibold text-slate-700 mb-1.5">Empleado <span class="text-red-500">*</span></label>
                            <select id="id_empleado" wire:model="id_empleado"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('id_empleado') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                <option value="">Seleccione...</option>
                                @foreach(($empleados ?? []) as $empleado)
                                    <option value="{{ $empleado->id_empleado }}" wire:key="option-{{ $empleado->id_empleado }}">{{ $empleado->apellido }}, {{ $empleado->nombre }}</option>
                                @endforeach
                            </select>
                            @error('id_empleado') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="monto" class="block text-sm font-semibold text-slate-700 mb-1.5">Monto <span class="text-red-500">*</span></label>
                            <div class="flex items-center gap-0">
                                <span class="px-3 py-2.5 bg-slate-100 border border-r-0 border-slate-300 rounded-l-lg text-slate-600 font-semibold text-sm">$</span>
                                <input type="number" id="monto" wire:model="monto" step="0.1" min="0"
                                    class="flex-1 px-4 py-2.5 border rounded-r-lg text-sm transition-colors @error('monto') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                    placeholder="0.00">
                            </div>
                            @error('monto') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_emision" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha de Adelanto <span class="text-red-500">*</span></label>
                            <input type="date" id="fecha_emision" wire:model="fecha_emision"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fecha_emision') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('fecha_emision') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-2 justify-end">
                        @if ($adelanto_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @canany(['crear-adelantos', 'editar-adelantos'])
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ {{ $adelanto_id ? 'Actualizar' : 'Guardar' }}
                        </button>
                        @endcanany
                    </div>
                </form>
            </div>
        </div>
        @endcanany
    @endif

    @if($tab_activo === 'listado')
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">Listado de Adelantos</h5>
            </div>
            <div class="p-6">
                <x-search-input placeholder="Buscar por empleado, monto o fecha..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Empleado</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Monto</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha Adelanto</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse (($adelantos ?? []) as $adelanto)
                                <tr wire:key="row-{{ $adelanto->id_adelanto }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $adelanto->id_adelanto }}</span></td>
                                    <td class="px-3 py-2.5 font-medium text-slate-800">{{ $adelanto->empleado?->apellido ?? 'N/A' }}, {{ $adelanto->empleado?->nombre ?? '' }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">${{ number_format($adelanto->monto, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $adelanto->fecha_emision ? \Carbon\Carbon::parse($adelanto->fecha_emision)->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="px-3 py-2.5">
                                        @if($adelanto->activo)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Activo</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-right">
                                        <x-action-buttons
                                            editWireClick="editar({{ $adelanto->id_adelanto }})"
                                            deleteWireClick="eliminar({{ $adelanto->id_adelanto }})"
                                            deleteMessage="¿Está seguro de eliminar este adelanto?"
                                            :canEdit="auth()->user()->can('editar-adelantos')"
                                            :canDelete="auth()->user()->can('eliminar-adelantos')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="6" message="No hay adelantos registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($adelantos->hasPages())
                    <div class="mt-6">
                        {{ $adelantos->links('pagination::tailwind') }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
