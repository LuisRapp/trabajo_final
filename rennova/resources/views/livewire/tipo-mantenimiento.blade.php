<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">🔧 Tipos de Mantenimiento</h1>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-5 py-3 text-sm font-medium" role="alert">
            <span class="text-emerald-600">✓</span> {{ session('message') }}
        </div>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Tipo', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-mantenimiento-tipos', 'editar-mantenimiento-tipos'])],
        ['value' => 'listado', 'label' => 'Listado de Tipos', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-mantenimiento-tipos', 'editar-mantenimiento-tipos'])
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $tipo_id ? '✏️ Editar Tipo' : '➕ Nuevo Tipo' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="mb-6">
                        <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre del Tipo <span class="text-red-500">*</span></label>
                        <input type="text" id="nombre" wire:model="nombre"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('nombre') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                            placeholder="Ej: Preventivo, Correctivo, Predictivo">
                        @error('nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-2 justify-end">
                        @if ($tipo_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @canany(['crear-mantenimiento-tipos', 'editar-mantenimiento-tipos'])
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ {{ $tipo_id ? 'Actualizar' : 'Guardar' }}
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
                <x-search-input placeholder="Buscar por nombre..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($tipos as $tipo)
                                <tr wire:key="row-{{ $tipo->id_tipo_mantenimiento }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $tipo->id_tipo_mantenimiento }}</span></td>
                                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $tipo->nombre }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <x-action-buttons
                                            editWireClick="editar({{ $tipo->id_tipo_mantenimiento }})"
                                            deleteWireClick="eliminar({{ $tipo->id_tipo_mantenimiento }})"
                                            deleteMessage="¿Está seguro de dar de baja este tipo?"
                                            :canEdit="auth()->user()->can('editar-mantenimiento-tipos')"
                                            :canDelete="auth()->user()->can('eliminar-mantenimiento-tipos')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="3" message="No hay tipos registrados." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
