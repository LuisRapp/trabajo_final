<?php

namespace App\Http\Controllers;

use App\Services\InventarioService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockReporteController extends Controller
{
    /**
     * Exporta los lotes de stock con los mismos filtros de la pantalla.
     * CSV con punto y coma y BOM UTF-8: se abre directo en Excel en espanol.
     */
    public function exportar(Request $request): StreamedResponse
    {
        $lotes = InventarioService::consultaLotesFiltrados([
            'insumo' => $request->filled('insumo') ? (int) $request->input('insumo') : null,
            'proveedor' => $request->filled('proveedor') ? (int) $request->input('proveedor') : null,
            'tipo' => $request->filled('tipo') ? (string) $request->input('tipo') : null,
            'estado' => $request->input('estado', 'disponibles'),
            'desde' => $request->filled('desde') ? (string) $request->input('desde') : null,
            'hasta' => $request->filled('hasta') ? (string) $request->input('hasta') : null,
        ])->get();

        $nombre = 'stock-'.now()->format('Ymd-Hi').'.csv';

        return response()->streamDownload(function () use ($lotes): void {
            $salida = fopen('php://output', 'w');

            // BOM para que Excel respete los acentos.
            fwrite($salida, "\xEF\xBB\xBF");

            fputcsv($salida, [
                'Lote', 'Insumo', 'Proveedor', 'Fecha', 'Tipo',
                'Cantidad inicial', 'Disponible', 'Precio unitario', 'Valor disponible', 'Estado',
            ], ';');

            foreach ($lotes as $lote) {
                fputcsv($salida, [
                    $lote->id_lote_inventario,
                    $lote->insumo?->nombre,
                    $lote->proveedor?->razon_social,
                    optional($lote->fecha_compra)->format('d/m/Y'),
                    ucfirst(str_replace('_', ' ', (string) $lote->tipo_movimiento)),
                    number_format((float) $lote->cantidad_inicial, 2, ',', ''),
                    number_format((float) $lote->cantidad_disponible, 2, ',', ''),
                    number_format((float) $lote->precio_unitario, 2, ',', ''),
                    number_format((float) $lote->valor_disponible, 2, ',', ''),
                    $lote->agotado ? 'Agotado' : 'Disponible',
                ], ';');
            }

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
