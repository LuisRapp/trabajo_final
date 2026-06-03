<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropuestaAsignacionMaquinaria extends Model
{
    use HasFactory;

    protected $table = 'allocation_proposal_maquinarias';

    protected $primaryKey = 'id_allocation_proposal_maquinaria';

    protected $fillable = [
        'id_allocation_proposal',
        'id_maquinaria',
        'tipo_sugerido',
        'score',
        'selected',
    ];

    protected $casts = [
        'selected' => 'boolean',
        'score' => 'decimal:4',
    ];

    public function proposal()
    {
        return $this->belongsTo(PropuestaAsignacion::class, 'id_allocation_proposal');
    }

    public function maquinaria()
    {
        return $this->belongsTo(Maquinaria::class, 'id_maquinaria');
    }
}
