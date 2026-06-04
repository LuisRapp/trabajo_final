<!-- Listado de Partes Diarios Registrados -->
<div id="listado-partes" role="tabpanel" aria-labelledby="listado-tab" class="tab-pane-content">
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-slate-200">
        <div class="bg-slate-100 px-6 py-4 border-b border-slate-200">
            <h5 class="text-lg font-semibold text-slate-900 mb-0">Partes Diarios Registrados</h5>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Buscar por Propietario</label>
                    <input type="text" wire:model.live.debounce.400ms="busqueda" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none" placeholder="Ej: Juan Pérez...">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Buscar por Fecha</label>
                    <input type="date" wire:model.live="busqueda_fecha" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none">
                </div>
            </div>
            
            @if($partes && count($partes) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead class="bg-slate-100 border-b border-slate-300">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">ID</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Lote</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Fecha</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Tipo</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Observaciones</th>
                                <th class="px-4 py-2 text-center font-semibold text-slate-900">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($partes as $parte)
                                <tr class="border-b border-slate-200 hover:bg-slate-50">
                                    <td class="px-4 py-2"><span class="inline-block px-3 py-1 bg-slate-200 text-slate-800 text-xs font-medium rounded">#{{ $parte->id_parte_diario }}</span></td>
                                    <td class="px-4 py-2">{{ $parte->lote?->propietario ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $parte->fecha ? \Carbon\Carbon::parse($parte->fecha)->format('d/m/Y') : '-' }}</td>
                                    <td class="px-4 py-2">
                                        @if($parte->es_dia_caido)
                                            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded"><i class="bi bi-calendar-x mr-1"></i>Día Caído</span>
                                        @else
                                            <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded"><i class="bi bi-truck mr-1"></i>Producción</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2"><small>{{ $parte->observaciones ? \Illuminate\Support\Str::limit($parte->observaciones, 40) : '-' }}</small></td>
                                    <td class="px-4 py-2 text-center">
                                        <div class="inline-flex gap-2">
                                            @can('editar-partes-diarios')
                                            <button type="button" class="px-3 py-1 border border-blue-500 text-blue-600 rounded text-sm hover:bg-blue-50 transition-colors" wire:click.prevent="editar({{ $parte->id_parte_diario }})" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            @endcan
                                            @can('eliminar-partes-diarios')
                                            <button type="button" class="px-3 py-1 border border-red-500 text-red-600 rounded text-sm hover:bg-red-50 transition-colors" wire:click.prevent="eliminar({{ $parte->id_parte_diario }})" wire:confirm="¿Está seguro de eliminar este parte diario?" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @isset($partes)
                    <div class="mt-6">
                        {{ $partes->links('pagination::tailwind') }}
                    </div>
                @endisset
            @else
                <div class="text-center py-12">
                    <i class="bi bi-inbox text-6xl text-slate-300 block mb-4"></i>
                    <p class="text-slate-600">No hay partes diarios registrados.</p>
                </div>
            @endif
        </div>
    </div>
</div>
