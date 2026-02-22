<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $reporte_nombre ?? 'Reporte de Lluvias' }}</title>
    <style>
        @page {
            margin: 160px 50px 70px 50px;
            counter-reset: page 1;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', sans-serif;
            color: #2c3e50;
            line-height: 1.5;
            margin: 0;
        }
        header {
            position: fixed;
            top: -130px;
            left: 0px;
            right: 0px;
            height: 120px;
            border-bottom: 3px solid #1a3d2f;
        }
        footer {
            position: fixed;
            bottom: -40px;
            left: 0px;
            right: 0px;
            height: 50px;
            font-size: 10px;
            color: #7f8c8d;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .title-report {
            font-size: 22px;
            font-weight: bold;
            color: #1a3d2f;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 1px;
        }
        .subtitle-report {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 6px;
            display: block;
        }
        .meta-block {
            font-size: 11px;
            color: #7f8c8d;
            margin-top: 4px;
            display: block;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table-data th {
            background-color: #f8f9fa;
            color: #1a3d2f;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            border-bottom: 2px solid #1a3d2f;
            font-size: 11px;
        }
        .table-data td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }
        .kpi-container {
            margin-top: 20px;
            width: 100%;
        }
        .kpi-card {
            background: #f4f7f6;
            padding: 12px;
            border-radius: 5px;
            text-align: center;
        }
        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            color: #1a3d2f;
            display: block;
        }
        .kpi-label {
            font-size: 10px;
            color: #7f8c8d;
            text-transform: uppercase;
        }
        .section-title {
            font-size: 14px;
            color: #1a3d2f;
            margin-top: 24px;
            border-left: 4px solid #1a3d2f;
            padding-left: 8px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo_rennova.png');
        $mostrarLogo = is_file($logoPath) && extension_loaded('gd');
    @endphp

    <header>
        <table style="width: 100%;">
            <tr>
                <td style="width: 20%; vertical-align: top;">
                    @if($mostrarLogo)
                        <img src="{{ $logoPath }}" style="height: 65px;" alt="Logo">
                    @endif
                </td>
                <td style="width: 80%; text-align: right; vertical-align: top;">
                    <h1 class="title-report">REPORTE DIAS CON LLUVIA</h1>
                    <div class="subtitle-report">Periodo: {{ $periodo ?? '-' }}</div>
                    @if(!empty($lote_seleccionado))
                        <div class="meta-block">Lote: {{ $lote_seleccionado->ubicacion ?? $lote_seleccionado->propietario ?? ('Lote ' . $lote_seleccionado->id_lote) }}</div>
                    @else
                        <div class="meta-block">Lote: Todos</div>
                    @endif
                </td>
            </tr>
        </table>
    </header>

    <footer>
        Reporte del clima - Generado por: {{ $generado_por ?? '-' }} | Fecha y hora: {{ $fecha_hora ?? '-' }}
    </footer>

    <main>
        <table class="kpi-container">
            <tr>
                <td style="width: 33%; padding-right: 10px;">
                    <div class="kpi-card">
                        <span class="kpi-value">{{ $total_registros ?? 0 }}</span>
                        <span class="kpi-label">Dias con lluvia</span>
                    </div>
                </td>
                <td style="width: 33%; padding-right: 10px;">
                    <div class="kpi-card">
                        <span class="kpi-value">{{ $total_lotes ?? 0 }}</span>
                        <span class="kpi-label">Lotes afectados</span>
                    </div>
                </td>
                <td style="width: 33%;">
                    <div class="kpi-card">
                        <span class="kpi-value">{{ $periodo ?? '-' }}</span>
                        <span class="kpi-label">Rango analizado</span>
                    </div>
                </td>
            </tr>
        </table>

        <h2 class="section-title">Registro de dias con lluvia</h2>
        <table class="table-data">
            <thead>
                <tr>
                    <th style="width: 12%;">Fecha</th>
                    <th style="width: 18%;">Dia</th>
                    <th style="width: 23%;">Lote</th>
                    <th style="width: 15%;">Lluvia (mm)</th>
                    <th style="width: 20%;">Motivo</th>
                    <th style="width: 12%;">Fuente</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $registro)
                    @php
                        $fecha = \Carbon\Carbon::parse($registro->fecha);
                        $lote = $registro->lote;
                        $loteNombre = $lote?->ubicacion ?? $lote?->propietario ?? ('Lote ' . $registro->id_lote);
                        $diaSemana = $fecha->locale('es')->isoFormat('dddd');
                        $diaSemana = function_exists('mb_convert_case')
                            ? mb_convert_case($diaSemana, MB_CASE_TITLE, 'UTF-8')
                            : ucfirst($diaSemana);
                        $snapshot = is_array($registro->snapshot) ? $registro->snapshot : [];
                        $mm = $snapshot['real_precipitacion_mm'] ?? null;
                    @endphp
                    <tr>
                        <td>{{ $fecha->format('d/m/Y') }}</td>
                        <td>{{ $diaSemana }}</td>
                        <td>{{ $loteNombre }}</td>
                        <td>{{ $mm !== null ? number_format((float) $mm, 1) : '-' }}</td>
                        <td>{{ $registro->razon_real ?? '-' }}</td>
                        <td>{{ strtoupper($registro->fuente_real ?? '-') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #7f8c8d;">No hay registros de lluvia en el periodo seleccionado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if(!empty($dias_por_lote))
            <h2 class="section-title">Resumen por lote</h2>
            <table class="table-data">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th style="width: 20%;">Dias con lluvia</th>
                        <th style="width: 20%;">Mm acumulados</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dias_por_lote as $item)
                        <tr>
                            <td>{{ $item['lote'] }}</td>
                            <td>{{ $item['cantidad'] }}</td>
                            <td>{{ number_format((float) ($item['mm_total'] ?? 0), 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </main>
</body>
</html>
