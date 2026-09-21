<?php

namespace App\Services;

use App\Mail\MantenimientoOrdenGeneradaMail;
use App\Models\Mantenimiento;
use App\Models\PropuestaCompraMantenimiento;
use App\Models\PropuestaCompraMantenimientoInsumo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Propuestas de compra de insumos para mantenimientos (PA-01).
 *
 * Crea o regenera la propuesta de compra cuando el kit preventivo tiene
 * faltantes de stock y envía por email la orden generada con sus adjuntos.
 */
class PropuestaCompraMantenimientoService
{
    public function __construct(
        private readonly MantenimientoDocumentsService $documentsService,
        private readonly MailMantenimientoService $mailService,
    ) {}

    /**
     * Crea o regenera la propuesta de compra de insumos para la orden.
     *
     * Los ítems anteriores de la propuesta quedan con baja lógica (SoftDeletes)
     * para preservar el historial de qué insumos contenía.
     *
     * @param  array  $insumosConProblema  Insuficientes: [{insumo_id, insumo, requerido, disponible, faltante}]
     */
    public function crearPropuesta(Mantenimiento $mantenimiento, array $insumosConProblema): PropuestaCompraMantenimiento
    {
        $propuesta = PropuestaCompraMantenimiento::firstOrCreate(
            ['id_mantenimiento' => $mantenimiento->id_mantenimiento],
            [
                'id_maquinaria' => $mantenimiento->id_maquinaria,
                'status' => 'pendiente',
            ]
        );

        if ($propuesta->id_maquinaria !== $mantenimiento->id_maquinaria) {
            $propuesta->id_maquinaria = $mantenimiento->id_maquinaria;
            $propuesta->save();
        }

        PropuestaCompraMantenimientoInsumo::query()
            ->where('id_propuesta_compra_mantenimiento', $propuesta->id_propuesta_compra_mantenimiento)
            ->delete();

        foreach ($insumosConProblema as $insuficiente) {
            if (empty($insuficiente['insumo_id'])) {
                continue;
            }

            PropuestaCompraMantenimientoInsumo::create([
                'id_propuesta_compra_mantenimiento' => $propuesta->id_propuesta_compra_mantenimiento,
                'id_insumo' => (int) $insuficiente['insumo_id'],
                'cantidad_requerida' => (float) ($insuficiente['requerido'] ?? 0),
                'stock_disponible' => (float) ($insuficiente['disponible'] ?? 0),
                'faltante' => (float) ($insuficiente['faltante'] ?? 0),
            ]);
        }

        return $propuesta->fresh(['insumos.insumo.unidadMedida', 'maquinaria.tipoMaquinaria', 'mantenimiento']);
    }

    /**
     * Envía por email la orden generada (y su propuesta de compra) con adjuntos.
     *
     * Con reintentos ante rate-limit; si falla, queda registrado en log y la
     * notificación interna (creada en la transacción del proceso) persiste
     * como canal de registro.
     */
    public function enviarCorreoOrden(
        Mantenimiento $mantenimiento,
        ?PropuestaCompraMantenimiento $propuesta
    ): void {
        $destinatarios = $this->obtenerDestinatarios();

        if (empty($destinatarios)) {
            return;
        }

        try {
            $mantenimiento->loadMissing(['maquinaria.tipoMaquinaria', 'tipoMantenimiento', 'empleados.rolLaboral']);

            $adjuntos = [];
            $adjuntos[] = $this->documentsService->generateMaintenanceOrderPdf($mantenimiento);

            if ($propuesta) {
                $adjuntos[] = $this->documentsService->generatePurchaseOrderPdf($propuesta);
            }

            $this->mailService->enviar(function () use ($destinatarios, $mantenimiento, $propuesta, $adjuntos) {
                Mail::to($destinatarios)
                    ->send(new MantenimientoOrdenGeneradaMail($mantenimiento, $propuesta, $adjuntos));
            });

            if ($propuesta) {
                $metadatos = is_array($propuesta->meta) ? $propuesta->meta : [];
                $metadatos['purchase_order'] = [
                    'sent_at' => now()->toISOString(),
                    'recipients' => $destinatarios,
                    'attachments' => array_map(fn ($adjunto) => $adjunto['path'] ?? null, $adjuntos),
                ];
                $propuesta->meta = $metadatos;
                $propuesta->status = 'enviada';
                $propuesta->save();
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando mail de mantenimiento con adjuntos', [
                'mantenimiento_id' => $mantenimiento->id_mantenimiento,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Destinatarios configurados para los mails del proceso de mantenimiento.
     *
     * @return array<int, string>
     */
    private function obtenerDestinatarios(): array
    {
        $destinatarios = array_values(array_filter((array) config('mail.purchase_order_emails', [])));
        $correoAdmin = trim((string) config('mail.admin_email', ''));

        if ($correoAdmin !== '') {
            $destinatarios[] = $correoAdmin;
        }

        return array_values(array_unique(array_filter($destinatarios)));
    }
}
