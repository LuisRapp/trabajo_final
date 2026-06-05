<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">📈 Histórico de Roles Laborales</h1>
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
        ['value' => 'nuevo', 'label' => 'Nuevo Histórico', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-historico-roles', 'editar-historico-roles'])],
        ['value' => 'listado', 'label' => 'Listado de Históricos', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-historico-roles', 'editar-historico-roles'])
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $historico_id ? '✏️ Editar Histórico' : '➕ Nuevo Histórico' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="rol_laboral_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Rol Laboral <span class="text-red-500">*</span></label>
                            <select id="rol_laboral_id" wire:model="rol_laboral_id"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('rol_laboral_id') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($rolesLaborales as $rol)
                                    <option value="{{ $rol->id_rol_laboral }}" wire:key="option-{{ $rol->id_rol_laboral }}">{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                            @error('rol_laboral_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="precio_tonelada" class="block text-sm font-semibold text-slate-700 mb-1.5">Precio/Ton <span class="text-red-500">*</span></label>
                            <input type="number" id="precio_tonelada" wire:model="precio_tonelada" step="0.01"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('precio_tonelada') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="0.00">
                            @error('precio_tonelada') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="jornal_diario" class="block text-sm font-semibold text-slate-700 mb-1.5">Jornal Diario <span class="text-red-500">*</span></label>
                            <input type="number" id="jornal_diario" wire:model="jornal_diario" step="0.01"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('jornal_diario') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="0.00">
                            @error('jornal_diario') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label for="fecha_inicio" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Inicio <span class="text-red-500">*</span></label>
                            <input type="date" id="fecha_inicio" wire:model="fecha_inicio"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fecha_inicio') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('fecha_inicio') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_fin" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Fin</label>
                            <input type="date" id="fecha_fin" wire:model="fecha_fin"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fecha_fin') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('fecha_fin') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="motivo_cambio" class="block text-sm font-semibold text-slate-700 mb-1.5">Motivo del Cambio</label>
                            <input type="text" id="motivo_cambio" wire:model="motivo_cambio"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('motivo_cambio') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="Motivo">
                            @error('motivo_cambio') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($historico_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @canany(['crear-historico-roles', 'editar-historico-roles'])
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ {{ $historico_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por rol..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Rol</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio/Ton</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Jornal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Inicio</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fin</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($historicos as $historico)
                                <tr wire:key="row-{{ $historico->id }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $historico->id }}</span></td>
                                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $historico->rolLaboral->nombre ?? 'N/A' }}</td>
                                    <td class="px-4 py-2.5 text-right text-slate-600">${{ number_format($historico->precio_tonelada, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-right text-slate-600">${{ number_format($historico->jornal_diario, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $historico->fecha_inicio ? \Carbon\Carbon::parse($historico->fecha_inicio)->format('d/m/Y') : '-' }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $historico->fecha_fin ? \Carbon\Carbon::parse($historico->fecha_fin)->format('d/m/Y') : 'Vigente' }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <x-action-buttons
                                            editWireClick="editar({{ $historico->id }})"
                                            deleteWireClick="eliminar({{ $historico->id }})"
                                            deleteMessage="¿Está seguro de eliminar este histórico?"
                                            :canEdit="auth()->user()->can('editar-historico-roles')"
                                            :canDelete="auth()->user()->can('eliminar-historico-roles')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="7" message="No hay históricos registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
