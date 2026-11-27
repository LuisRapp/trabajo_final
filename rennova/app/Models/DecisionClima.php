<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DecisionClima extends Model
{
    use SoftDeletes;

    protected $table = 'decisiones_climaticas';

    protected $primaryKey = 'id_decision';

    protected $fillable = [
        'id_lote',
        'fecha',
        'estrategia',
        'nivel',
        'dias_no_operativos',
        'deficit_tn',
        'residual_tn',
        'meta_diaria',
        'mensaje',
        'payload',
    ];

    protected $casts = [
        'fecha' => 'date',
        'payload' => 'array',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }
}
