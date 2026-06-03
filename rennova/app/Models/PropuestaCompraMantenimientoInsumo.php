<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropuestaCompraMantenimientoInsumo extends Model
{
    use HasFactory;

    protected $table = 'mantenimiento_purchase_proposal_insumos';

    protected $primaryKey = 'id_mantenimiento_purchase_proposal_insumo';

    protected $fillable = [
        'id_mantenimiento_purchase_proposal',
        'id_insumo',
        'cantidad_requerida',
        'stock_disponible',
        'faltante',
    ];

    public function propuesta()
    {
        return $this->belongsTo(PropuestaCompraMantenimiento::class, 'id_mantenimiento_purchase_proposal');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo');
    }
}
