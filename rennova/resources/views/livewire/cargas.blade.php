<div class="w-full">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.truck class="size-6" />
            Cargas
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-5">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    <!-- Tabs -->
    <div class="mb-6 flex gap-0">
        @canany(['crear-cargas', 'editar-cargas'])
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-sm transition-all {{ $tab_activo === 'nuevo' ? 'text-blanco bg-pino border-pino' : 'bg-blanco text-tinta border-arena hover:bg-corteza-suave' }}">
            <flux:icon.plus class="size-4" />
            Nueva Carga
        </button>
        @endcanany
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-sm transition-all {{ $tab_activo === 'listado' ? 'text-blanco bg-pino border-pino' : 'bg-blanco text-tinta border-arena hover:bg-corteza-suave' }}">
            <flux:icon.list-bullet class="size-4" />
            Listado de Cargas
        </button>
    </div>

    @if($tab_activo === 'nuevo')
        @canany(['crear-cargas', 'editar-cargas'])
        <x-ui.card class="overflow-hidden mb-5">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="text-lg font-semibold text-tinta flex items-center gap-2">
                    @if($carga_id)
                        <flux:icon.pencil-square class="size-5" />
                        Editar Carga
                    @else
                        <flux:icon.plus class="size-5" />
                        Nueva Carga
                    @endif
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <label for="id_lote" class="block text-sm font-semibold text-tinta mb-1.5">Lote <span class="text-tierra">*</span></label>
                            <select id="id_lote" wire:model="id_lote"
                                class="form-input @error('id_lote') border-tierra bg-tierra-suave @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($lotes as $lote)
                                    <option value="{{ $lote->id_lote }}" wire:key="option-{{ $lote->id_lote }}">{{ $lote->id_lote }}</option>
                                @endforeach
                            </select>
                            @error('id_lote') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="id_categoria_madera" class="block text-sm font-semibold text-tinta mb-1.5">Categoría Madera</label>
                            <select id="id_categoria_madera" wire:model="id_categoria_madera"
                                class="form-input">
                                <option value="">Seleccione...</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id_categoria_madera }}" wire:key="option-{{ $cat->id_categoria_madera }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                            @error('id_categoria_madera') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="id_chofer" class="block text-sm font-semibold text-tinta mb-1.5">Chofer (ID)</label>
                            <input type="number" id="id_chofer" wire:model="id_chofer"
                                class="form-input @error('id_chofer') border-tierra bg-tierra-suave @enderror">
                            @error('id_chofer') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="id_parte_diario" class="block text-sm font-semibold text-tinta mb-1.5">Parte Diario</label>
                            <select id="id_parte_diario" wire:model="id_parte_diario"
                                class="form-input">
                                <option value="">Seleccione...</option>
                                @foreach($partes as $parte)
                                    <option value="{{ $parte->id_parte_diario }}" wire:key="option-{{ $parte->id_parte_diario }}">{{ $parte->id_parte_diario }}</option>
                                @endforeach
                            </select>
                            @error('id_parte_diario') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                        <div>
                            <label for="ticket" class="block text-sm font-semibold text-tinta mb-1.5">Ticket</label>
                            <input type="text" id="ticket" wire:model="ticket" maxlength="20"
                                class="form-input @error('ticket') border-tierra bg-tierra-suave @enderror">
                            @error('ticket') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="peso_bruto" class="block text-sm font-semibold text-tinta mb-1.5">Peso Bruto</label>
                            <input type="number" id="peso_bruto" wire:model="peso_bruto" step="0.1" min="0"
                                class="form-input @error('peso_bruto') border-tierra bg-tierra-suave @enderror">
                            @error('peso_bruto') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="tara" class="block text-sm font-semibold text-tinta mb-1.5">Tara</label>
                            <input type="number" id="tara" wire:model="tara" step="0.1" min="0"
                                class="form-input @error('tara') border-tierra bg-tierra-suave @enderror">
                            @error('tara') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="peso_neto" class="block text-sm font-semibold text-tinta mb-1.5">Peso Neto</label>
                            <input type="number" id="peso_neto" wire:model="peso_neto" step="0.1" min="0"
                                class="form-input @error('peso_neto') border-tierra bg-tierra-suave @enderror">
                            @error('peso_neto') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="id_cliente" class="block text-sm font-semibold text-tinta mb-1.5">Cliente</label>
                            <select id="id_cliente" wire:model="id_cliente"
                                class="form-input @error('id_cliente') border-tierra bg-tierra-suave @enderror">
                                <option value="">-- Seleccionar cliente --</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id_cliente }}">{{ $cliente->razon_social }}</option>
                                @endforeach
                            </select>
                            @error('id_cliente') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="fecha_carga" class="block text-sm font-semibold text-tinta mb-1.5">Fecha Carga <span class="text-tierra">*</span></label>
                            <input type="date" id="fecha_carga" wire:model="fecha_carga" max="{{ now()->toDateString() }}"
                                class="form-input @error('fecha_carga') border-tierra bg-tierra-suave @enderror">
                            @error('fecha_carga') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex gap-2 justify-end">
                        @if ($carga_id)
                            <x-ui.button variant="secondary" icon="x-mark" wire:click="resetCampos">
                                Cancelar
                            </x-ui.button>
                        @endif
                        @canany(['crear-cargas', 'editar-cargas'])
                        <x-ui.button variant="primary" icon="check" type="submit">
                            {{ $carga_id ? 'Actualizar' : 'Guardar' }}
                        </x-ui.button>
                        @endcanany
                    </div>
                </form>
            </div>
        </x-ui.card>
        @endcanany
    @elseif($tab_activo === 'listado')
        <x-ui.card class="overflow-hidden">
            <div class="p-6">
                <div class="relative max-w-md mb-4">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-tinta-suave">
                        <flux:icon.magnifying-glass class="size-4" />
                    </span>
                    <input type="text" wire:model.live="busqueda"
                        class="form-input pl-10"
                        placeholder="Buscar por lote, ticket, cliente, chofer o fecha...">
                </div>

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Lote</th>
                                <th>Categoría</th>
                                <th>Chofer</th>
                                <th>Parte Diario</th>
                                <th>Ticket</th>
                                <th class="text-right">Bruto</th>
                                <th class="text-right">Tara</th>
                                <th class="text-right">Neto</th>
                                <th>Destino</th>
                                <th>Fecha</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cargas as $carga)
                                <tr wire:key="row-{{ $carga->id_carga }}">
                                    <td><x-ui.badge variant="neutral">{{ $carga->id_carga }}</x-ui.badge></td>
                                    <td class="text-tinta-suave">{{ $carga->lote->id_lote ?? $carga->id_lote }}</td>
                                    <td class="text-tinta-suave">{{ $carga->categoriaMadera->nombre ?? '-' }}</td>
                                    <td class="text-tinta-suave">
                                        @if($carga->chofer)
                                            {{ $carga->chofer->apellido }} {{ $carga->chofer->nombre }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-tinta-suave">{{ $carga->id_parte_diario ?? '-' }}</td>
                                    <td class="text-tinta-suave">{{ $carga->ticket ?? '-' }}</td>
                                    <td class="text-right text-tinta-suave">{{ number_format($carga->peso_bruto ?? 0, 2) }}</td>
                                    <td class="text-right text-tinta-suave">{{ number_format($carga->tara ?? 0, 2) }}</td>
                                    <td class="text-right font-semibold text-tinta">{{ number_format($carga->peso_neto ?? 0, 2) }}</td>
                                    <td class="text-tinta-suave">{{ $carga->cliente->razon_social ?? '-' }}</td>
                                    <td class="text-tinta-suave">{{ \Carbon\Carbon::parse($carga->fecha_carga)->format('d/m/Y') }}</td>
                                    <td class="text-right">
                                        <div class="inline-flex items-center gap-1">
                                            @can('editar-cargas')
                                            <x-ui.button variant="ghost" size="sm" icon="pencil-square" wire:click="editar({{ $carga->id_carga }})" title="Editar" />
                                            @endcan
                                            @can('eliminar-cargas')
                                            <x-ui.button variant="ghost" size="sm" icon="trash" wire:click="eliminar({{ $carga->id_carga }})" onclick="return confirm('¿Eliminar esta carga?')" title="Eliminar" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-12 text-tinta-suave">
                                        No hay cargas registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>

                @if($cargas->hasPages())
                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-tinta-suave">
                            Mostrando <span class="font-semibold">{{ $cargas->firstItem() }}</span> a
                            <span class="font-semibold">{{ $cargas->lastItem() }}</span> de
                            <span class="font-semibold">{{ $cargas->total() }}</span> cargas
                        </div>
                        <nav class="flex gap-1">
                            @if ($cargas->onFirstPage())
                                <span class="px-3 py-2 text-tinta-suave bg-corteza-suave rounded-sm text-sm">Anterior</span>
                            @else
                                <button wire:click="previousPage" class="px-3 py-2 text-tinta bg-blanco border border-arena hover:bg-corteza-suave rounded-sm text-sm transition-colors">Anterior</button>
                            @endif

                            @foreach ($cargas->getUrlRange(1, $cargas->lastPage()) as $page => $url)
                                @if ($page == $cargas->currentPage())
                                    <span class="px-3 py-2 text-blanco bg-pino rounded-sm text-sm" wire:key="page-{{ $page }}">{{ $page }}</span>
                                @else
                                    <button wire:click="gotoPage({{ $page }})" class="px-3 py-2 text-tinta bg-blanco border border-arena hover:bg-corteza-suave rounded-sm text-sm transition-colors" wire:key="page-{{ $page }}">{{ $page }}</button>
                                @endif
                            @endforeach

                            @if ($cargas->hasMorePages())
                                <button wire:click="nextPage" class="px-3 py-2 text-tinta bg-blanco border border-arena hover:bg-corteza-suave rounded-sm text-sm transition-colors">Siguiente</button>
                            @else
                                <span class="px-3 py-2 text-tinta-suave bg-corteza-suave rounded-sm text-sm">Siguiente</span>
                            @endif
                        </nav>
                    </div>
                @endif
            </div>
        </x-ui.card>
    @endif
</div>
