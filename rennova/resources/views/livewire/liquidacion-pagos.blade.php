<div class="w-full">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.calculator class="size-7" />
            Liquidación de Pagos a Empleados
        </h1>
    </div>

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

    <x-ui.card class="overflow-hidden">
        <div class="bg-corteza-suave border-b border-arena px-6 py-4">
            <h2 class="text-lg font-semibold text-tinta">Gestión de liquidaciones</h2>
        </div>
        <div class="p-6">
            @if (!$mostrar_liquidacion)
                @canany(['crear-liquidacion-pagos', 'editar-liquidacion-pagos'])
                <form wire:submit.prevent="calcularLiquidacion" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-5">
                        <label for="id_empleado" class="block text-sm font-semibold text-tinta mb-2">Empleado <span class="text-tierra">*</span></label>
                        <select wire:model="id_empleado" id="id_empleado"
                            class="form-input @error('id_empleado') border-tierra bg-tierra-suave @enderror" @disabled($liquidar_todos)>
                            <option value="">Seleccione un empleado</option>
                            @foreach ($empleados as $emp)
                                <option value="{{ $emp->id_empleado }}" wire:key="option-{{ $emp->id_empleado }}">
                                    {{ $emp->apellido }}, {{ $emp->nombre }} - {{ $emp->rolLaboral->nombre ?? 'Sin rol' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_empleado') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                        <div class="mt-2 flex items-center gap-2 text-sm text-tinta-suave">
                            <input type="checkbox" id="liquidar_todos" wire:model="liquidar_todos" class="h-4 w-4 rounded border-arena text-pino focus:ring-pino">
                            <label for="liquidar_todos">Liquidar a todos los empleados</label>
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <label for="fecha_inicio" class="block text-sm font-semibold text-tinta mb-2">Fecha Inicio <span class="text-tierra">*</span></label>
                        <input type="date" wire:model="fecha_inicio" id="fecha_inicio"
                            class="form-input @error('fecha_inicio') border-tierra bg-tierra-suave @enderror">
                        @error('fecha_inicio') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-3">
                        <label for="fecha_fin" class="block text-sm font-semibold text-tinta mb-2">Fecha Fin <span class="text-tierra">*</span></label>
                        <input type="date" wire:model="fecha_fin" id="fecha_fin"
                            class="form-input @error('fecha_fin') border-tierra bg-tierra-suave @enderror">
                        @error('fecha_fin') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-1 flex items-end">
                        <x-ui.button type="submit" variant="primary" icon="calculator" class="w-full">
                            Calcular
                        </x-ui.button>
                    </div>
                    <div class="md:col-span-12">
                        <p class="mt-2 text-xs text-tinta-suave">Esta opción genera recibos para todos los empleados activos del período seleccionado.</p>
                    </div>
                </form>
                @endcanany
            @else
                <x-ui.alert variant="info" class="mb-6" dismissible="false">
                    <div class="flex items-center gap-2 font-semibold">
                        <flux:icon.user class="size-5" />
                        {{ $empleado_seleccionado->apellido }}, {{ $empleado_seleccionado->nombre }}
                    </div>
                    <p class="text-sm mt-1">
                        <strong>Rol:</strong> {{ $empleado_seleccionado->rolLaboral->nombre ?? 'N/A' }} |
                        <strong>Período:</strong> {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
                    </p>
                </x-ui.alert>

                @if (!$recibo_generado)
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <x-ui.card class="overflow-hidden">
                                <div class="bg-corteza-suave border-b border-arena px-4 py-3">
                                    <h6 class="font-semibold text-tinta flex items-center gap-2">
                                        <flux:icon.information-circle class="size-5" />
                                        Detalle del Cálculo
                                    </h6>
                                </div>
                                <div class="p-4">
                                    <div class="space-y-3 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold">Días caídos trabajados</span>
                                            <span>{{ $calculo['cantidad_dias_caidos'] }} días</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold">Jornal diario</span>
                                            <span>${{ number_format($calculo['valor_jornal'], 2) }}</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-sm bg-corteza-suave px-3 py-2">
                                            <span class="font-semibold">Subtotal jornales</span>
                                            <span class="font-semibold">${{ number_format($calculo['total_pagar_jornales'], 2) }}</span>
                                        </div>
                                        <hr class="border-arena">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold">Toneladas producidas</span>
                                            <span>{{ number_format($calculo['total_peso_toneladas'] ?? 0, 2) }} ton</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold">Tarifa por tonelada</span>
                                            <span>${{ number_format($calculo['tarifa_fija_por_tonelada'], 2) }}</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-sm bg-corteza-suave px-3 py-2">
                                            <span class="font-semibold">Subtotal producción</span>
                                            <span class="font-semibold">${{ number_format($calculo['total_pagar_produccion'], 2) }}</span>
                                        </div>
                                        <hr class="border-arena">
                                        <div class="flex items-center justify-between rounded-sm bg-musgo-suave px-3 py-2 text-musgo">
                                            <span class="font-semibold">TOTAL CALCULADO</span>
                                            <span class="text-lg font-bold">${{ number_format($calculo['total_pagar_final'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </x-ui.card>

                            @if(count($adelantos_pendientes) > 0)
                                <x-ui.card class="overflow-hidden border-resina/30">
                                    <div class="bg-resina-suave border-b border-resina/20 px-4 py-3">
                                        <h6 class="font-semibold text-resina flex items-center gap-2">
                                            <flux:icon.banknotes class="size-5" />
                                            Adelantos Pendientes
                                        </h6>
                                    </div>
                                    <div class="p-4">
                                        <x-ui.table-container>
                                            <table class="data-table">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th class="text-right">Monto</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($adelantos_pendientes as $adelanto)
                                                        <tr wire:key="row-{{ $adelanto->id }}">
                                                            <td>{{ \Carbon\Carbon::parse($adelanto->fecha_emision)->format('d/m/Y') }}</td>
                                                            <td class="text-right">${{ number_format($adelanto->monto, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="font-semibold bg-resina-suave">
                                                        <td>TOTAL ADELANTOS</td>
                                                        <td class="text-right">${{ number_format($total_adelantos, 2) }}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </x-ui.table-container>
                                        <x-ui.alert variant="info" class="mt-3" dismissible="false">
                                            Estos adelantos se descontarán automáticamente y se marcarán como "pagados" al generar el recibo.
                                        </x-ui.alert>
                                    </div>
                                </x-ui.card>
                            @endif
                        </div>

                        <x-ui.card class="overflow-hidden">
                            <div class="bg-corteza-suave border-b border-arena px-4 py-3">
                                <h6 class="font-semibold text-tinta flex items-center gap-2">
                                    <flux:icon.pencil-square class="size-5" />
                                    Datos del Recibo (Editable)
                                </h6>
                            </div>
                            <div class="p-4">
                                <form wire:submit.prevent="generarRecibo" class="space-y-4">
                                    <div>
                                        <label for="monto_bruto" class="block text-sm font-semibold text-tinta mb-2">Monto Bruto <span class="text-tierra">*</span></label>
                                        <input type="number" wire:model.live="monto_bruto" id="monto_bruto" step="0.1" min="0"
                                            class="form-input @error('monto_bruto') border-tierra bg-tierra-suave @enderror">
                                        @error('monto_bruto') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                                        <p class="mt-1 text-xs text-tinta-suave">Puedes modificar el monto calculado si es necesario</p>
                                    </div>

                                    <div>
                                        <label for="descuentos" class="block text-sm font-semibold text-tinta mb-2">
                                            Descuentos
                                            @if($total_adelantos > 0)
                                                <x-ui.badge variant="warning" class="ml-2">
                                                    Incluye adelantos
                                                </x-ui.badge>
                                            @endif
                                        </label>
                                        <input type="number" wire:model.live="descuentos" id="descuentos" step="0.1" min="0"
                                            class="form-input @error('descuentos') border-tierra bg-tierra-suave @enderror">
                                        @error('descuentos') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                                        <p class="mt-1 text-xs text-tinta-suave">
                                            @if($total_adelantos > 0)
                                                Adelantos del período: ${{ number_format($total_adelantos, 2) }} (se marcarán como pagados)
                                            @else
                                                Otros descuentos: retenciones, etc.
                                            @endif
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-tinta mb-2">Monto Neto a Pagar</label>
                                        <div class="rounded-sm border border-musgo bg-musgo-suave px-4 py-3 text-musgo">
                                            <div class="text-2xl font-bold">${{ number_format($monto_neto, 2) }}</div>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="observaciones" class="block text-sm font-semibold text-tinta mb-2">Observaciones</label>
                                        <textarea wire:model="observaciones" id="observaciones" rows="3"
                                            class="form-input @error('observaciones') border-tierra bg-tierra-suave @enderror" maxlength="150"></textarea>
                                        @error('observaciones') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        @canany(['crear-liquidacion-pagos', 'editar-liquidacion-pagos'])
                                        <x-ui.button type="submit" variant="primary" icon="check" class="w-full justify-center">
                                            Generar Recibo
                                        </x-ui.button>
                                        @endcanany
                                        <x-ui.button type="button" variant="secondary" icon="x-mark" wire:click="nuevaLiquidacion" class="w-full justify-center">
                                            Cancelar
                                        </x-ui.button>
                                    </div>
                                </form>
                            </div>
                        </x-ui.card>
                    </div>
                @else
                    <div class="rounded-sm border border-musgo bg-musgo-suave p-8 text-center text-musgo">
                        <flux:icon.check-circle class="size-16 mx-auto" />
                        <h3 class="mt-3 text-2xl font-semibold">Recibo generado correctamente</h3>
                        <p class="mt-2 text-sm">El pago ha sido registrado en el sistema.</p>
                        <x-ui.button type="button" variant="primary" icon="plus" wire:click="nuevaLiquidacion" class="mt-4 justify-center">
                            Nueva Liquidación
                        </x-ui.button>
                    </div>
                @endif
            @endif
        </div>
    </x-ui.card>
</div>
