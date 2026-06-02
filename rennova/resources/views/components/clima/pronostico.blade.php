@props([
    'lote' => 'Lote sin especificar',
    'alerta' => 'NORMAL',
    'pronostico' => [],
    'analisisImpacto' => ['diasPerdidos' => 0, 'deficitTn' => 0, 'accionPorcentaje' => 0],
    'recomendacionDetallada' => null,
])

@php
    // Configuración High Density - Colores semánticos al 5%
    $alertConfig = [
        'ACELERAR' => [
            'bg' => 'bg-amber-50/50',
            'text' => 'text-amber-700',
            'dot' => 'bg-amber-500',
            'label' => 'ALERTA',
        ],
        'SUSPENDER' => [
            'bg' => 'bg-red-50/50',
            'text' => 'text-red-700',
            'dot' => 'bg-red-500',
            'label' => 'PARADA',
        ],
        'NORMAL' => [
            'bg' => 'bg-emerald-50/50',
            'text' => 'text-emerald-700',
            'dot' => 'bg-emerald-500',
            'label' => 'OPERATIVO',
        ],
    ];
    
    $currentAlert = $alertConfig[$alerta] ?? $alertConfig['NORMAL'];
    
    $iconMap = [
        'sun' => '☀',
        'storm' => '⛈',
        'fog' => '🌫',
        'cloud' => '☁',
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
        <div class="rounded-lg border border-slate-200 {{ $currentAlert['bg'] }} p-3">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full {{ $currentAlert['dot'] }}"></span>
                <span class="text-xs font-bold uppercase tracking-wide {{ $currentAlert['text'] }}">{{ $currentAlert['label'] }}</span>
            </div>
            <div class="mt-2 text-xs text-slate-600">
                <span class="font-medium {{ $currentAlert['text'] }}">{{ $diaCritico }}</span> · {{ $ventana }}
            </div>
        </div>

        {{-- Widget 2: Dias Perdidos --}}
        <div class="rounded-lg border border-slate-200 bg-white p-3">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">DIAS PERDIDOS</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ $analisisImpacto['diasPerdidos'] ?? 0 }}</div>
            <div class="text-xs text-slate-500">Lluvia + barro</div>
        </div>

        {{-- Widget 3: Deficit --}}
        <div class="rounded-lg border border-slate-200 bg-white p-3">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">DEFICIT TN</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ abs($analisisImpacto['deficitTn'] ?? 0) }}</div>
            <div class="text-xs text-slate-500">Volumen en riesgo</div>
        </div>

        {{-- Widget 4: Accion --}}
        <div class="rounded-lg border border-slate-200 bg-white p-3">
            @php
                $accionConfig = match($alerta) {
                    'SUSPENDER' => ['text' => 'Suspender', 'color' => 'text-red-700'],
                    'ACELERAR' => ['text' => '+' . ($analisisImpacto['accionPorcentaje'] ?? 0) . '%', 'color' => 'text-amber-700'],
                    default => ['text' => 'Normal', 'color' => 'text-emerald-700']
                };
            @endphp
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">ACCION</div>
            <div class="mt-1 text-2xl font-bold {{ $accionConfig['color'] }}">{{ $accionConfig['text'] }}</div>
            <div class="text-xs text-slate-500">Sugerida</div>
        </div>
    </div>


    {{-- Timeline Strip: Pronostico 7 dias (estilo Gantt compacto) --}}
    <div class="rounded-lg border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-3 py-2">
            <h3 class="text-xs font-bold uppercase tracking-wide text-slate-700">Pronostico Operativo (7 dias)</h3>
        </div>
        @if(count($pronostico) > 0)
            <div class="grid grid-cols-7 divide-x divide-slate-100">
                @foreach($pronostico as $dia)
                    @php
                        $isInactivo = $dia['inactivo'] ?? false;
                        $bgCell = $isInactivo ? 'bg-red-50/50' : 'bg-white';
                        $textColor = $isInactivo ? 'text-red-700' : 'text-emerald-700';
                        $dotColor = $isInactivo ? 'bg-red-500' : 'bg-emerald-500';
                    @endphp
                    <div class="px-2 py-3 {{ $bgCell }} text-center">
                        <div class="text-xs font-medium text-slate-500">{{ $dia['label'] }}</div>
                        <div class="my-2 text-lg">{{ $iconMap[$dia['icono']] ?? '☀' }}</div>
                        <div class="flex items-center justify-center gap-1">
                            <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
                            <span class="text-xs font-medium {{ $textColor }}">{{ $dia['estado'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-3 py-4 text-center text-xs text-slate-500">Sin datos</div>
        @endif
    </div>


    {{-- Recomendacion (compacta) --}}
    @if($recomendacionDetallada)
    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
        <div class="flex items-start gap-2">
            <svg class="h-4 w-4 flex-shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <div class="text-xs font-medium text-slate-700">Recomendacion del Sistema</div>
                <div class="mt-1 whitespace-pre-line text-xs text-slate-600">{!! nl2br(e($recomendacionDetallada)) !!}</div>
            </div>
        </div>
    </div>
    @endif
</div>
