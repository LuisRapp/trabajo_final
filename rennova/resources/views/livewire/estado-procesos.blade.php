<div class="w-full">
    <div class="grid gap-6 lg:grid-cols-3">
        @foreach ($procesos as $proceso)
            <x-ui.card class="flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="text-base font-semibold text-tinta leading-snug">{{ $proceso['nombre'] }}</h3>
                    <span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-pino-suave px-2.5 py-0.5 text-xs font-semibold text-pino">
                        <flux:icon.bolt class="size-3" />
                        {{ $proceso['disparador'] }}
                    </span>
                </div>
                <p class="text-sm text-tinta-suave leading-relaxed">{{ $proceso['descripcion'] }}</p>
                <dl class="mt-auto space-y-1.5 text-xs">
                    <div class="flex gap-2">
                        <dt class="font-semibold text-tinta">Frecuencia:</dt>
                        <dd class="text-tinta-suave">{{ $proceso['frecuencia'] }}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="font-semibold text-tinta">Ejecución:</dt>
                        <dd class="text-tinta-suave font-mono">{{ $proceso['comando'] }}</dd>
                    </div>
                </dl>
            </x-ui.card>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <x-ui.card>
            <div class="border-b border-arena px-5 py-4">
                <h2 class="flex items-center gap-2 text-lg font-semibold text-tinta mb-0">
                    <flux:icon.cloud class="size-5 text-pino" />
                    Sincronización climática por lote
                </h2>
            </div>

            @if (empty($climaPorLote))
                <div class="px-5 py-6 text-sm text-tinta-suave">
                    No hay lotes activos para monitorear.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-corteza-suave text-left text-xs uppercase tracking-wide text-tinta-suave">
                                <th class="px-4 py-3 font-semibold">Lote</th>
                                <th class="px-4 py-3 font-semibold">Estado hoy</th>
                                <th class="px-4 py-3 font-semibold">Fuente</th>
                                <th class="px-4 py-3 font-semibold">Última sync</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-arena">
                            @foreach ($climaPorLote as $fila)
                                <tr class="hover:bg-corteza-suave/60">
                                    <td class="px-4 py-3 text-tinta">{{ $fila['lote'] }}</td>
                                    <td class="px-4 py-3">
                                        @if (! $fila['con_coordenadas'])
                                            <span class="inline-flex items-center gap-1 rounded-full bg-resina-suave px-2.5 py-0.5 text-xs font-semibold text-resina">
                                                <flux:icon.map-pin class="size-3" />
                                                Sin coordenadas
                                            </span>
                                        @elseif ($fila['api_error'])
                                            <span class="inline-flex items-center gap-1 rounded-full bg-resina-suave px-2.5 py-0.5 text-xs font-semibold text-resina"
                                                title="{{ $fila['api_error'] }}">
                                                <flux:icon.exclamation-triangle class="size-3" />
                                                {{ $fila['estado_operativo'] ?? 'FALLBACK' }}
                                            </span>
                                        @elseif ($fila['estado_operativo'] === 'INACTIVO')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-tierra-suave px-2.5 py-0.5 text-xs font-semibold text-tierra">
                                                <flux:icon.exclamation-circle class="size-3" />
                                                INACTIVO
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-musgo-suave px-2.5 py-0.5 text-xs font-semibold text-musgo">
                                                <flux:icon.check-circle class="size-3" />
                                                {{ $fila['estado_operativo'] ?? 'OPERATIVO' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-tinta-suave font-mono text-xs">{{ $fila['fuente'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-tinta-suave">{{ $fila['actualizado'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card>
            <div class="border-b border-arena px-5 py-4 flex items-center justify-between">
                <h2 class="flex items-center gap-2 text-lg font-semibold text-tinta mb-0">
                    <flux:icon.queue-list class="size-5 text-pino" />
                    Cola de trabajos
                </h2>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold
                    {{ $cola['fallidos'] ? 'bg-tierra-suave text-tierra' : 'bg-musgo-suave text-musgo' }}">
                    {{ $cola['pendientes'] }} pendientes
                </span>
            </div>

            @if (empty($cola['fallidos']))
                <div class="px-5 py-6 text-sm text-tinta-suave flex items-center gap-2">
                    <flux:icon.check-circle class="size-5 text-musgo" />
                    Sin trabajos fallidos. Los procesos encolados se están ejecutando con normalidad.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-corteza-suave text-left text-xs uppercase tracking-wide text-tinta-suave">
                                <th class="px-4 py-3 font-semibold">Trabajo</th>
                                <th class="px-4 py-3 font-semibold">Error</th>
                                <th class="px-4 py-3 font-semibold">Fallido</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-arena">
                            @foreach ($cola['fallidos'] as $job)
                                <tr class="hover:bg-corteza-suave/60">
                                    <td class="px-4 py-3 text-tinta font-mono text-xs">
                                        {{ $job['nombre'] ?? 'Job #'.$job['id'] }}
                                    </td>
                                    <td class="px-4 py-3 text-tierra text-xs" title="{{ $job['error'] }}">
                                        {{ \Illuminate\Support\Str::limit($job['error'], 60) }}
                                    </td>
                                    <td class="px-4 py-3 text-tinta-suave text-xs">{{ $job['fallido_en'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-arena px-5 py-3 text-xs text-tinta-suave">
                    Revise <code class="font-mono">php artisan queue:failed</code> para reintentar o descartar trabajos fallidos.
                </div>
            @endif
        </x-ui.card>
    </div>
</div>
