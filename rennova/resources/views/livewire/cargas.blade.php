<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">🚛 Cargas</h1>
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
        ['value' => 'nuevo', 'label' => 'Nueva Carga', 'icon' => 'plus-circle', 'can' => auth()->user()->canAny(['crear-cargas', 'editar-cargas'])],
        ['value' => 'listado', 'label' => 'Listado de Cargas', 'icon' => 'list-ul'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-cargas', 'editar-cargas'])
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800">
                    {{ $carga_id ? '✏️ Editar Carga' : '➕ Nueva Carga' }}
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <label for="id_lote" class="block text-sm font-semibold text-slate-700 mb-1.5">Lote <span class="text-red-500">*</span></label>
                            <select id="id_lote" wire:model="id_lote"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('id_lote') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($lotes as $lote)
                                    <option value="{{ $lote->id_lote }}" wire:key="option-{{ $lote->id_lote }}">{{ $lote->id_lote }}</option>
                                @endforeach
                            </select>
                            @error('id_lote') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="id_categoria_madera" class="block text-sm font-semibold text-slate-700 mb-1.5">Categoría Madera</label>
                            <select id="id_categoria_madera" wire:model="id_categoria_madera"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                                <option value="">Seleccione...</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id_categoria_madera }}" wire:key="option-{{ $cat->id_categoria_madera }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                            @error('id_categoria_madera') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="id_chofer" class="block text-sm font-semibold text-slate-700 mb-1.5">Chofer (ID)</label>
                            <input type="number" id="id_chofer" wire:model="id_chofer"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('id_chofer') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('id_chofer') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="id_parte_diario" class="block text-sm font-semibold text-slate-700 mb-1.5">Parte Diario</label>
                            <select id="id_parte_diario" wire:model="id_parte_diario"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20">
                                <option value="">Seleccione...</option>
                                @foreach($partes as $parte)
                                    <option value="{{ $parte->id_parte_diario }}" wire:key="option-{{ $parte->id_parte_diario }}">{{ $parte->id_parte_diario }}</option>
                                @endforeach
                            </select>
                            @error('id_parte_diario') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                        <div>
                            <label for="ticket" class="block text-sm font-semibold text-slate-700 mb-1.5">Ticket</label>
                            <input type="text" id="ticket" wire:model="ticket" maxlength="20"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('ticket') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('ticket') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="peso_bruto" class="block text-sm font-semibold text-slate-700 mb-1.5">Peso Bruto</label>
                            <input type="number" id="peso_bruto" wire:model="peso_bruto" step="0.1" min="0"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('peso_bruto') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('peso_bruto') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="tara" class="block text-sm font-semibold text-slate-700 mb-1.5">Tara</label>
                            <input type="number" id="tara" wire:model="tara" step="0.1" min="0"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('tara') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('tara') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="peso_neto" class="block text-sm font-semibold text-slate-700 mb-1.5">Peso Neto</label>
                            <input type="number" id="peso_neto" wire:model="peso_neto" step="0.1" min="0"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('peso_neto') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('peso_neto') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="destino" class="block text-sm font-semibold text-slate-700 mb-1.5">Destino</label>
                            <input type="text" id="destino" wire:model="destino" maxlength="100"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('destino') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('destino') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_carga" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Carga <span class="text-red-500">*</span></label>
                            <input type="date" id="fecha_carga" wire:model="fecha_carga" max="{{ now()->toDateString() }}"
                                class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('fecha_carga') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                            @error('fecha_carga') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-2 justify-end">
                        @if ($carga_id)
                            <button type="button" wire:click="resetCampos"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                                ✕ Cancelar
                            </button>
                        @endif
                        @canany(['crear-cargas', 'editar-cargas'])
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                            ✓ {{ $carga_id ? 'Actualizar' : 'Guardar' }}
                        </button>
                        @endcanany
                    </div>
                </form>
            </div>
        </div>
        @endcanany
    @elseif($tab_activo === 'listado')
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6">
                <x-search-input placeholder="Buscar por lote, ticket, destino, chofer o fecha..." />

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Lote</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Categoría</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Chofer</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Parte Diario</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Ticket</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Bruto</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Tara</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Neto</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Destino</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($cargas as $carga)
                                <tr wire:key="row-{{ $carga->id_carga }}" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $carga->id_carga }}</span></td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $carga->lote->id_lote ?? $carga->id_lote }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $carga->categoriaMadera->nombre ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">
                                        @if($carga->chofer)
                                            {{ $carga->chofer->apellido }} {{ $carga->chofer->nombre }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $carga->id_parte_diario ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $carga->ticket ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-right text-slate-600">{{ number_format($carga->peso_bruto ?? 0, 2) }}</td>
                                    <td class="px-3 py-2.5 text-right text-slate-600">{{ number_format($carga->tara ?? 0, 2) }}</td>
                                    <td class="px-3 py-2.5 text-right font-semibold text-slate-800">{{ number_format($carga->peso_neto ?? 0, 2) }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ $carga->destino ?? '-' }}</td>
                                    <td class="px-3 py-2.5 text-slate-600">{{ \Carbon\Carbon::parse($carga->fecha_carga)->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2.5 text-right">
                                        <x-action-buttons
                                            editWireClick="editar({{ $carga->id_carga }})"
                                            deleteWireClick="eliminar({{ $carga->id_carga }})"
                                            deleteMessage="¿Eliminar esta carga?"
                                            :canEdit="auth()->user()->can('editar-cargas')"
                                            :canDelete="auth()->user()->can('eliminar-cargas')" />
                                    </td>
                                </tr>
                            @empty
                                <x-empty-state :colspan="12" message="No hay cargas registradas." />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($cargas->hasPages())
                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-slate-600">
                            Mostrando <span class="font-semibold">{{ $cargas->firstItem() }}</span> a
                            <span class="font-semibold">{{ $cargas->lastItem() }}</span> de
                            <span class="font-semibold">{{ $cargas->total() }}</span> cargas
                        </div>
                        <nav class="flex gap-1">
                            @if ($cargas->onFirstPage())
                                <span class="px-3 py-2 text-slate-400 bg-slate-100 rounded-lg text-sm">← Anterior</span>
                            @else
                                <button wire:click="previousPage" class="px-3 py-2 text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg text-sm transition-colors">← Anterior</button>
                            @endif

                            @foreach ($cargas->getUrlRange(1, $cargas->lastPage()) as $page => $url)
                                @if ($page == $cargas->currentPage())
                                    <span class="px-3 py-2 text-white bg-brand rounded-lg text-sm" wire:key="page-{{ $page }}">{{ $page }}</span>
                                @else
                                    <button wire:click="gotoPage({{ $page }})" class="px-3 py-2 text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg text-sm transition-colors" wire:key="page-{{ $page }}">{{ $page }}</button>
                                @endif
                            @endforeach

                            @if ($cargas->hasMorePages())
                                <button wire:click="nextPage" class="px-3 py-2 text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg text-sm transition-colors">Siguiente →</button>
                            @else
                                <span class="px-3 py-2 text-slate-400 bg-slate-100 rounded-lg text-sm">Siguiente →</span>
                            @endif
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
