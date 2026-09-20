@props([
    'lote' => 'Lote sin especificar',
    'alerta' => 'NORMAL',
    'pronostico' => [],
    'analisisImpacto' => ['diasPerdidos' => 0, 'deficitTn' => 0, 'accionPorcentaje' => 0],
    'recomendacionDetallada' => null,
])

@php
    // Configuración High Density - Tokens forestales
    $alertConfig = [
        'ACELERAR' => [
            'bg' => 'bg-resina-suave/50',
            'text' => 'text-resina',
            'dot' => 'bg-resina',
            'label' => 'ALERTA',
        ],
        'SUSPENDER' => [
            'bg' => 'bg-tierra-suave/50',
            'text' => 'text-tierra',
            'dot' => 'bg-tierra',
            'label' => 'PARADA',
        ],
        'NORMAL' => [
            'bg' => 'bg-musgo-suave/50',
            'text' => 'text-musgo',
            'dot' => 'bg-musgo',
            'label' => 'OPERATIVO',
        ],
    ];

    $currentAlert = $alertConfig[$alerta] ?? $alertConfig['NORMAL'];

    $iconMap = [
        'sun' => 'sun',
        'storm' => 'cloud-arrow-down',
        'fog' => 'cloud',
        'cloud' => 'cloud',
    ];

    // Calcular la ventana de trabajo
    $diasOperativos = 0;
    $diasPerdidos = $analisisImpacto['diasPerdidos'] ?? 0;
    foreach ($pronostico as $dia) {
        if (!($dia['inactivo'] ?? false)) {
            $diasOperativos++;
        }
    }
    $ventana = $diasOperativos . ' dias' ?? '2 dias';

    // Encontrar el día crítico
    $diaCritico = 'Desconocido';
    foreach ($pronostico as $dia) {
        if ($dia['inactivo'] ?? false) {
            $diaCritico = ucfirst($dia['label'] ?? 'proximamente');
            break;
        }
    }
@endphp

<div class="w-full space-y-3">
    {{-- Bento Grid: Status + KPIs en fila compacta --}}
    <div class="grid grid-cols-1 gap-2 md:grid-cols-4">
        {{-- Widget 1: Estado General --}}
        <div class="rounded-lg border border-arena {{ $currentAlert['bg'] }} p-3">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full {{ $currentAlert['dot'] }}"></span>
                <span class="text-xs font-bold uppercase tracking-wide {{ $currentAlert['text'] }}">{{ $currentAlert['label'] }}</span>
            </div>
            <div class="mt-2 text-xs text-tinta-suave">
                <span class="font-medium {{ $currentAlert['text'] }}">{{ $diaCritico }}</span> · {{ $ventana }}
            </div>
        </div>

        {{-- Widget 2: Dias Perdidos --}}
        <div class="rounded-lg border border-arena bg-blanco p-3">
            <div class="text-xs font-medium uppercase tracking-wide text-tinta-suave">DIAS PERDIDOS</div>
            <div class="mt-1 text-2xl font-bold text-tinta">{{ $analisisImpacto['diasPerdidos'] ?? 0 }}</div>
            <div class="text-xs text-tinta-suave">Lluvia + barro</div>
        </div>

        {{-- Widget 3: Deficit --}}
        <div class="rounded-lg border border-arena bg-blanco p-3">
            <div class="text-xs font-medium uppercase tracking-wide text-tinta-suave">DEFICIT TN</div>
            <div class="mt-1 text-2xl font-bold text-tinta">{{ abs($analisisImpacto['deficitTn'] ?? 0) }}</div>
            <div class="text-xs text-tinta-suave">Volumen en riesgo</div>
        </div>

        {{-- Widget 4: Accion --}}
        <div class="rounded-lg border border-arena bg-blanco p-3">
            @php
                $accionConfig = match($alerta) {
                    'SUSPENDER' => ['text' => 'Suspender', 'color' => 'text-tierra'],
                    'ACELERAR' => ['text' => '+' . ($analisisImpacto['accionPorcentaje'] ?? 0) . '%', 'color' => 'text-resina'],
                    default => ['text' => 'Normal', 'color' => 'text-musgo']
                };
            @endphp
            <div class="text-xs font-medium uppercase tracking-wide text-tinta-suave">ACCION</div>
            <div class="mt-1 text-2xl font-bold {{ $accionConfig['color'] }}">{{ $accionConfig['text'] }}</div>
            <div class="text-xs text-tinta-suave">Sugerida</div>
        </div>
    </div>


    {{-- Timeline Strip: Pronostico 7 dias (estilo Gantt compacto) --}}
    <div class="rounded-lg border border-arena bg-blanco">
        <div class="border-b border-arena px-3 py-2">
            <h3 class="text-xs font-bold uppercase tracking-wide text-tinta">Pronostico Operativo (7 dias)</h3>
        </div>
        @if(count($pronostico) > 0)
            <div class="grid grid-cols-7 divide-x divide-arena/50">
                @foreach($pronostico as $dia)
                    @php
                        $isInactivo = $dia['inactivo'] ?? false;
                        $bgCell = $isInactivo ? 'bg-tierra-suave/50' : 'bg-blanco';
                        $textColor = $isInactivo ? 'text-tierra' : 'text-musgo';
                        $dotColor = $isInactivo ? 'bg-tierra' : 'bg-musgo';
                    @endphp
                    <div class="px-2 py-3 {{ $bgCell }} text-center">
                        <div class="text-xs font-medium text-tinta-suave">{{ $dia['label'] }}</div>
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
            <div class="px-3 py-4 text-center text-xs text-tinta-suave">Sin datos</div>
        @endif
    </div>


    {{-- Recomendacion (compacta) --}}
    @if($recomendacionDetallada)
    <div class="rounded-lg border border-arena bg-corteza-suave px-3 py-2">
        <div class="flex items-start gap-2">
            <flux:icon.information-circle class="h-4 w-4 flex-shrink-0 text-tinta-suave" />
            <div class="flex-1">
                <div class="text-xs font-medium text-tinta">Recomendacion del Sistema</div>
                <div class="mt-1 whitespace-pre-line text-xs text-tinta-suave">{!! nl2br(e($recomendacionDetallada)) !!}</div>
            </div>
        </div>
    </div>
    @endif
</div>
