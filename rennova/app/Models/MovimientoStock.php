<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class MovimientoStock extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'movimiento_stocks';

    protected $primaryKey = 'id_movimiento_stock';

    protected $fillable = [
        'id_insumo',
        'tipo',
        'cantidad',
        'fecha',
        'motivo',
        'precio_unitario',
        'id_lote_inventario',
        'costo_total_movimiento',
        'id_parte_diario',
    ];

    protected $casts = [
        'fecha' => 'date',
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'costo_total_movimiento' => 'decimal:2',
    ];

    // Relaciones
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo');
    }

    public function loteInventario()
    {
        return $this->belongsTo(LoteInventario::class, 'id_lote_inventario');
    }

    public function parteDiario()
    {
        return $this->belongsTo(ParteDiario::class, 'id_parte_diario');
    }

    /**
     * Filter movements linked to a specific parte diario (direct or by fallback matching).
     */
    public function scopeDelParteDiario($query, int $parteDiarioId, ?string $fecha = null): void
    {
        $query->where(function ($q) use ($parteDiarioId, $fecha) {
            $q->where('id_parte_diario', $parteDiarioId)
                ->orWhere(function ($fallback) use ($parteDiarioId, $fecha) {
                    $fallback->whereNull('id_parte_diario')
                        ->where('motivo', 'LIKE', 'Parte Diario #'.$parteDiarioId.'%');
                    if ($fecha) {
                        $fallback->whereDate('fecha', $fecha);
                    }
                });
        });
    }

    /**
     * @deprecated Use InventarioService::registrarSalida() instead
     */

    /**
     * @deprecated Use InventarioService::registrarEntrada() instead
     */

    /**
     * @deprecated Use InventarioService::stockDisponible() instead
     */

    /**
     * @deprecated Use InventarioService::precioPromedio() instead
     */
}
