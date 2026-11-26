<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de venta {{ $venta->id_recibo }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1c1917; margin: 0; }
        .encabezado { border-bottom: 2px solid #2f5233; padding-bottom: 10px; margin-bottom: 14px; }
        .marca { font-size: 18px; font-weight: bold; color: #2f5233; }
        .documento { font-size: 10px; letter-spacing: 1px; color: #57534e; text-transform: uppercase; }
        .numero { font-size: 16px; font-weight: bold; text-align: right; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .meta td { vertical-align: top; padding: 3px 0; }
        .meta .etiqueta { color: #57534e; width: 22%; }
        .bloque { border: 1px solid #d6d3cd; padding: 8px; margin-bottom: 14px; }
        .bloque-titulo { font-size: 8px; letter-spacing: 1px; text-transform: uppercase; color: #57534e; margin-bottom: 4px; }
        table.detalle { width: 100%; border-collapse: collapse; }
        table.detalle th { background: #f3f0eb; border-bottom: 1px solid #d6d3cd; padding: 4px 3px; font-size: 7px; text-transform: uppercase; letter-spacing: 0.3px; color: #57534e; text-align: left; vertical-align: bottom; }
        table.detalle td { padding: 3px; border-bottom: 1px solid #e7e5e4; font-size: 8.5px; }
        table.detalle tr:nth-child(even) td { background: #faf9f7; }
        table.detalle th.num, table.detalle td.num { text-align: right; }
        table.detalle th.centro, table.detalle td.centro { text-align: center; }
        .totales { width: 45%; margin-left: 55%; margin-top: 8px; border-collapse: collapse; }
        .totales td { padding: 4px 0; }
        .totales .etiqueta { color: #57534e; }
        .totales .valor { text-align: right; }
        .totales .gran-total td { border-top: 2px solid #2f5233; padding-top: 6px; font-size: 12px; font-weight: bold; color: #2f5233; }
        .observaciones { margin-top: 12px; font-size: 9px; color: #57534e; }
        .pie { margin-top: 18px; border-top: 1px solid #d6d3cd; padding-top: 8px; font-size: 8px; color: #57534e; }
        .fin { text-align: center; font-size: 8px; color: #a8a29e; margin-top: 10px; }
        .no-fiscal { display: inline-block; border: 1px solid #991b1b; color: #991b1b; font-size: 8px; padding: 1px 4px; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="encabezado">
        <table width="100%">
            <tr>
                <td>
                    <div class="marca">Rennova</div>
                    <div class="documento">Comprobante de venta</div>
                </td>
                <td style="text-align: right;">
                    <div class="numero">N.º {{ str_pad((string) $venta->id_recibo, 6, '0', STR_PAD_LEFT) }}</div>
                    <div class="documento">{{ $venta->fecha_emision ? \Carbon\Carbon::parse($venta->fecha_emision)->format('d/m/Y') : '' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="bloque">
        <div class="bloque-titulo">Cliente</div>
        <strong>{{ $venta->cliente?->razon_social ?? 'Sin cliente' }}</strong><br>
        @if ($venta->cliente?->cuit)
            CUIT: {{ $venta->cliente->cuit }}<br>
        @endif
        @if ($venta->cliente?->direccion)
            {{ $venta->cliente->direccion }}
        @endif
    </div>

    <table class="detalle">
        <thead>
            <tr>
                <th style="width: 3%;" class="centro">#</th>
                <th style="width: 9%;">Ticket</th>
                <th style="width: 8%;">Fecha</th>
                <th style="width: 13%;">Chofer</th>
                <th style="width: 9%;">Lote</th>
                <th style="width: 12%;">Categoría</th>
                <th style="width: 8%;" class="num">Bruto (kg)</th>
                <th style="width: 8%;" class="num">Tara (kg)</th>
                <th style="width: 8%;" class="num">Neto (kg)</th>
                <th style="width: 9%;" class="num">Precio/Tn</th>
                <th style="width: 12%;" class="num">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($venta->cargas as $carga)
                <tr>
                    <td class="centro">{{ $loop->iteration }}</td>
                    <td>{{ $carga->ticket }}</td>
                    <td>{{ $carga->fecha_carga ? \Carbon\Carbon::parse($carga->fecha_carga)->format('d/m/Y') : '—' }}</td>
                    <td>{{ $carga->chofer ? $carga->chofer->apellido.', '.$carga->chofer->nombre : '—' }}</td>
                    <td>{{ $carga->lote?->propietario ?? '—' }}</td>
                    <td>{{ $carga->categoriaMadera?->nombre ?? '—' }}</td>
                    <td class="num">{{ number_format((float) $carga->peso_bruto, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $carga->tara, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $carga->peso_neto, 0, ',', '.') }}</td>
                    <td class="num">$ {{ number_format((float) $carga->pivot->precio_unitario, 2, ',', '.') }}</td>
                    <td class="num">$ {{ number_format((float) $carga->pivot->subtotal, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td class="etiqueta">Cargas</td>
            <td class="valor">{{ $venta->cargas->count() }}</td>
        </tr>
        <tr>
            <td class="etiqueta">Total toneladas</td>
            <td class="valor">{{ number_format((float) $venta->cargas->sum(fn ($c) => (float) $c->pivot->peso_toneladas), 3, ',', '.') }}</td>
        </tr>
        <tr class="gran-total">
            <td>Total</td>
            <td class="valor">$ {{ number_format((float) $venta->monto, 2, ',', '.') }}</td>
        </tr>
    </table>

    @if ($venta->observaciones)
        <div class="observaciones">
            <strong>Observaciones:</strong> {{ $venta->observaciones }}
        </div>
    @endif

    <div class="pie">
        <table width="100%">
            <tr>
                <td>Emitido por {{ $emitidoPor }} el {{ $emitidoEl->format('d/m/Y H:i') }}</td>
                <td style="text-align: right;"><span class="no-fiscal">Documento no fiscal</span></td>
            </tr>
        </table>
    </div>

    <div class="fin">— Fin del comprobante —</div>
</body>
</html>
