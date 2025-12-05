<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use Carbon\Carbon;

class Carga extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;
    protected $table = 'cargas';
    protected $primaryKey = 'id_carga';
    protected $fillable = [
        'id_lote',
        'id_categoria_madera',
        'id_chofer',
        'id_parte_diario',
        'ticket',
        'peso_bruto',
        'tara',
        'peso_neto',
        'destino',
        'fecha_carga',
        'estado',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    public function chofer()
    {
        return $this->belongsTo(Chofer::class, 'id_chofer');
    }

    public function parteDiario()
    {
        return $this->belongsTo(ParteDiario::class, 'id_parte_diario');
    }

    public function categoriaMadera()
    {
        return $this->belongsTo(CategoriaMadera::class, 'id_categoria_madera');
    }

    public function empleados()
    {
        return $this->belongsToMany(Empleado::class, 'carga_empleado', 'id_carga', 'id_empleado')->withTimestamps();
    }

    public function maquinarias()
    {
        return $this->belongsToMany(Maquinaria::class, 'carga_maquinaria', 'id_carga', 'id_maquinaria')->withTimestamps();
    }

    public function cliente()
    {
        // La relación no es directa ya que destino contiene el nombre, no el id
        // Esta relación no se debe usar directamente
        return $this->belongsTo(Cliente::class, 'destino', 'razon_social');
    }

    public function ventas()
    {
        return $this->belongsToMany(Venta::class, 'venta_cargas', 'id_carga', 'id_venta')
                    ->withPivot('precio_unitario', 'peso_toneladas', 'subtotal')
                    ->withTimestamps();
    }

    protected static function booted()
    {
        static::saving(function (self $model) {
            if ($model->fecha_carga && Carbon::parse($model->fecha_carga)->isAfter(Carbon::today())) {
                throw new \InvalidArgumentException('La fecha de la carga no puede ser futura.');
            }
        });
    }
}
