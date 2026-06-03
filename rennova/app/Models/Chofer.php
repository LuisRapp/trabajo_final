<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Chofer extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'choferes';

    protected $primaryKey = 'id_chofer';

    protected $fillable = [
        'id_cliente',
        'nombre',
        'apellido',
        'dni',
        'telefono',
        'direccion',
        'estado',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function cargas()
    {
        return $this->hasMany(Carga::class, 'id_chofer');
    }
}
