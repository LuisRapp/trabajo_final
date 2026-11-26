<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Services\VentaService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('ventas.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venta $venta)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venta $venta)
    {
        //
    }

    /**
     * Comprobante de la venta en PDF, con criterio de salida impresa:
     * encabezado, datos del cliente, detalle, totales y pie con emision.
     */
    public function comprobante(int $venta)
    {
        $ventaModel = VentaService::obtenerParaComprobante($venta);

        $options = new Options;
        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $html = view('ventas.comprobante-pdf', [
            'venta' => $ventaModel,
            'emitidoPor' => auth()->user()?->name ?? 'Sistema',
            'emitidoEl' => now(),
        ])->render();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $canvas->page_script('
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 8;
            $text = "Pagina $PAGE_NUM de $PAGE_COUNT";
            $width = $fontMetrics->getTextWidth($text, $font, $size);
            $x = ($pdf->get_width() - $width) / 2;
            $pdf->text($x, $pdf->get_height() - 28, $text, $font, $size, [168, 162, 158]);
        ');

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="comprobante-venta-'.$venta.'.pdf"',
        ]);
    }
}
