<?php

namespace App\Services;

use App\Models\Carga;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class VentaService
{
    /**
     * Register a new sale: creates the venta record, attaches cargas with
     * pricing pivot data, and marks them as facturada.
     *
     * Executes within a database transaction.
     *
     * @param  int  $idCliente  The client ID
     * @param  array  $detalleCargas  Array of carga data with keys: id_carga, precio_unitario, peso_toneladas, subtotal
     * @param  float  $totalVenta  Total sale amount
     * @param  string  $observaciones  Observations for the sale
     * @return \App\Models\Venta The created sale
     *
     * @throws \Exception If a database error occurs
     */
    public static function registrarVenta(int $idCliente, array $detalleCargas, float $totalVenta, string $observaciones): Venta
    {
        DB::beginTransaction();

        try {
            $venta = Venta::create([
                'id_cliente' => $idCliente,
                'fecha_emision' => now()->toDateString(),
                'monto' => $totalVenta,
                'observaciones' => $observaciones,
            ]);

            foreach ($detalleCargas as $detalle) {
                $venta->cargas()->attach($detalle['id_carga'], [
                    'precio_unitario' => $detalle['precio_unitario'],
                    'peso_toneladas' => $detalle['peso_toneladas'],
                    'subtotal' => $detalle['subtotal'],
                ]);

                Carga::where('id_carga', $detalle['id_carga'])
                    ->update(['estado' => 'facturada']);
            }

            DB::commit();

            return $venta;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deactivate a sale: soft-deletes the venta and reverts associated
     * cargas back to pendiente state.
     *
     * Executes within a database transaction.
     *
     * @param  int  $idRecibo  The sale (venta) ID to deactivate
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If venta not found
     * @throws \Exception If a database error occurs
     */
    public static function darDeBaja(int $idRecibo): void
    {
        DB::beginTransaction();

        try {
            $venta = Venta::with('cargas')->findOrFail($idRecibo);

            $venta->delete();

            foreach ($venta->cargas as $carga) {
                $carga->update(['estado' => 'pendiente']);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
