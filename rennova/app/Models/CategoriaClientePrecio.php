<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class CategoriaClientePrecio extends Model implements Auditable
{
    use SoftDeletes, \OwenIt\Auditing\Auditable;
    
    protected $table = 'categoria_cliente_precio';
    protected $fillable = [
        'cliente_id',
        'categoria_id',
        'precio',
        'fecha_desde',
        'fecha_hasta',
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
        'precio' => 'decimal:2',
    ];

    /**
     * Scope para obtener precios vigentes en una fecha específica
     */
    public function scopeVigentesEn($query, $fecha = null)
    {
        $fecha = $fecha ?? now()->toDateString();
        
        return $query->where('fecha_desde', '<=', $fecha)
                    ->where(function($q) use ($fecha) {
                        $q->whereNull('fecha_hasta')
                          ->orWhere('fecha_hasta', '>=', $fecha);
                    });
    }

    /**
     * Scope para obtener solo precios actuales (fecha_hasta = NULL)
     */
    public function scopeActuales($query)
    {
        return $query->whereNull('fecha_hasta');
    }

    /**
     * Scope para obtener el historial de precios (fecha_hasta != NULL)
     */
    public function scopeHistorico($query)
    {
        return $query->whereNotNull('fecha_hasta');
    }

    /**
     * Relación con Cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'id_cliente');
    }

    /**
     * Relación con Categoría de Madera
     */
    public function categoria()
    {
        return $this->belongsTo(CategoriaMadera::class, 'categoria_id', 'id_categoria_madera');
    }
}
