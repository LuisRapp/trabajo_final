<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <x-ui.card class="mb-6">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h4 class="mb-1 text-xl font-bold text-tinta flex items-center gap-2">
                        <flux:icon.wrench-screwdriver class="size-5" />
                        Configurando Kit: <strong>{{ $kit->nombre_kit }}</strong>
                    </h4>
                    <p class="text-tinta-suave flex items-center gap-2">
                        <flux:icon.cog class="size-4" />
                        Tipo de Maquinaria:
                        <x-ui.badge variant="info">{{ $kit->tipoMaquinaria->nombre }}</x-ui.badge>
                    </p>
                </div>
                <a href="{{ route('kits-preventivos.index') }}" class="btn-secondary inline-flex items-center gap-1.5">
                    <flux:icon.arrow-left class="size-4" />
                    Volver a Kits
                </a>
            </div>
        </div>
    </x-ui.card>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif

    @if (session()->has('error'))
        <x-ui.alert variant="danger" class="mb-6">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <x-ui.card class="mb-6 overflow-hidden">
        <div class="bg-pino text-white px-6 py-4">
            <h5 class="text-lg font-semibold flex items-center gap-2">
                <flux:icon.plus class="size-5" />
                Añadir Insumo al Kit
            </h5>
        </div>
        <div class="p-6">
            <form wire:submit.prevent="agregarInsumo">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-tinta mb-1.5">Insumo <span class="text-tierra">*</span></label>
                        <select wire:model="insumo_id"
                            class="form-input @error('insumo_id') border-tierra bg-tierra-suave @enderror">
                            <option value="">Seleccione un insumo</option>
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->id_insumo }}" wire:key="option-{{ $insumo->id_insumo }}">
                                    {{ $insumo->nombre }} ({{ $insumo->unidad_medida }})
                                </option>
                            @endforeach
                        </select>
                        @error('insumo_id') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-tinta mb-1.5">Cantidad Necesaria <span class="text-tierra">*</span></label>
                        <input type="number" step="0.1" min="0" wire:model="cantidad_necesaria"
                            class="form-input @error('cantidad_necesaria') border-tierra bg-tierra-suave @enderror"
                            placeholder="0.00">
                        @error('cantidad_necesaria') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end">
                        <x-ui.button type="submit" icon="plus" class="w-full justify-center">
                            Agregar
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </div>
    </x-ui.card>

    <x-ui.card class="overflow-hidden">
        <div class="bg-corteza-suave border-b border-arena px-6 py-4">
            <h5 class="text-lg font-semibold text-tinta flex items-center gap-2">
                <flux:icon.archive-box class="size-5" />
                Insumos en el Kit
            </h5>
        </div>
        <div class="p-6">
            <x-ui.table-container>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Insumo</th>
                            <th>Unidad de Medida</th>
                            <th>Cantidad Necesaria</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($insumosKit as $insumo)
                            <tr wire:key="row-{{ $insumo['id_insumo'] }}">
                                <td><x-ui.badge variant="neutral">{{ $insumo['id_insumo'] }}</x-ui.badge></td>
                                <td class="font-medium text-tinta">{{ $insumo['nombre'] }}</td>
                                <td class="text-tinta-suave">{{ $insumo['unidad_medida'] }}</td>
                                <td>
                                    <x-ui.badge variant="info">
                                        {{ number_format($insumo['cantidad_necesaria'], 2) }} {{ $insumo['unidad_medida'] }}
                                    </x-ui.badge>
                                </td>
                                <td class="text-right">
                                    <x-ui.button size="sm" variant="danger" icon="trash"
                                        wire:click="quitarInsumo({{ $insumo['id_insumo'] }})"
                                        wire:confirm="¿Esta seguro de quitar este insumo del kit?"
                                        title="Quitar">
                                        Quitar
                                    </x-ui.button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-tinta-suave">
                                    <p>No hay insumos en este kit. Agregue insumos usando el formulario superior.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-ui.table-container>
        </div>
    </x-ui.card>
</div>
