<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-truck"></i> Cargas
        </h1>
    </div>

    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span class="flex-1 font-medium">{{ session('message') }}</span>
            <button type="button" class="text-green-600 hover:text-green-800" @click="open = false">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <div class="mb-6 flex gap-0">
        @canany(['crear-cargas', 'editar-cargas'])
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all {{ $tab_activo === 'nuevo' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
            style="{{ $tab_activo === 'nuevo' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : '' }}">
            <i class="bi bi-plus-circle"></i> Nueva Carga
        </button>
        @endcanany
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all {{ $tab_activo === 'listado' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}"
            style="{{ $tab_activo === 'listado' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : '' }}">
            <i class="bi bi-list-ul"></i> Listado de Cargas
        </button>
    </div>

    @if($tab_activo === 'nuevo')
        @canany(['crear-cargas', 'editar-cargas'])
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                    <h5 class="flex items-center gap-2 text-lg font-semibold text-slate-800 mb-0">
                        <i class="bi bi-{{ $carga_id ? 'pencil-square' : 'plus-circle' }}"></i> 
                        {{ $carga_id ? 'Editar Carga' : 'Nueva Carga' }}
                    </h5>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="guardar">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Lote <span class="text-red-500">*</span></label>
                                <select wire:model="id_lote" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('id_lote') ring-2 ring-red-500 @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($lotes as $lote)
                                        <option value="{{ $lote->id_lote }}">{{ $lote->id_lote }}</option>
                                    @endforeach
                                </select>
                                @error('id_lote') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Categoría Madera</label>
                                <select wire:model="id_categoria_madera" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('id_categoria_madera') ring-2 ring-red-500 @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id_categoria_madera }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('id_categoria_madera') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Chofer (ID)</label>
                                <input type="number" wire:model="id_chofer" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('id_chofer') ring-2 ring-red-500 @enderror">
                                @error('id_chofer') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Parte Diario</label>
                                <select wire:model="id_parte_diario" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('id_parte_diario') ring-2 ring-red-500 @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($partes as $parte)
                                        <option value="{{ $parte->id_parte_diario }}">{{ $parte->id_parte_diario }}</option>
                                    @endforeach
                                </select>
                                @error('id_parte_diario') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Ticket</label>
                                <input type="text" wire:model="ticket" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('ticket') ring-2 ring-red-500 @enderror" maxlength="20">
                                @error('ticket') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Peso Bruto</label>
                                <input type="number" wire:model="peso_bruto" step="0.1" min="0" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('peso_bruto') ring-2 ring-red-500 @enderror">
                                @error('peso_bruto') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tara</label>
                                <input type="number" wire:model="tara" step="0.1" min="0" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('tara') ring-2 ring-red-500 @enderror">
                                @error('tara') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Peso Neto</label>
                                <input type="number" wire:model="peso_neto" step="0.1" min="0" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('peso_neto') ring-2 ring-red-500 @enderror">
                                @error('peso_neto') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Destino</label>
                                <input type="text" wire:model="destino" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('destino') ring-2 ring-red-500 @enderror" maxlength="100">
                                @error('destino') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Fecha Carga <span class="text-red-500">*</span></label>
                                <input type="date" wire:model="fecha_carga" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('fecha_carga') ring-2 ring-red-500 @enderror" max="{{ now()->toDateString() }}">
                                @error('fecha_carga') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex gap-2 justify-end">
                            @if ($carga_id)
                                <button type="button" wire:click="resetCampos" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium text-sm">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            @endif
                            @canany(['crear-cargas', 'editar-cargas'])
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg transition-colors font-medium text-sm" style="background-color: #2d7a4f;" onmouseover="this.style.backgroundColor='#245c3d'" onmouseout="this.style.backgroundColor='#2d7a4f'">
                                <i class="bi bi-check-circle"></i> {{ $carga_id ? 'Actualizar' : 'Guardar' }}
                            </button>
                            @endcanany
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcanany
    @elseif($tab_activo === 'listado')
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                <div class="p-6">
                    <!-- Buscador -->
                    <div class="mb-6">
                        <div class="flex items-center gap-2 px-4 py-3 border border-slate-300 rounded-lg bg-slate-50">
                            <i class="bi bi-search text-slate-500"></i>
                            <input type="text" wire:model.live="busqueda" placeholder="Buscar por lote, ticket, destino, chofer o fecha..." class="flex-1 bg-slate-50 border-0 focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400">
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50">
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Lote</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Categoría</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Chofer</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Parte Diario</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Ticket</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase text-slate-600">Bruto</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase text-slate-600">Tara</th>
                                    <th class="px-3 py-3 text-right text-xs font-semibold uppercase text-slate-600">Neto</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Destino</th>
                                    <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Fecha</th>
                                    <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($cargas as $carga)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $carga->id_carga }}</span></td>
                                        <td class="px-3 py-3 text-slate-600">{{ $carga->lote->id_lote ?? $carga->id_lote }}</td>
                                        <td class="px-3 py-3 text-slate-600">{{ $carga->categoriaMadera->nombre ?? '-' }}</td>
                                        <td class="px-3 py-3 text-slate-600">
                                            @if($carga->chofer)
                                                {{ $carga->chofer->apellido }} {{ $carga->chofer->nombre }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-slate-600">{{ $carga->id_parte_diario ?? '-' }}</td>
                                        <td class="px-3 py-3 text-slate-600">{{ $carga->ticket ?? '-' }}</td>
                                        <td class="px-3 py-3 text-right text-slate-600">{{ number_format($carga->peso_bruto ?? 0, 2) }}</td>
                                        <td class="px-3 py-3 text-right text-slate-600">{{ number_format($carga->tara ?? 0, 2) }}</td>
                                        <td class="px-3 py-3 text-right"><strong class="text-slate-900">{{ number_format($carga->peso_neto ?? 0, 2) }}</strong></td>
                                        <td class="px-3 py-3 text-slate-600">{{ $carga->destino ?? '-' }}</td>
                                        <td class="px-3 py-3 text-slate-600">{{ \Carbon\Carbon::parse($carga->fecha_carga)->format('d/m/Y') }}</td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="flex gap-1 justify-center">
                                                @can('editar-cargas')
                                                <button wire:click="editar({{ $carga->id_carga }})" @click="$set('tab_activo', 'nuevo')" title="Editar" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200">
                                                    <i class="bi bi-pencil text-sm"></i>
                                                </button>
                                                @endcan
                                                @can('eliminar-cargas')
                                                <button wire:click="eliminar({{ $carga->id_carga }})" onclick="return confirm('¿Eliminar esta carga?')" title="Eliminar" class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200">
                                                    <i class="bi bi-trash text-sm"></i>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="px-3 py-8 text-center">
                                            <i class="bi bi-inbox text-slate-300 block mb-2" style="font-size: 2rem;"></i>
                                            <p class="text-slate-500 font-medium">No hay cargas registradas.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($cargas->hasPages())
                        <div class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-slate-600">
                                Mostrando <span class="font-semibold">{{ $cargas->firstItem() }}</span> a 
                                <span class="font-semibold">{{ $cargas->lastItem() }}</span> de 
                                <span class="font-semibold">{{ $cargas->total() }}</span> cargas
                            </div>
                            <nav class="flex gap-1">
                                {{-- Previous Page Link --}}
                                @if ($cargas->onFirstPage())
                                    <span class="px-3 py-2 text-slate-400 bg-slate-100 rounded-lg">← Anterior</span>
                                @else
                                    <button wire:click="previousPage" class="px-3 py-2 text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors">← Anterior</button>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($cargas->getUrlRange(1, $cargas->lastPage()) as $page => $url)
                                    @if ($page == $cargas->currentPage())
                                        <span class="px-3 py-2 text-white rounded-lg" style="background-color: #2d7a4f;">{{ $page }}</span>
                                    @else
                                        <button wire:click="gotoPage({{ $page }})" class="px-3 py-2 text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors">{{ $page }}</button>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($cargas->hasMorePages())
                                    <button wire:click="nextPage" class="px-3 py-2 text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg transition-colors">Siguiente →</button>
                                @else
                                    <span class="px-3 py-2 text-slate-400 bg-slate-100 rounded-lg">Siguiente →</span>
                                @endif
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<!-- JavaScript para cambiar entre pestañas -->
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('cargaGuardada', () => {
            // Livewire actualizará automáticamente
        });
    });
</script>
