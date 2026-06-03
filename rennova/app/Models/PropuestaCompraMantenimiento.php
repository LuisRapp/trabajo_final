<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropuestaCompraMantenimiento extends Model
{
    use HasFactory;

    protected $table = 'mantenimiento_purchase_proposals';

    protected $primaryKey = 'id_mantenimiento_purchase_proposal';

    protected $fillable = [
        'id_mantenimiento',
        'id_maquinaria',
        'status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class, 'id_mantenimiento', 'id_mantenimiento');
    }

    public function maquinaria()
    {
        return $this->belongsTo(Maquinaria::class, 'id_maquinaria', 'id_maquinaria');
    }

    public function insumos()
    {
        return $this->hasMany(PropuestaCompraMantenimientoInsumo::class, 'id_mantenimiento_purchase_proposal');
    }
}
