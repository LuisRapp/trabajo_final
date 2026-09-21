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

    <div class="mb-6 flex gap-0">
        @canany(['crear-mantenimientos', 'editar-mantenimientos'])
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-sm transition-all {{ $tab_activo === 'nuevo' ? 'text-white bg-pino border-pino' : 'bg-white text-tinta-suave border-arena hover:bg-corteza-suave' }}">
            <flux:icon.plus class="size-4" />
            Nuevo Mantenimiento
        </button>
        @endcanany
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-sm transition-all {{ $tab_activo === 'listado' ? 'text-white bg-pino border-pino' : 'bg-white text-tinta-suave border-arena hover:bg-corteza-suave' }} {{ !auth()->user()->canAny(['crear-mantenimientos', 'editar-mantenimientos']) ? 'rounded-l-sm' : '' }}">
            <flux:icon.list-bullet class="size-4" />
            Listado de Mantenimientos
        </button>
    </div>

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

                <form wire:submit.prevent="guardar">
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
                            <input type="date" wire:model="fecha_inicio" class="form-input @error('fecha_inicio') ring-2 ring-tierra @enderror">
                            @error('fecha_inicio') <p class="mt-1 text-sm text-tierra">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-2">Fecha Programada</label>
                            <input type="date" wire:model="fecha_programada" class="form-input @error('fecha_programada') ring-2 ring-tierra @enderror">
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
                        <input type="text" wire:model.live="busqueda" placeholder="Buscar por maquinaria, tipo, estado o costo..." class="flex-1 bg-transparent border-0 focus:ring-0 focus:outline-none text-tinta placeholder-arena-oscura">
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
                                            $isVencido = strtolower(trim($mantenimiento->estado ?? '')) === 'vencido';
                                        @endphp
                                        <div class="flex gap-1 justify-center">
                                            @if($isProgramado)
                                                @can('confirmar-mantenimiento')
                                                <x-ui.button size="sm" variant="secondary" icon="check" wire:click="confirmarMantenimiento({{ $mantenimiento->id_mantenimiento }})" title="Confirmar realizacion" />
                                                @endcan
                                            @endif
                                            @if($isVencido)
                                                @can('reprogramar-mantenimiento')
                                                <x-ui.button size="sm" variant="secondary" icon="calendar-date-range" wire:click="reprogramarMantenimiento({{ $mantenimiento->id_mantenimiento }})" title="Reprogramar" />
                                                @endcan
                                            @endif
                                            @can('editar-mantenimientos')
                                            <x-ui.button size="sm" variant="secondary" icon="check" wire:click="abrirModalCompletar({{ $mantenimiento->id_mantenimiento }})" title="Completar" class="{{ ($isCompletado || $isVencido) ? 'opacity-50 cursor-not-allowed' : '' }}" @if($isCompletado || $isVencido) disabled @endif />
                                            <x-ui.button size="sm" variant="secondary" icon="pencil-square" wire:click="editar({{ $mantenimiento->id_mantenimiento }})" title="Editar" />
                                            @endcan
                                            @can('eliminar-mantenimientos')
                                            <x-ui.button size="sm" variant="danger" icon="trash" wire:click="eliminar({{ $mantenimiento->id_mantenimiento }})" wire:confirm="¿Esta seguro de eliminar este mantenimiento?" title="Eliminar" />
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
