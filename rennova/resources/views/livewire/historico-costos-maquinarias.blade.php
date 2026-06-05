<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">💵 Histórico de Costos de Maquinarias</h1>
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
        ['value' => 'nuevo', 'label' => 'Nuevo Histórico', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-historico-costos', 'editar-historico-costos'])],
        ['value' => 'listado', 'label' => 'Listado de Históricos', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-historico-costos', 'editar-historico-costos'])
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $historico_id ? '✏️ Editar Histórico' : '➕ Nuevo Histórico' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="id_maquinaria" class="block text-sm font-semibold text-slate-700 mb-1.5">Maquinaria <span class="text-red-500">*</span></label>
                            <select id="id_maquinaria" wire:model="id_maquinaria"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('id_maquinaria') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($maquinarias as $maq)
                                    <option value="{{ $maq->id_maquinaria }}" wire:key="option-{{ $maq->id_maquinaria }}">{{ $maq->modelo }}</option>
                                @endforeach
                            </select>
                            @error('id_maquinaria') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="costo_por_tonelada" class="block text-sm font-semibold text-slate-700 mb-1.5">Costo por Tonelada <span class="text-red-500">*</span></label>
                            <input type="number" id="costo_por_tonelada" wire:model="costo_por_tonelada" step="0.01"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('costo_por_tonelada') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                placeholder="0.00">
                            @error('costo_por_tonelada') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="fecha_inicio_vigencia" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Inicio Vigencia <span class="text-red-500">*</span></label>
                            <input type="date" id="fecha_inicio_vigencia" wire:model="fecha_inicio_vigencia"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fecha_inicio_vigencia') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('fecha_inicio_vigencia') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_fin_vigencia" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Fin Vigencia</label>
                            <input type="date" id="fecha_fin_vigencia" wire:model="fecha_fin_vigencia"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fecha_fin_vigencia') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('fecha_fin_vigencia') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            <small class="text-slate-500 text-xs mt-1 block">Opcional — dejar en blanco si está vigente actualmente</small>
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($historico_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @canany(['crear-historico-costos', 'editar-historico-costos'])
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
                <x-search-input placeholder="Buscar por modelo de maquinaria..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Maquinaria</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Costo/Ton</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Inicio Vig.</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fin Vig.</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($historicos as $historico)
                                <tr wire:key="row-{{ $historico->id_costo }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $historico->id_costo }}</span></td>
                                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $historico->maquinaria->modelo ?? 'N/A' }}</td>
                                    <td class="px-4 py-2.5 text-right text-slate-600">${{ number_format($historico->costo_por_tonelada, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $historico->fecha_inicio_vigencia ? \Carbon\Carbon::parse($historico->fecha_inicio_vigencia)->format('d/m/Y') : '-' }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $historico->fecha_fin_vigencia ? \Carbon\Carbon::parse($historico->fecha_fin_vigencia)->format('d/m/Y') : 'Vigente' }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <x-action-buttons
                                            editWireClick="editar({{ $historico->id_costo }})"
                                            deleteWireClick="eliminar({{ $historico->id_costo }})"
                                            deleteMessage="¿Eliminar este histórico?"
                                            :canEdit="auth()->user()->can('editar-historico-costos')"
                                            :canDelete="auth()->user()->can('eliminar-historico-costos')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="6" message="No hay históricos registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $historicos->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
