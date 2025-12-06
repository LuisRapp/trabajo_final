@props([
    'lote' => 'Lote sin especificar',
    'alerta' => 'NORMAL',
    'pronostico' => [],
    'analisisImpacto' => ['diasPerdidos' => 0, 'deficitTn' => 0, 'accionPorcentaje' => 0],
    'recomendacionDetallada' => null,
])

@php
    $alertStyles = [
        'ACELERAR' => 'alert-warning',
        'SUSPENDER' => 'alert-danger',
        'NORMAL' => 'alert-success',
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

    // Calcular la ventana de trabajo
    $diasOperativos = 0;
    $diasPerdidos = $analisisImpacto['diasPerdidos'] ?? 0;
    foreach ($pronostico as $dia) {
        if (!($dia['inactivo'] ?? false)) {
            $diasOperativos++;
        }
    }
    $ventana = $diasOperativos . ' días' ?? '2 días';

    // Encontrar el día crítico (primer día inactivo)
    $diaCritico = 'Desconocido';
    foreach ($pronostico as $dia) {
        if ($dia['inactivo'] ?? false) {
            $diaCritico = ucfirst($dia['label'] ?? 'próximamente');
            break;
        }
    }
@endphp

<div class="py-4" style="max-width: 100%; overflow-x: hidden;">
    <!-- Alerta Principal -->
    <div class="alert mb-4 p-4 rounded-2 border-3 {{ $alertStyles[$alerta] ?? $alertStyles['NORMAL'] }}" style="border-width: 2px;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-2" style="font-size: 1.5rem;">{{ $alertTitle[$alerta] ?? $alertTitle['NORMAL'] }}</h2>
                <p class="mb-0" style="font-size: 1.05rem;">
                    Se pronostican lluvias fuertes para el <span class="fw-bold">{{ $diaCritico }}</span>. 
                    <br>Ventana operativa: <span class="fw-bold">{{ $ventana }}</span>.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-light text-dark p-3" style="font-size: 1rem;">
                    📍 {{ $lote }}
                </span>
            </div>
        </div>
    </div>

    <!-- Pronóstico de 7 Días -->
    <div class="mt-5">
        <h5 class="fw-bold text-dark mb-3">📅 Pronóstico Operativo (7 Días)</h5>
        <div class="row g-2">
            @if(count($pronostico) > 0)
                @foreach($pronostico as $dia)
                    @php
                        $isInactivo = $dia['inactivo'] ?? false;
                        $isBarro = ($dia['suelo'] ?? null) === 'BARRO';
                        $bgClass = $isInactivo
                            ? 'bg-danger text-white'
                            : 'bg-light border border-success';
                        $badgeClass = $isInactivo
                            ? 'bg-white text-danger'
                            : 'bg-success text-white';
                    @endphp
                    <div class="col-6 col-md-4 col-lg-1-7">
                        <div class="card h-100 {{ $bgClass }} text-center rounded-2 shadow-sm p-2">
                            <div class="card-body p-2">
                                <small class="fw-bold d-block mb-1" style="font-size: 0.8rem;">{{ $dia['label'] }}</small>
                                <div style="font-size: 1.8rem;" class="mb-2">{{ $iconMap[$dia['icono']] ?? '☀️' }}</div>
                                <span class="badge {{ $badgeClass }}" style="font-size: 0.7rem;">
                                    {{ $dia['estado'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12">
                    <p class="text-muted text-center py-3">Datos de pronóstico no disponibles</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Análisis de Impacto -->
    <div class="mt-5">
        <h5 class="fw-bold text-dark mb-3">📊 Análisis de Impacto</h5>
        <div class="row g-3">
            <!-- Días Perdidos -->
            <div class="col-md-4">
                <div class="card h-100 rounded-2 shadow-sm border-start border-danger border-5 p-3">
                    <h6 class="fw-bold text-muted small mb-3 text-uppercase">📅 Días Perdidos</h6>
                    <div class="mb-2">
                        <span class="display-4 fw-bold text-danger">{{ $analisisImpacto['diasPerdidos'] ?? 0 }}</span>
                        <small class="text-muted ms-2">días</small>
                    </div>
                    <small class="text-muted">Incluye lluvia y barro post-lluvia</small>
                </div>
            </div>

            <!-- Déficit Proyectado -->
            <div class="col-md-4">
                <div class="card h-100 rounded-2 shadow-sm border-start border-warning border-5 p-3">
                    <h6 class="fw-bold text-muted small mb-3 text-uppercase">⚠️ Déficit Proyectado</h6>
                    <div class="mb-2">
                        <span class="display-4 fw-bold text-warning">{{ abs($analisisImpacto['deficitTn'] ?? 0) }}</span>
                        <small class="text-muted ms-2">Tn</small>
                    </div>
                    <small class="text-muted">Volumen en riesgo si no se actúa</small>
                </div>
            </div>

            <!-- Acción Sugerida -->
            <div class="col-md-4">
                @php
                    $accionConfig = match($alerta) {
                        'SUSPENDER' => [
                            'color' => 'danger',
                            'bg' => '#fff0f0',
                            'texto' => 'SUSPENDER OPERACIONES',
                            'detalle' => 'Condiciones no permiten trabajar',
                            'icono' => '🛑',
                            'mostrar_porcentaje' => false
                        ],
                        'ACELERAR' => [
                            'color' => 'warning',
                            'bg' => '#fff8e1',
                            'texto' => '+' . ($analisisImpacto['accionPorcentaje'] ?? 0) . '%',
                            'detalle' => 'Acelerar ritmo de producción',
                            'icono' => '⚡',
                            'mostrar_porcentaje' => true
                        ],
                        default => [
                            'color' => 'success',
                            'bg' => '#f0f8f4',
                            'texto' => 'OPERACIÓN NORMAL',
                            'detalle' => 'Mantener ritmo actual',
                            'icono' => '✅',
                            'mostrar_porcentaje' => false
                        ]
                    };
                @endphp
                <div class="card h-100 rounded-2 shadow-sm border-start border-{{ $accionConfig['color'] }} border-5 p-3" style="background-color: {{ $accionConfig['bg'] }};">
                    <h6 class="fw-bold text-{{ $accionConfig['color'] }} small mb-3 text-uppercase">{{ $accionConfig['icono'] }} Acción Sugerida</h6>
                    <div class="mb-2">
                        <span class="display-4 fw-bold text-{{ $accionConfig['color'] }}">{{ $accionConfig['texto'] }}</span>
                    </div>
                    <small class="fw-semibold text-{{ $accionConfig['color'] }}">{{ $accionConfig['detalle'] }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recomendación Detallada -->
    @if($recomendacionDetallada)
    <div class="mt-4">
        <div class="card rounded-2 shadow-sm border-0" style="background-color: #f8f9fa;">
            <div class="card-body p-3">
                <h6 class="fw-bold text-dark mb-2" style="font-size: 0.9rem;"><i class="bi bi-lightbulb"></i> Recomendación del Sistema</h6>
                <div style="font-size: 0.8rem; line-height: 1.5; white-space: pre-line; font-family: 'Courier New', monospace; color: #333;">{!! nl2br(e($recomendacionDetallada)) !!}</div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    .col-lg-1-7 {
        flex: 0 0 calc(100% / 7);
    }
    @media (max-width: 992px) {
        .col-lg-1-7 {
            flex: 0 0 calc(100% / 4);
        }
    }
    @media (max-width: 768px) {
        .col-lg-1-7 {
            flex: 0 0 calc(100% / 2);
        }
    }
</style>
