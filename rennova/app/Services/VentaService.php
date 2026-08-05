<?php

namespace App\Services;

use App\Models\Carga;
use App\Models\Venta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    /**
     * Search for pending cargas for a client within a date range, calculating
     * pricing based on category-client price lists effective on the carga date.
     *
     * @param  int  $idCliente  The client ID
     * @param  string  $fechaDesde  Start date (Y-m-d)
     * @param  string  $fechaHasta  End date (Y-m-d)
     * @return array Array of cargas with pricing data
     *
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public static function buscarCargasPendientes(int $idCliente, string $fechaDesde, string $fechaHasta): array
    {
        if ($idCliente <= 0) {
            throw new \InvalidArgumentException('Client ID must be positive.');
        }

        if (empty($fechaDesde) || empty($fechaHasta)) {
            throw new \InvalidArgumentException('Date range is required.');
        }

        if ($fechaDesde > $fechaHasta) {
            throw new \InvalidArgumentException('Start date must be before or equal to end date.');
        }

        $cliente = \App\Models\Cliente::find($idCliente);
        if (! $cliente) {
            throw new \InvalidArgumentException('Client not found.');
        }

        $nombreCliente = $cliente->razon_social;

        $query = Carga::query()
            ->select([
                'cargas.id_carga',
                'cargas.fecha_carga',
                'cargas.ticket',
                'cargas.peso_neto',
                'cargas.id_categoria_madera',
                'cargas.destino',
                DB::raw('cat.nombre as categoria'),
                DB::raw('ROUND(cargas.peso_neto / 1000.0, 3) as peso_toneladas'),
                DB::raw('COALESCE(ccp.precio, 0) as precio_unitario'),
                DB::raw('ROUND((cargas.peso_neto / 1000.0) * COALESCE(ccp.precio, 0), 2) as subtotal'),
            ])
            ->join('categoria_maderas as cat', 'cat.id_categoria_madera', '=', 'cargas.id_categoria_madera')
            ->leftJoin('categoria_cliente_precio as ccp', function ($join) use ($idCliente) {
                $join->on('ccp.categoria_id', '=', 'cargas.id_categoria_madera')
                    ->where('ccp.cliente_id', '=', $idCliente)
                    ->whereColumn('ccp.fecha_desde', '<=', 'cargas.fecha_carga')
                    ->where(function ($q) {
                        $q->whereNull('ccp.fecha_hasta')
                            ->orWhereColumn('ccp.fecha_hasta', '>=', 'cargas.fecha_carga');
                    });
            })
            ->where('cargas.destino', $nombreCliente)
            ->where('cargas.estado', 'pendiente')
            ->whereBetween('cargas.fecha_carga', [$fechaDesde, $fechaHasta])
            ->orderBy('cargas.fecha_carga');

        $rows = $query->get();

        return $rows->map(function ($r) {
            return [
                'id_carga' => $r->id_carga,
                'fecha_carga' => $r->fecha_carga,
                'ticket' => $r->ticket,
                'categoria' => $r->categoria,
                'peso_kg' => (float) $r->peso_neto,
                'peso_toneladas' => (float) $r->peso_toneladas,
                'precio_unitario' => (float) $r->precio_unitario,
                'subtotal' => (float) $r->subtotal,
            ];
        })->toArray();
    }

    /**
     * Update a sale's observaciones and monto within a transaction.
     *
     * @param  int  $idRecibo  The sale (venta) ID
     * @param  string|null  $observaciones  New observations
     * @param  float|null  $monto  New amount
     * @return \App\Models\Venta The updated sale
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If venta not found
     * @throws \Exception If a database error occurs
     */
    public static function editarVenta(int $idRecibo, ?string $observaciones, ?float $monto): Venta
    {
        DB::beginTransaction();

        try {
            $venta = Venta::findOrFail($idRecibo);

            $venta->update([
                'observaciones' => $observaciones,
                'monto' => $monto,
            ]);

            DB::commit();

            return $venta->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * List sales with optional search filtering by client name, receipt ID, or amount.
     *
     * @param  string|null  $busqueda  Search term
     * @param  int  $perPage  Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Paginated sales
     */
    public static function listarVentas(?string $busqueda = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Venta::with(['cliente', 'cargas'])
            ->orderBy('fecha_emision', 'desc')
            ->orderBy('id_recibo', 'desc');

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->whereHas('cliente', function ($qc) use ($busq) {
                    $qc->where('razon_social', 'LIKE', '%'.$busq.'%');
                })
                    ->orWhere('id_recibo', 'LIKE', '%'.$busq.'%')
                    ->orWhereRaw('LOWER(CAST(monto AS TEXT)) LIKE LOWER(?)', ['%'.$busq.'%']);
            });
        }

        return $query->paginate($perPage);
    }
}
