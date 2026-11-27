@props([
    'lote' => 'Lote sin especificar',
    'alerta' => 'NORMAL',
    'pronostico' => [],
    'analisisImpacto' => [
        'diasPerdidos' => 0,
        'deficitTn' => 0,
        'accionPorcentaje' => 0,
        'diasOperativos' => 0,
        'primerOperativo' => null,
        'diasCriticos' => [],
        'nuevaMetaDiaria' => null,
        'residual' => 0,
        'recuperable' => 0,
        'mantenimiento' => [],
        'metaFuente' => 'sin_datos',
    ],
    'recomendacionDetallada' => null,
    'generadoEn' => null,
])

@php
    $alertConfig = [
        'COMPENSAR' => ['bg' => 'bg-resina-suave/50', 'text' => 'text-resina', 'dot' => 'bg-resina', 'label' => 'COMPENSAR'],
        'PRIORIZAR' => ['bg' => 'bg-tierra-suave/50', 'text' => 'text-tierra', 'dot' => 'bg-tierra', 'label' => 'PRIORIZAR'],
        'MANTENIMIENTO' => ['bg' => 'bg-pino-suave/50', 'text' => 'text-pino', 'dot' => 'bg-pino', 'label' => 'MANTENIMIENTO'],
        'REPROGRAMAR' => ['bg' => 'bg-corteza-suave/70', 'text' => 'text-tinta', 'dot' => 'bg-tierra', 'label' => 'REPROGRAMAR'],
        'SIN HISTORIAL' => ['bg' => 'bg-hueso', 'text' => 'text-tinta-suave', 'dot' => 'bg-arena-oscura', 'label' => 'SIN HISTORIAL'],
        'NORMAL' => ['bg' => 'bg-musgo-suave/50', 'text' => 'text-musgo', 'dot' => 'bg-musgo', 'label' => 'OPERATIVO'],
    ];

    $currentAlert = $alertConfig[$alerta] ?? $alertConfig['NORMAL'];

    $iconMap = [
        'sun' => 'sun',
        'storm' => 'cloud-arrow-down',
        'fog' => 'cloud',
        'cloud' => 'cloud',
        'calendar' => 'calendar',
    ];

    $diasOperativos = 0;
    foreach ($pronostico as $dia) {
        if (! ($dia['inactivo'] ?? false) && ! ($dia['no_laboral'] ?? false)) {
            $diasOperativos++;
        }
    }
    $ventana = $diasOperativos === 1 ? '1 día' : $diasOperativos.' días';

    $diaCritico = null;
    foreach ($pronostico as $dia) {
        if ($dia['inactivo'] ?? false) {
            $diaCritico = $dia['label'] ?? null;
            break;
        }
    }
    $diaCritico ??= 'Sin días no operativos';

    $porcentaje = (int) ($analisisImpacto['accionPorcentaje'] ?? 0);
    $residual = (float) ($analisisImpacto['residual'] ?? 0);
    $mantenimiento = $analisisImpacto['mantenimiento'] ?? [];
    $cantidadMantenimiento = count($mantenimiento);
    $metaFuente = $analisisImpacto['metaFuente'] ?? 'sin_datos';

    $primerOperativoCorto = $analisisImpacto['primerOperativo']
        ? preg_replace('/ \((\d{2}\/\d{2})\/\d{4}\)/', ' $1', $analisisImpacto['primerOperativo'])
        : null;

    $accionConfig = match ($alerta) {
        'COMPENSAR' => [
            'text' => '+'.$porcentaje.'%',
            'sub' => $primerOperativoCorto ? 'Desde '.$primerOperativoCorto : 'En días operativos',
            'color' => 'text-resina',
        ],
        'PRIORIZAR' => [
            'text' => '+25%',
            'sub' => 'Quedan '.number_format($residual, 0, ',', '.').' tn sin cubrir',
            'color' => 'text-tierra',
        ],
        'MANTENIMIENTO' => [
            'text' => 'Mantenimiento',
            'sub' => $cantidadMantenimiento === 1 ? '1 máquina disponible' : $cantidadMantenimiento.' máquinas disponibles',
            'color' => 'text-pino',
        ],
        'REPROGRAMAR' => [
            'text' => 'Reprogramar',
            'sub' => 'Sin días operativos',
            'color' => 'text-tinta',
        ],
        'SIN HISTORIAL' => [
            'text' => 'Sin historial',
            'sub' => 'Sin producción previa',
            'color' => 'text-tinta-suave',
        ],
        default => [
            'text' => 'Ritmo normal',
            'sub' => 'Sin cambios',
            'color' => 'text-musgo',
        ],
    };
@endphp

<div class="w-full space-y-3">
    {{-- Encabezado de la tarjeta --}}
    <div class="flex flex-wrap items-center justify-between gap-2 px-1">
        <div class="flex items-center gap-2">
            <flux:icon.cloud class="size-4 text-pino" />
            <h2 class="text-sm font-semibold text-tinta">Clima operativo · {{ $lote }}</h2>
        </div>
        @if ($generadoEn)
            <span class="text-xs text-tinta-suave">Actualizado {{ $generadoEn }}</span>
        @endif
    </div>

    {{-- Indicadores --}}
    <div class="grid grid-cols-1 gap-2 md:grid-cols-4">
        {{-- Estado --}}
        <div class="rounded-lg border border-arena {{ $currentAlert['bg'] }} p-4">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full {{ $currentAlert['dot'] }}"></span>
                <span class="text-xs font-bold uppercase tracking-wide {{ $currentAlert['text'] }}">{{ $currentAlert['label'] }}</span>
            </div>
            <p class="mt-2 text-sm font-medium text-tinta">{{ $diaCritico }}</p>
            <p class="text-xs text-tinta-suave">{{ $ventana }} operativos en 7 días</p>
        </div>

        {{-- Días no operativos --}}
        <div class="rounded-lg border border-arena bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-tinta-suave">Días no operativos</p>
            <p class="mt-1 text-2xl font-bold text-tinta">{{ $analisisImpacto['diasPerdidos'] ?? 0 }}</p>
            <p class="text-xs text-tinta-suave">Lluvia o barro · fines de semana excluidos</p>
        </div>

        {{-- Producción en riesgo --}}
        <div class="rounded-lg border border-arena bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-tinta-suave">Producción en riesgo</p>
            <p class="mt-1 text-2xl font-bold text-tinta">
                {{ number_format((float) ($analisisImpacto['deficitTn'] ?? 0), 0, ',', '.') }}
                <span class="text-sm font-normal text-tinta-suave">tn</span>
            </p>
            <p class="text-xs text-tinta-suave">
                {{ $metaFuente === 'sin_datos' ? 'Sin historial de producción' : 'Según producción del lote' }}
            </p>
        </div>

        {{-- Acción --}}
        <div class="rounded-lg border border-arena bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-tinta-suave">Acción</p>
            <p class="mt-1 text-2xl font-bold {{ $accionConfig['color'] }}">{{ $accionConfig['text'] }}</p>
            <p class="text-xs text-tinta-suave">{{ $accionConfig['sub'] }}</p>
        </div>
    </div>

    {{-- Pronóstico de 7 días --}}
    <div class="rounded-lg border border-arena bg-white">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-arena px-4 py-3">
            <h3 class="text-xs font-bold uppercase tracking-wide text-tinta">Pronóstico operativo (7 días)</h3>
            <div class="flex flex-wrap items-center gap-3 text-xs text-tinta-suave">
                <span class="inline-flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-musgo"></span> Operativo</span>
                <span class="inline-flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-resina"></span> Operativo cond.</span>
                <span class="inline-flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-tierra"></span> No operativo</span>
                <span class="inline-flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-arena-oscura"></span> No laboral</span>
            </div>
        </div>

        @if (count($pronostico) > 0)
            <div class="grid grid-cols-7 divide-x divide-arena/50">
                @foreach ($pronostico as $dia)
                    @php
                        $esNoLaboral = $dia['no_laboral'] ?? false;
                        $esInactivo = ($dia['inactivo'] ?? false) && ! $esNoLaboral;
                        $bgCell = $esNoLaboral ? 'bg-corteza-suave/40' : ($esInactivo ? 'bg-tierra-suave/50' : 'bg-white');
                        $textColor = $esNoLaboral ? 'text-tinta-suave' : ($esInactivo ? 'text-tierra' : 'text-musgo');
                        $dotColor = $esNoLaboral ? 'bg-arena-oscura' : ($esInactivo ? 'bg-tierra' : 'bg-musgo');
                    @endphp
                    <div class="px-2 py-3 {{ $bgCell }} text-center" @if ($dia['suelo'] ?? null) title="{{ $dia['suelo'] }}" @endif>
                        <p class="text-xs font-medium text-tinta-suave">{{ $dia['label'] }}</p>
                        <div class="my-2 flex justify-center">
                            <x-dynamic-component component="flux::icon.{{ $iconMap[$dia['icono']] ?? 'sun' }}" class="size-6 {{ $textColor }}" />
                        </div>
                        <div class="flex items-center justify-center gap-1">
                            <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
                            <span class="text-xs font-medium {{ $textColor }}">{{ $dia['estado'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-4 py-6 text-center text-sm text-tinta-suave">Sin datos de pronóstico.</div>
        @endif
    </div>

    {{-- Qué hacer --}}
    @if ($recomendacionDetallada)
        <div class="rounded-lg border border-arena bg-hueso p-4">
            <div class="flex items-start gap-3">
                <flux:icon.light-bulb class="mt-0.5 size-5 shrink-0 text-pino" />
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-tinta">Qué hacer</p>
                    <p class="mt-1 text-sm leading-relaxed text-tinta-suave">{{ $recomendacionDetallada }}</p>

                    @if ($cantidadMantenimiento > 0)
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($mantenimiento as $maquina)
                                <span class="inline-flex items-center gap-1 rounded-full border border-arena bg-white px-2.5 py-1 text-xs text-tinta">
                                    <flux:icon.wrench-screwdriver class="size-3 text-pino" />
                                    {{ $maquina['nombre'] }} · {{ $maquina['motivo'] }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
