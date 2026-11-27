<div class="w-full" wire:poll.60s="refrescar">
    <x-ui.card class="overflow-hidden">
        <div class="flex items-center justify-between gap-3 border-b border-arena px-5 py-4">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-tinta mb-0">
                <flux:icon.bolt class="size-5 text-pino" />
                Automatizaciones programadas
            </h2>
            <a href="{{ route('configuracion-mantenimiento.index') }}" class="text-xs font-medium text-pino underline">
                Editar frecuencias
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-corteza-suave text-left text-xs uppercase tracking-wide text-tinta-suave">
                        <th class="px-4 py-3 font-semibold">Tarea</th>
                        <th class="px-4 py-3 font-semibold">Frecuencia</th>
                        <th class="px-4 py-3 font-semibold">Próxima ejecución</th>
                        <th class="px-4 py-3 font-semibold">Comando</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-arena">
                    @foreach ($procesos as $proceso)
                        <tr class="hover:bg-corteza-suave/60">
                            <td class="px-4 py-3">
                                <div class="font-medium text-tinta">{{ $proceso['nombre'] }}</div>
                                <div class="text-xs text-tinta-suave">{{ $proceso['descripcion'] }}</div>
                                @if (! empty($proceso['detalle']))
                                    <div class="text-xs text-tinta-suave">{{ $proceso['detalle'] }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <code class="rounded-sm bg-corteza-suave px-1.5 py-0.5 font-mono text-xs text-tinta">{{ $proceso['cron'] }}</code>
                                @if ($proceso['editable'])
                                    <span class="ml-1 inline-flex items-center rounded-full bg-pino-suave px-2 py-0.5 text-xs font-semibold text-pino">editable</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-tinta-suave">{{ $proceso['proximo'] ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-tinta-suave">{{ $proceso['comando'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
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
                                <th class="px-4 py-3 font-semibold">Decisión</th>
                                <th class="px-4 py-3 font-semibold text-right">Días no op.</th>
                                <th class="px-4 py-3 font-semibold text-right">Déficit</th>
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
                                    <td class="px-4 py-3">
                                        @if ($fila['decision_label'])
                                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold
                                                {{ $fila['decision_label'] === 'Priorizar' ? 'bg-tierra-suave text-tierra' : ($fila['decision_label'] === 'Reprogramar' ? 'bg-corteza-suave text-tinta' : ($fila['decision_label'] === 'Compensar' ? 'bg-resina-suave text-resina' : 'bg-musgo-suave text-musgo')) }}">
                                                {{ $fila['decision_label'] }}
                                            </span>
                                        @else
                                            <span class="text-xs text-tinta-suave">Sin decisión</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-tinta-suave">{{ $fila['decision_dias'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right text-tinta-suave">
                                        @if ($fila['decision_deficit'] !== null)
                                            {{ number_format((float) $fila['decision_deficit'], 0, ',', '.') }} tn
                                        @else
                                            —
                                        @endif
                                    </td>
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
                    Revisá <code class="font-mono">php artisan queue:failed</code> para reintentar o descartar trabajos fallidos.
                </div>
            @endif
        </x-ui.card>
    </div>
</div>
