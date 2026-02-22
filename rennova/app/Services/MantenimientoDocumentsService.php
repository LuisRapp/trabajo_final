<?php

namespace App\Services;

use App\Models\Mantenimiento;
use App\Models\MantenimientoPurchaseProposal;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

class MantenimientoDocumentsService
{
    /**
     * Genera PDF de orden de mantenimiento y lo guarda en storage/app.
     */
    public function generateMaintenanceOrderPdf(Mantenimiento $mantenimiento): array
    {
        $mantenimiento->loadMissing([
            'maquinaria.tipoMaquinaria',
            'tipoMantenimiento',
            'empleados.rolLaboral',
        ]);

        $html = view('mantenimientos.pdf.orden-mantenimiento', [
            'mantenimiento' => $mantenimiento,
            'generatedAt' => now(),
        ])->render();

        $pdf = $this->renderPdf($html);

        $filename = 'orden_mantenimiento_' . $mantenimiento->id_mantenimiento . '_' . now()->format('Ymd_His') . '.pdf';
        $relativePath = 'private/mantenimientos/' . $mantenimiento->id_mantenimiento . '/' . $filename;
        Storage::disk('local')->put($relativePath, $pdf);

        return [
            'filename' => $filename,
            'path' => $relativePath,
            'absolute_path' => Storage::disk('local')->path($relativePath),
        ];
    }

    /**
     * Genera PDF de orden de compra de mantenimiento y lo guarda en storage/app.
     */
    public function generatePurchaseOrderPdf(MantenimientoPurchaseProposal $proposal): array
    {
        $proposal->loadMissing([
            'mantenimiento.tipoMantenimiento',
            'maquinaria.tipoMaquinaria',
            'insumos.insumo.unidadMedida',
        ]);

        $html = view('mantenimientos.pdf.orden-compra', [
            'proposal' => $proposal,
            'generatedAt' => now(),
        ])->render();

        $pdf = $this->renderPdf($html);

        $mantenimientoId = (int) $proposal->id_mantenimiento;
        $filename = 'orden_compra_mantenimiento_' . $mantenimientoId . '_' . now()->format('Ymd_His') . '.pdf';
        $relativePath = 'private/mantenimientos/' . $mantenimientoId . '/' . $filename;
        Storage::disk('local')->put($relativePath, $pdf);

        return [
            'filename' => $filename,
            'path' => $relativePath,
            'absolute_path' => Storage::disk('local')->path($relativePath),
        ];
    }

    private function renderPdf(string $html): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}

