<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AllocationProposalMaquinaria extends Model
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
        return $this->belongsTo(AllocationProposal::class, 'id_allocation_proposal');
    }

    public function maquinaria()
    {
        return $this->belongsTo(Maquinaria::class, 'id_maquinaria');
    }
}
