<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            🧮 Liquidación de Pagos a Empleados
        </h1>
    </div>

    @if (session()->has('message'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 shadow-sm" role="alert">
            ✅
            <span class="flex-1 font-medium">{{ session('message') }}</span>
            <button type="button" class="text-green-600 hover:text-green-800" @click="open = false">
                ✕
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm" role="alert">
            ⚠️
            <span class="flex-1 font-medium">{{ session('error') }}</span>
            <button type="button" class="text-red-600 hover:text-red-800" @click="open = false">
                ✕
            </button>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-800">Gestión de liquidaciones</h2>
        </div>
        <div class="p-6">
            @if (!$mostrar_liquidacion)
                @canany(['crear-liquidacion-pagos', 'editar-liquidacion-pagos'])
                <form wire:submit.prevent="calcularLiquidacion" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-5">
                        <label for="id_empleado" class="block text-sm font-semibold text-slate-700 mb-2">Empleado *</label>
                        <select wire:model="id_empleado" id="id_empleado"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('id_empleado') ring-2 ring-red-500 @enderror" @disabled($liquidar_todos)>
                            <option value="">Seleccione un empleado</option>
                            @foreach ($empleados as $emp)
                                <option value="{{ $emp->id_empleado }}" wire:key="option-{{ $emp->id_empleado }}">
                                    {{ $emp->apellido }}, {{ $emp->nombre }} - {{ $emp->rolLaboral->nombre ?? 'Sin rol' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_empleado') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        <div class="mt-2 flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" id="liquidar_todos" wire:model="liquidar_todos" class="h-4 w-4 rounded border-slate-300 text-green-700 focus:ring-green-600">
                            <label for="liquidar_todos">Liquidar a todos los empleados</label>
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <label for="fecha_inicio" class="block text-sm font-semibold text-slate-700 mb-2">Fecha Inicio *</label>
                        <input type="date" wire:model="fecha_inicio" id="fecha_inicio"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('fecha_inicio') ring-2 ring-red-500 @enderror">
                        @error('fecha_inicio') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-3">
                        <label for="fecha_fin" class="block text-sm font-semibold text-slate-700 mb-2">Fecha Fin *</label>
                        <input type="date" wire:model="fecha_fin" id="fecha_fin"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('fecha_fin') ring-2 ring-red-500 @enderror">
                        @error('fecha_fin') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-1 flex items-end">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-green-700 px-4 py-3 text-white font-semibold shadow-sm hover:bg-green-800">
                            🧮 Calcular
                        </button>
                    </div>
                    <div class="md:col-span-12">
                        <p class="mt-2 text-xs text-slate-500">Esta opción genera recibos para todos los empleados activos del período seleccionado.</p>
                    </div>
                </form>
                @endcanany
            @else
                <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-800">
                    <h5 class="mb-1 flex items-center gap-2 font-semibold">
                        👤
                        {{ $empleado_seleccionado->apellido }}, {{ $empleado_seleccionado->nombre }}
                    </h5>
                    <p class="text-sm">
                        <strong>Rol:</strong> {{ $empleado_seleccionado->rolLaboral->nombre ?? 'N/A' }} |
                        <strong>Período:</strong> {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
                    </p>
                </div>

                @if (!$recibo_generado)
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                                <div class="border-b border-slate-200 bg-slate-100 px-4 py-3">
                                    <h6 class="mb-0 flex items-center gap-2 font-semibold text-slate-800">
                                        ℹ️ Detalle del Cálculo
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
                                        <div class="flex items-center justify-between rounded-md bg-slate-50 px-3 py-2">
                                            <span class="font-semibold">Subtotal jornales</span>
                                            <span class="font-semibold">${{ number_format($calculo['total_pagar_jornales'], 2) }}</span>
                                        </div>
                                        <hr>
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold">Toneladas producidas</span>
                                            <span>{{ number_format($calculo['total_peso_toneladas'] ?? 0, 2) }} ton</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold">Tarifa por tonelada</span>
                                            <span>${{ number_format($calculo['tarifa_fija_por_tonelada'], 2) }}</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-md bg-slate-50 px-3 py-2">
                                            <span class="font-semibold">Subtotal producción</span>
                                            <span class="font-semibold">${{ number_format($calculo['total_pagar_produccion'], 2) }}</span>
                                        </div>
                                        <hr>
                                        <div class="flex items-center justify-between rounded-md bg-green-50 px-3 py-2 text-green-800">
                                            <span class="font-semibold">TOTAL CALCULADO</span>
                                            <span class="text-lg font-bold">${{ number_format($calculo['total_pagar_final'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(count($adelantos_pendientes) > 0)
                                <div class="rounded-lg border border-amber-200 bg-amber-50 shadow-sm">
                                    <div class="border-b border-amber-200 px-4 py-3 text-amber-900">
                                        <h6 class="mb-0 flex items-center gap-2 font-semibold">
                                            💰 Adelantos Pendientes
                                        </h6>
                                    </div>
                                    <div class="p-4">
                                        <div class="overflow-hidden rounded-lg border border-amber-200">
                                            <table class="min-w-full text-sm">
                                                <thead class="bg-amber-100 text-amber-900">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left">Fecha</th>
                                                        <th class="px-3 py-2 text-right">Monto</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-amber-200 bg-white">
                                                    @foreach($adelantos_pendientes as $adelanto)
                                                        <tr wire:key="row-{{ $adelanto->id }}">
                                                            <td class="px-3 py-2">{{ \Carbon\Carbon::parse($adelanto->fecha_emision)->format('d/m/Y') }}</td>
                                                            <td class="px-3 py-2 text-right">${{ number_format($adelanto->monto, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="bg-amber-100">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left">TOTAL ADELANTOS</th>
                                                        <th class="px-3 py-2 text-right">${{ number_format($total_adelantos, 2) }}</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <div class="mt-3 rounded-md border border-blue-200 bg-blue-50 p-3 text-xs text-blue-800">
                                            ℹ️ Estos adelantos se descontarán automáticamente y se marcarán como "pagados" al generar el recibo.
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-200 bg-slate-100 px-4 py-3">
                                <h6 class="mb-0 flex items-center gap-2 font-semibold text-slate-800">
                                    ✏️ Datos del Recibo (Editable)
                                </h6>
                            </div>
                            <div class="p-4">
                                <form wire:submit.prevent="generarRecibo" class="space-y-4">
                                    <div>
                                        <label for="monto_bruto" class="block text-sm font-semibold text-slate-700 mb-2">Monto Bruto *</label>
                                        <input type="number" wire:model.live="monto_bruto" id="monto_bruto" step="0.1" min="0"
                                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('monto_bruto') ring-2 ring-red-500 @enderror">
                                        @error('monto_bruto') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                        <p class="mt-1 text-xs text-slate-500">Puedes modificar el monto calculado si es necesario</p>
                                    </div>

                                    <div>
                                        <label for="descuentos" class="block text-sm font-semibold text-slate-700 mb-2">
                                            Descuentos
                                            @if($total_adelantos > 0)
                                                <span class="ml-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">
                                                    💰 Incluye adelantos
                                                </span>
                                            @endif
                                        </label>
                                        <input type="number" wire:model.live="descuentos" id="descuentos" step="0.1" min="0"
                                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('descuentos') ring-2 ring-red-500 @enderror">
                                        @error('descuentos') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                        <p class="mt-1 text-xs text-slate-500">
                                            @if($total_adelantos > 0)
                                                Adelantos del período: ${{ number_format($total_adelantos, 2) }} (se marcarán como pagados)
                                            @else
                                                Otros descuentos: retenciones, etc.
                                            @endif
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Monto Neto a Pagar</label>
                                        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                                            <div class="text-2xl font-bold">${{ number_format($monto_neto, 2) }}</div>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="observaciones" class="block text-sm font-semibold text-slate-700 mb-2">Observaciones</label>
                                        <textarea wire:model="observaciones" id="observaciones" rows="3"
                                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors @error('observaciones') ring-2 ring-red-500 @enderror" maxlength="150"></textarea>
                                        @error('observaciones') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        @canany(['crear-liquidacion-pagos', 'editar-liquidacion-pagos'])
                                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-700 px-4 py-3 text-white font-semibold shadow-sm hover:bg-green-800">
                                            ✓ Generar Recibo
                                        </button>
                                        @endcanany
                                        <button type="button" wire:click="nuevaLiquidacion" class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-200 px-4 py-3 text-slate-700 font-semibold hover:bg-slate-300">
                                            ✕ Cancelar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-lg border border-green-200 bg-green-50 p-8 text-center text-green-800">
                        <span class="text-5xl">✅</span>
                        <h3 class="mt-3 text-2xl font-semibold">¡Recibo generado correctamente!</h3>
                        <p class="mt-2 text-sm">El pago ha sido registrado en el sistema.</p>
                        <button type="button" wire:click="nuevaLiquidacion"
                            class="mt-4 inline-flex items-center justify-center gap-2 rounded-lg bg-green-700 px-4 py-3 text-white font-semibold shadow-sm hover:bg-green-800">
                            ➕ Nueva Liquidación
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
