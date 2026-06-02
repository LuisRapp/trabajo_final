<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AllocationProposal extends Model
{
    use HasFactory;

    protected $table = 'allocation_proposals';
    protected $primaryKey = 'id_allocation_proposal';

    protected $fillable = [
        'id_lote',
        'id_lote_tarea',
        'tipo_tarea',
        'especie',
        'superficie_ha',
        'estimated_person_days',
        'estimated_machine_days',
        'estimated_duration_days',
        'suggested_team_size',
        'suggested_machinery_count',
        'meta',
        'status',
        'confirmed_at',
        'applied_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'superficie_ha' => 'decimal:2',
        'estimated_person_days' => 'decimal:2',
        'estimated_machine_days' => 'decimal:2',
        'estimated_duration_days' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    public function loteTarea()
    {
        return $this->belongsTo(LoteTarea::class, 'id_lote_tarea');
    }

    public function proposedEmployees()
    {
        return $this->hasMany(AllocationProposalEmployee::class, 'id_allocation_proposal');
    }

    public function proposedMaquinarias()
    {
        return $this->hasMany(AllocationProposalMaquinaria::class, 'id_allocation_proposal');
    }

    public function proposedInsumos()
    {
        return $this->hasMany(AllocationProposalInsumo::class, 'id_allocation_proposal');
    }
}
