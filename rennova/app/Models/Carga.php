<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carga extends Model
{
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
}
