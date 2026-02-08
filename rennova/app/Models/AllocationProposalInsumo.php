<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AllocationProposalInsumo extends Model
{
    use HasFactory;

    protected $table = 'allocation_proposal_insumos';
    protected $primaryKey = 'id_allocation_proposal_insumo';

    protected $fillable = [
        'id_allocation_proposal',
        'id_insumo',
        'cantidad_semana_1',
        'costo_estimado_semana_1',
        'selected',
    ];

    protected $casts = [
        'selected' => 'boolean',
        'cantidad_semana_1' => 'decimal:2',
        'costo_estimado_semana_1' => 'decimal:2',
    ];

    public function proposal()
    {
        return $this->belongsTo(AllocationProposal::class, 'id_allocation_proposal');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'id_insumo');
    }
}
