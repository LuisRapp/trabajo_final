<!-- Listado de Partes Diarios Registrados -->
<div id="listado-partes" role="tabpanel" aria-labelledby="listado-tab" class="tab-pane-content">
    <x-ui.card>
        <div class="px-6 py-4 border-b border-arena">
            <h5 class="text-lg font-semibold text-tinta mb-0">Partes Diarios Registrados</h5>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-tinta mb-2">Buscar por Propietario</label>
                    <input type="text" wire:model.live.debounce.400ms="busqueda" class="form-input" placeholder="Ej: Juan Pérez...">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-tinta mb-2">Buscar por Fecha</label>
                    <input type="date" wire:model.live="busqueda_fecha" class="form-input">
                </div>
            </div>

            @if($partes && count($partes) > 0)
                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Lote</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Observaciones</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($partes as $parte)
                                <tr wire:key="row-{{ $parte->id_parte_diario }}">
                                    <td><x-ui.badge variant="neutral">#{{ $parte->id_parte_diario }}</x-ui.badge></td>
                                    <td>{{ $parte->lote?->propietario ?? '-' }}</td>
                                    <td>{{ $parte->fecha ? \Carbon\Carbon::parse($parte->fecha)->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        @if($parte->es_dia_caido)
                                            <x-ui.badge variant="warning">Día Caído</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="success">Producción</x-ui.badge>
                                        @endif
                                    </td>
                                    <td><small class="text-tinta-suave">{{ $parte->observaciones ? \Illuminate\Support\Str::limit($parte->observaciones, 40) : '-' }}</small></td>
                                    <td class="text-center">
                                        <div class="inline-flex gap-2">
                                            @can('editar-partes-diarios')
                                                <x-ui.button variant="secondary" size="sm" icon="pencil-square" wire:click.prevent="editar({{ $parte->id_parte_diario }})" title="Editar" />
                                            @endcan
                                            @can('eliminar-partes-diarios')
                                                <x-ui.button variant="danger" size="sm" icon="trash" wire:click.prevent="eliminar({{ $parte->id_parte_diario }})" wire:confirm="¿Está seguro de eliminar este parte diario?" title="Eliminar" />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-ui.table-container>
                @isset($partes)
                    <div class="mt-6">
                        {{ $partes->links('pagination::tailwind') }}
                    </div>
                @endisset
            @else
                <x-empty-state message="No hay partes diarios registrados." icon="clipboard-document-list" />
            @endif
        </div>
    </x-ui.card>
</div>
