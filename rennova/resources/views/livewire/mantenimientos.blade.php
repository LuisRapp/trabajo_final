<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.wrench class="size-6" />
            Mantenimientos
        </h1>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    @if (session()->has('error'))
        <x-ui.alert variant="danger" class="mb-6" :dismissible="false">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <x-tab-nav :tabs="[
        ['value' => 'nuevo', 'label' => 'Nuevo Mantenimiento', 'icon' => 'plus', 'can' => auth()->user()?->canAny(['crear-mantenimientos', 'editar-mantenimientos']) ?? false],
        ['value' => 'listado', 'label' => 'Listado de Mantenimientos', 'icon' => 'list-bullet'],
    ]" activeTab="{{ $tab_activo }}" tabProperty="tab_activo" />

    @if($tab_activo === 'nuevo')
        @canany(['crear-mantenimientos', 'editar-mantenimientos'])
        <x-ui.card class="overflow-hidden">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="flex items-center gap-2 text-lg font-semibold text-tinta mb-0">
                    @if($mantenimiento_id)
                        <flux:icon.pencil-square class="size-5" />
                        Editar Orden
                    @else
                        <flux:icon.plus class="size-5" />
                        Nueva Orden de Mantenimiento
                    @endif
                </h5>
            </div>
            <div class="p-6">
                @if(count($kitPreventivo) > 0)
                    <x-ui.alert variant="info" class="mb-6" :dismissible="false">
                        <p class="font-semibold mb-1">Kit de Mantenimiento Preventivo</p>
                        <p class="text-xs">Se utilizaran los siguientes insumos del kit configurado:</p>
                        <ul class="mb-0 mt-2 list-inside space-y-1 text-sm">
                            @foreach($kitPreventivo as $item)
                                <li wire:key="kit-{{ $loop->index }}">{{ $item['nombre'] ?? 'N/A' }}: {{ number_format($item['cantidad_requerida'], 2) }} unidades</li>
                            @endforeach
                        </ul>
                    </x-ui.alert>
                @elseif($id_maquinaria && $id_tipo_mantenimiento)
                    @php
                        $tipoSeleccionado = $tipos->firstWhere('id_tipo_mantenimiento', $id_tipo_mantenimiento);
                        $esPreventivo = $tipoSeleccionado && $this->esTipoPreventivo($tipoSeleccionado);
                    @endphp
                    @if($esPreventivo)
                        <x-ui.alert variant="warning" class="mb-6" :dismissible="false">
                            <strong>Advertencia:</strong> No hay kit de mantenimiento preventivo configurado para esta maquinaria.
                            <a href="/kits-mantenimiento" class="font-semibold hover:underline">Configurar kit</a>
                        </x-ui.alert>
                    @endif
                @endif

                <form autocomplete="off" wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-2">Maquinaria <span class="text-tierra">*</span></label>
                            <select wire:model.live="id_maquinaria" class="form-input @error('id_maquinaria') ring-2 ring-tierra @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($maquinarias as $maquinaria)
                                    <option value="{{ $maquinaria->id_maquinaria }}" wire:key="option-{{ $maquinaria->id_maquinaria }}">
                                        {{ $maquinaria->modelo }} - {{ $maquinaria->tipoMaquinaria?->nombre ?? 'N/A' }}
                                        @if($maquinaria->umbral_toneladas)
                                            ({{ number_format($maquinaria->toneladas_acumuladas ?? 0, 0) }}/{{ number_format($maquinaria->umbral_toneladas, 0) }} ton)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('id_maquinaria') <p class="mt-1 text-sm text-tierra">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-2">Tipo de Mantenimiento <span class="text-tierra">*</span></label>
                            <select wire:model.live="id_tipo_mantenimiento" class="form-input @error('id_tipo_mantenimiento') ring-2 ring-tierra @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo->id_tipo_mantenimiento }}" wire:key="option-{{ $tipo->id_tipo_mantenimiento }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            @error('id_tipo_mantenimiento') <p class="mt-1 text-sm text-tierra">{{ $message }}</p> @enderror
                            @if($id_tipo_mantenimiento)
                                @php
                                    $tipoSeleccionado = $tipos->firstWhere('id_tipo_mantenimiento', $id_tipo_mantenimiento);
                                @endphp
                                @if($tipoSeleccionado && $this->esTipoPreventivo($tipoSeleccionado))
                                    <small class="text-pino text-xs mt-1 block">
                                        <flux:icon.information-circle class="size-3 inline" />
                                        Se utilizara el kit de mantenimiento preventivo
                                    </small>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-2">Fecha Inicio <span class="text-tierra">*</span></label>
                            <input autocomplete="off" type="date" wire:model="fecha_inicio" class="form-input @error('fecha_inicio') ring-2 ring-tierra @enderror">
                            @error('fecha_inicio') <p class="mt-1 text-sm text-tierra">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-2">Fecha Programada</label>
                            <input autocomplete="off" type="date" wire:model="fecha_programada" class="form-input @error('fecha_programada') ring-2 ring-tierra @enderror">
                            @error('fecha_programada') <p class="mt-1 text-sm text-tierra">{{ $message }}</p> @enderror
                            <small class="text-tinta-suave text-xs mt-1 block">
                                <flux:icon.information-circle class="size-3 inline" />
                                Debe estar dentro de los proximos 7 dias ({{ \Carbon\Carbon::now()->format('d/m/Y') }} - {{ \Carbon\Carbon::now()->addDays(7)->format('d/m/Y') }})
                            </small>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-tinta mb-2">Estado <span class="text-tierra">*</span></label>
                        <select wire:model="estado" class="form-input @error('estado') ring-2 ring-tierra @enderror">
                            <option value="programado">Programado</option>
                            <option value="en curso">En Curso</option>
                        </select>
                        @error('estado') <p class="mt-1 text-sm text-tierra">{{ $message }}</p> @enderror
                        <small class="text-tinta-suave text-xs mt-1 block">La orden se completara desde el listado</small>
                    </div>

                    <div class="flex gap-2 justify-end">
                        @if ($mantenimiento_id)
                            <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="resetCampos">
                                Cancelar
                            </x-ui.button>
                        @endif
                        @canany(['crear-mantenimientos', 'editar-mantenimientos'])
                        <x-ui.button type="submit" icon="check">
                            {{ $mantenimiento_id ? 'Actualizar' : 'Crear Orden' }}
                        </x-ui.button>
                        @endcanany
                    </div>
                </form>
            </div>
        </x-ui.card>
        @endcanany
    @elseif($tab_activo === 'listado')
        <x-ui.card>
            <div class="p-6">
                <div class="mb-6">
                    <div class="flex items-center gap-2 px-4 py-3 border border-arena rounded-sm bg-hueso">
                        <flux:icon.magnifying-glass class="size-4 text-arena-oscura" />
                        <input autocomplete="off" type="text" wire:model.live="busqueda" placeholder="Buscar por maquinaria, tipo, estado o costo..." class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-tinta placeholder-arena-oscura">
                    </div>
                </div>

                <x-ui.table-container>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Maquinaria</th>
                                <th>Tipo</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Programada</th>
                                <th>Fecha Fin</th>
                                <th class="text-right">Costo</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mantenimientos as $mantenimiento)
                                <tr wire:key="row-{{ $mantenimiento->id_mantenimiento }}">
                                    <td><x-ui.badge variant="neutral">{{ $mantenimiento->id_mantenimiento }}</x-ui.badge></td>
                                    <td class="font-semibold text-tinta">{{ $mantenimiento->maquinaria?->modelo ?? 'N/A' }}</td>
                                    <td class="text-tinta-suave">{{ $mantenimiento->tipoMantenimiento?->nombre ?? 'N/A' }}</td>
                                    <td class="text-tinta-suave">{{ $mantenimiento->fecha_inicio ? \Carbon\Carbon::parse($mantenimiento->fecha_inicio)->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        @if($mantenimiento->fecha_programada)
                                            <x-ui.badge variant="info">
                                                {{ \Carbon\Carbon::parse($mantenimiento->fecha_programada)->format('d/m/Y') }}
                                            </x-ui.badge>
                                            @if($mantenimiento->estado === 'programado' && \Carbon\Carbon::parse($mantenimiento->fecha_programada)->isToday())
                                                <flux:icon.exclamation-triangle class="size-4 inline text-resina ml-1" />
                                            @endif
                                        @else
                                            <span class="text-arena-oscura">-</span>
                                        @endif
                                    </td>
                                    <td class="text-tinta-suave">{{ $mantenimiento->fecha_fin ? \Carbon\Carbon::parse($mantenimiento->fecha_fin)->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="text-right text-tinta-suave">${{ number_format($mantenimiento->costo_total, 2, ',', '.') }}</td>
                                    <td>
                                        @php
                                            $estadoVariant = match(strtolower(trim($mantenimiento->estado ?? ''))) {
                                                'completado' => 'success',
                                                'en curso' => 'warning',
                                                'vencido' => 'danger',
                                                default => 'neutral',
                                            };
                                        @endphp
                                        <x-ui.badge variant="{{ $estadoVariant }}">{{ ucfirst($mantenimiento->estado) }}</x-ui.badge>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $isCompletado = strtolower(trim($mantenimiento->estado ?? '')) === 'completado';
                                            $isProgramado = strtolower(trim($mantenimiento->estado ?? '')) === 'programado';
                                            $isEnCurso = strtolower(trim($mantenimiento->estado ?? '')) === 'en curso';
                                            $isVencido = strtolower(trim($mantenimiento->estado ?? '')) === 'vencido';
                                        @endphp
                                        <div class="flex gap-1 justify-center">
                                            @if($isProgramado)
                                                @can('confirmar-mantenimiento')
                                                <x-ui.button size="sm" variant="secondary" icon="check" wire:click="abrirModalAprobar({{ $mantenimiento->id_mantenimiento }})" title="Aprobar y verificar stock del kit" />
                                                @endcan
                                            @endif
                                            @if($isVencido)
                                                @can('reprogramar-mantenimiento')
                                                <x-ui.button size="sm" variant="secondary" icon="calendar-date-range" wire:click="reprogramarMantenimiento({{ $mantenimiento->id_mantenimiento }})" title="Reprogramar" />
                                                @endcan
                                            @endif
                                            @if($isEnCurso)
                                                @can('completar-mantenimiento')
                                                <x-ui.button size="sm" variant="secondary" icon="flag" wire:click="abrirModalCompletar({{ $mantenimiento->id_mantenimiento }})" title="Completar y cerrar la orden">
                                                    Completar
                                                </x-ui.button>
                                                @endcan
                                            @endif
                                            @unless($isCompletado)
                                                @can('editar-mantenimientos')
                                                <x-ui.button size="sm" variant="secondary" icon="pencil-square" wire:click="editar({{ $mantenimiento->id_mantenimiento }})" title="Editar" />
                                                @endcan
                                            @endunless
                                            @can('eliminar-mantenimientos')
                                            <x-ui.confirmador accion="eliminar" :parametros="[$mantenimiento->id_mantenimiento]"
                                                mensaje="¿Eliminar el mantenimiento #{{ $mantenimiento->id_mantenimiento }} de «{{ $mantenimiento->maquinaria?->modelo ?? 'la maquinaria' }}»?"
                                                detalle="Tipo: {{ $mantenimiento->tipoMantenimiento?->nombre ?? 'N/A' }}."
                                            />
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-8 text-tinta-suave">
                                        No hay mantenimientos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-ui.table-container>
            </div>
        </x-ui.card>
    @endif

    <livewire:completar-orden-modal />

    {{-- Aprobar orden: verifica el kit antes de pasarla a "en curso" --}}
    @if ($modal_aprobar && $orden_aprobar)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" wire:key="modal-aprobar">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-sm border border-arena bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-arena bg-hueso px-5 py-4">
                    <h2 class="flex items-center gap-2 text-lg font-semibold text-tinta">
                        <flux:icon.check class="size-5" />
                        Aprobar orden #{{ $orden_aprobar->id_mantenimiento }}
                    </h2>
                    <button type="button" class="text-tinta-suave hover:text-tinta" wire:click="cerrarModalAprobar" title="Cerrar">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <div class="space-y-4 px-5 py-5">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-tinta-suave">Maquinaria</p>
                            <p class="font-medium text-tinta">{{ $orden_aprobar->maquinaria?->modelo ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-tinta-suave">Tipo</p>
                            <p class="font-medium text-tinta">{{ $orden_aprobar->tipoMantenimiento?->nombre ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-tinta-suave">Programada</p>
                            <p class="font-medium text-tinta">{{ $orden_aprobar->fecha_programada ? \Carbon\Carbon::parse($orden_aprobar->fecha_programada)->format('d/m/Y') : '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-tinta-suave">Estado</p>
                            <p class="font-medium text-tinta">{{ ucfirst($orden_aprobar->estado) }}</p>
                        </div>
                    </div>

                    @if ($verificacion_stock)
                        @if ($verificacion_stock['puede_aprobar'])
                            <x-ui.alert variant="success" :dismissible="false">
                                Todos los insumos obligatorios del kit están disponibles.
                            </x-ui.alert>
                        @else
                            <x-ui.alert variant="warning" :dismissible="false">
                                Faltan insumos del kit: al aprobar se genera la propuesta de compra de los obligatorios.
                            </x-ui.alert>
                        @endif

                        @if (count($verificacion_stock['kit']) === 0)
                            <p class="rounded-sm border border-arena bg-hueso py-4 text-center text-sm text-tinta-suave">
                                La maquinaria no tiene kit preventivo configurado.
                            </p>
                        @else
                            <x-ui.table-container>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Insumo</th>
                                            <th class="text-right">Requerido</th>
                                            <th class="text-right">Disponible</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($verificacion_stock['kit'] as $item)
                                            @php
                                                $faltan = $item['cantidad_requerida'] - $item['stock_disponible'];
                                                $enFalta = in_array($item['insumo_id'], array_column($verificacion_stock['insuficientes'], 'insumo_id'), true);
                                            @endphp
                                            <tr wire:key="kit-{{ $item['insumo_id'] }}" class="{{ $enFalta ? 'bg-tierra-suave' : '' }}">
                                                <td class="text-tinta">{{ $item['nombre'] }}</td>
                                                <td class="text-right text-tinta-suave">{{ number_format($item['cantidad_requerida'], 2, ',', '.') }}</td>
                                                <td class="text-right text-tinta-suave">{{ number_format($item['stock_disponible'], 2, ',', '.') }}</td>
                                                <td>
                                                    @if ($faltan <= 0)
                                                        <x-ui.badge variant="success">Disponible</x-ui.badge>
                                                    @elseif (! $item['es_obligatorio'])
                                                        <x-ui.badge variant="neutral">Opcional: faltan {{ number_format($faltan, 2, ',', '.') }}</x-ui.badge>
                                                    @else
                                                        <x-ui.badge variant="danger">Faltan {{ number_format($faltan, 2, ',', '.') }}</x-ui.badge>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </x-ui.table-container>
                        @endif
                    @else
                        <x-ui.alert variant="info" :dismissible="false">
                            Es un mantenimiento correctivo: se aprueba sin verificar insumos.
                        </x-ui.alert>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-arena bg-hueso px-5 py-3">
                    <x-ui.button variant="secondary" icon="x-mark" wire:click="cerrarModalAprobar">Cancelar</x-ui.button>
                    <x-ui.button variant="primary" icon="check" wire:click="aprobarOrden">Aprobar y poner en curso</x-ui.button>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.querySelector('[x-data*="open"]');
            if (successAlert && window.Alpine) {
                setTimeout(() => {
                    successAlert.remove?.();
                }, 3000);
            }
        });
    </script>
</div>
