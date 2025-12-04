@props([
    'lote' => 'AP-04 (Apóstoles)',
    'ventana' => '2 días',
    'diaCritico' => 'Jueves',
    'recomendacion' => 'ACELERAR', // ACELERAR | SUSPENDER | NORMAL
    'pronostico' => [
        ['label' => 'HOY (Mar)', 'estado' => 'Operativo', 'icono' => 'sun', 'inactivo' => false, 'suelo' => null],
        ['label' => 'Mié', 'estado' => 'Operativo', 'icono' => 'sun', 'inactivo' => false, 'suelo' => null],
        ['label' => 'Jue', 'estado' => 'LLUVIA', 'icono' => 'storm', 'inactivo' => true, 'suelo' => 'LLUVIA'],
        ['label' => 'Vie', 'estado' => 'BARRO', 'icono' => 'fog', 'inactivo' => true, 'suelo' => 'BARRO'],
        ['label' => 'Sáb', 'estado' => 'BARRO', 'icono' => 'cloud', 'inactivo' => true, 'suelo' => 'BARRO'],
        ['label' => 'Dom', 'estado' => 'Secando', 'icono' => 'sun', 'inactivo' => false, 'suelo' => null],
        ['label' => 'Lun', 'estado' => 'Normal', 'icono' => 'sun', 'inactivo' => false, 'suelo' => null],
    ],
    'diasPerdidos' => 3,
    'rangoPerdidos' => '(Jue-Sáb)',
    'deficitTn' => -150,
    'accionPorcentaje' => 25,
    'accionDias' => 'Hoy y Mañana',
])

@php
    $alertStyles = [
        'ACELERAR' => 'bg-amber-100 border-amber-300 text-amber-900',
        'SUSPENDER' => 'bg-red-100 border-red-300 text-red-900',
        'NORMAL' => 'bg-emerald-100 border-emerald-300 text-emerald-900',
    ];
    $alertTitle = [
        'ACELERAR' => 'ALERTA: ACELERAR PRODUCCIÓN',
        'SUSPENDER' => 'ALERTA: SUSPENDER OPERACIONES',
        'NORMAL' => 'OPERACIÓN NORMAL',
    ];
    $iconMap = [
        'sun' => '☀️',
        'storm' => '⛈️',
        'fog' => '🌫️',
        'cloud' => '☁️',
    ];
@endphp

<div class="mx-auto max-w-7xl px-4 py-6">
    <div class="flex items-start justify-between rounded-xl border p-5 shadow-sm {{ $alertStyles[$recomendacion] ?? $alertStyles['NORMAL'] }}">
        <div>
            <h2 class="text-xl font-bold">{{ $alertTitle[$recomendacion] ?? $alertTitle['NORMAL'] }}</h2>
            <p class="mt-1 text-sm">
                Se pronostican lluvias fuertes para el <span class="font-semibold">{{ $diaCritico }}</span>. Ventana de trabajo: {{ $ventana }}.
            </p>
        </div>
        <div class="text-right">
            <span class="inline-flex items-center rounded-md border px-3 py-1 text-sm font-medium">Lote: {{ $lote }}</span>
        </div>
    </div>

    <h5 class="mt-6 mb-3 flex items-center gap-2 text-sm text-neutral-600"><span class="font-medium">Pronóstico Operativo (7 Días)</span></h5>
    <div class="grid grid-cols-2 gap-3 md:grid-cols-7">
        @foreach($pronostico as $dia)
            @php
                $isInactivo = $dia['inactivo'] ?? false;
                $isBarro = ($dia['suelo'] ?? null) === 'BARRO';
                $cardClasses = $isInactivo
                    ? 'bg-red-600 text-white'
                    : ($isBarro ? 'bg-amber-100 border border-amber-300' : 'bg-white border');
            @endphp
            <div class="rounded-xl p-3 text-center shadow-sm {{ $cardClasses }}">
                <small class="block font-semibold opacity-75">{{ $dia['label'] }}</small>
                <div class="my-2 text-3xl">{{ $iconMap[$dia['icono']] ?? '☀️' }}</div>
                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold @if($isInactivo) bg-white text-red-600 @elseif($isBarro) bg-amber-200 text-amber-900 @else bg-emerald-100 text-emerald-800 @endif">
                    {{ $dia['estado'] }}
                </span>
            </div>
        @endforeach
    </div>

    <h5 class="mt-8 mb-3 flex items-center gap-2 text-sm text-neutral-600"><span class="font-medium">Análisis de Impacto</span></h5>
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border-l-4 border-red-500 bg-white p-4 shadow-sm">
            <h6 class="mb-2 text-xs uppercase tracking-wide text-neutral-500">Días Perdidos Est.</h6>
            <div class="flex items-end gap-2">
                <div class="text-4xl font-bold">{{ $diasPerdidos }}</div>
                <span class="font-semibold text-red-600">{{ $rangoPerdidos }}</span>
            </div>
            <small class="text-neutral-500">Incluye 1 de lluvia + 2 de barro</small>
        </div>
        <div class="rounded-xl border-l-4 border-amber-500 bg-white p-4 shadow-sm">
            <h6 class="mb-2 text-xs uppercase tracking-wide text-neutral-500">Déficit Proyectado</h6>
            <div class="flex items-end gap-2">
                <div class="text-4xl font-bold text-red-600">{{ $deficitTn }}</div>
                <span class="text-lg text-neutral-600">Toneladas</span>
            </div>
            <small class="text-neutral-500">Volumen en riesgo si no se actúa</small>
        </div>
        <div class="rounded-xl border-l-4 border-emerald-600 bg-emerald-50 p-4 shadow-sm">
            <h6 class="mb-2 text-xs font-bold uppercase tracking-wide text-emerald-700">Acción Sugerida</h6>
            <div class="flex items-end gap-2">
                <div class="text-4xl font-bold text-emerald-700">+{{ $accionPorcentaje }}%</div>
                <span class="text-lg text-emerald-700">{{ $accionDias }}</span>
            </div>
            <small class="font-semibold text-emerald-700">Aumentar ritmo para cubrir el déficit</small>
        </div>
    </div>

    <div class="mt-6 flex justify-end gap-2">
        <span class="text-xs text-neutral-500">Modo Demo:</span>
        <button type="button" class="rounded-md border px-3 py-1 text-xs" onclick="window.dispatchEvent(new CustomEvent('simular', { detail: 'LLUVIA' }))">⛈️ Simular Lluvia</button>
        <button type="button" class="rounded-md border px-3 py-1 text-xs" onclick="window.dispatchEvent(new CustomEvent('simular', { detail: 'SOL' }))">☀️ Simular Sol</button>
    </div>
</div>
