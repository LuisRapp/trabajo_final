<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Orden de Mantenimiento</title>
    <style>
        @page {
            margin: 135px 45px 65px 45px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.45;
        }
        header {
            position: fixed;
            top: -105px;
            left: 0;
            right: 0;
            height: 95px;
            border-bottom: 2px solid #1a3d2f;
        }
        footer {
            position: fixed;
            bottom: -35px;
            left: 0;
            right: 0;
            height: 35px;
            border-top: 1px solid #e5ebea;
            color: #6b7280;
            text-align: center;
            font-size: 10px;
            padding-top: 8px;
        }
        .doc-title {
            margin: 0;
            font-size: 18px;
            letter-spacing: .6px;
            color: #1a3d2f;
            font-weight: 700;
            text-transform: uppercase;
        }
        .doc-subtitle {
            margin-top: 4px;
            color: #6b7280;
            font-size: 11px;
        }
        .section-title {
            margin: 18px 0 8px;
            font-size: 13px;
            color: #1a3d2f;
            font-weight: 700;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-order td,
        .table-order th,
        .table-personal td,
        .table-personal th {
            border: 1px solid #dbe5e1;
            padding: 8px;
            vertical-align: top;
        }
        .table-order th,
        .table-personal th {
            background: #f3f6f5;
            color: #1a3d2f;
            font-size: 11px;
            text-align: left;
            font-weight: 700;
        }
        .label {
            width: 34%;
            background: #f8faf9;
            font-weight: 700;
            color: #374151;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo_rennova.png');
        $mostrarLogo = is_file($logoPath) && extension_loaded('gd');
        $asignado = $mantenimiento->empleados->first();
        $fechaProgramada = $mantenimiento->fecha_programada
            ? \Carbon\Carbon::parse($mantenimiento->fecha_programada)->format('d/m/Y')
            : 'N/A';
        $fechaInicio = $mantenimiento->fecha_inicio
            ? \Carbon\Carbon::parse($mantenimiento->fecha_inicio)->format('d/m/Y')
            : 'N/A';
    @endphp

    <header>
        <table>
            <tr>
                <td style="width:20%;vertical-align:top;">
                    @if($mostrarLogo)
                        <img src="{{ $logoPath }}" style="height:58px;" alt="Logo">
                    @endif
                </td>
                <td style="width:80%;text-align:right;vertical-align:top;">
                    <h1 class="doc-title">Orden de Mantenimiento</h1>
                    <div class="doc-subtitle">Orden #{{ $mantenimiento->id_mantenimiento }}</div>
                    <div class="doc-subtitle">Generado por: Sistema | {{ $generatedAt->format('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        Documento operativo de mantenimiento | Rennova
    </footer>

    <main>
        <h2 class="section-title">Datos de la orden</h2>
        <table class="table-order">
            <tbody>
                <tr>
                    <td class="label">Estado</td>
                    <td colspan="3">{{ ucfirst((string) $mantenimiento->estado) }}</td>
                </tr>
                <tr>
                    <td class="label">Fecha programada</td>
                    <td>{{ $fechaProgramada }}</td>
                    <td class="label">Fecha de inicio</td>
                    <td>{{ $fechaInicio }}</td>
                </tr>
                <tr>
                    <td class="label">Maquinaria</td>
                    <td colspan="3">{{ $mantenimiento->maquinaria->modelo ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Tipo de mantenimiento</td>
                    <td colspan="3">{{ $mantenimiento->tipoMantenimiento->nombre ?? 'Preventivo' }}</td>
                </tr>
            </tbody>
        </table>

        <h2 class="section-title">Asignacion de personal</h2>
        <table class="table-personal">
            <thead>
                <tr>
                    <th style="width:14%;">ID</th>
                    <th style="width:56%;">Apellido y nombre</th>
                    <th style="width:30%;">Rol</th>
                </tr>
            </thead>
            <tbody>
                @if($asignado)
                    <tr>
                        <td>{{ $asignado->id_empleado }}</td>
                        <td>{{ $asignado->apellido }}, {{ $asignado->nombre }}</td>
                        <td>{{ $asignado->rolLaboral->nombre ?? 'Sin rol' }}</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="3" style="text-align:center;color:#6b7280;">No hay personal asignado.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </main>
</body>
</html>
