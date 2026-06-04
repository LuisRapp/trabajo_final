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
