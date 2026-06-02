<?php

namespace App\Models;

use App\Jobs\ProcessAllocationProposal;
use App\Models\AllocationProposal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Lote extends Model implements AuditableContract
{
    use HasFactory, Auditable;
    
    protected $table = 'lotes';
    protected $primaryKey = 'id_lote';
    protected $fillable = [
        'propietario',
        'condicion_compra',
        'estado',
        'ubicacion',
        'especie',
        'superficie',
        'latitud',
        'longitud',
        'main_task_type',
    ];

    /**
     * Atributos que deben ser auditados
     */
    protected $auditInclude = [
        'propietario',
        'condicion_compra',
        'estado',
        'ubicacion',
        'especie',
        'superficie',
        'main_task_type',
    ];

    /**
     * Eventos que deben ser auditados
     */
    protected $auditEvents = [
        'created',
        'updated',
        'deleted',
        // Nota: Los eventos de attach/detach de relaciones se auditan automáticamente
        // si se usa el trait Auditable y se realizan dentro de transacciones
    ];

    // Nota: evitamos enganchar eventos inexistentes de pivot a nivel de modelo.
    // La auditoría de asignaciones se realiza en el flujo de guardado del componente Livewire.

    protected static function booted(): void
    {
        static::saved(function (self $lote) {
            if ($lote->wasRecentlyCreated || $lote->wasChanged(['estado', 'especie', 'superficie', 'main_task_type'])) {
                // Use afterCommit to avoid database transaction conflicts
                \DB::afterCommit(function () use ($lote) {
                    ProcessAllocationProposal::dispatch($lote->id_lote);
                });
            }
        });
    }

    public function parteDiarios()
    {
        return $this->hasMany(ParteDiario::class, 'id_lote');
    }

    public function tareas()
    {
        return $this->hasMany(LoteTarea::class, 'id_lote');
    }

    public function allocationProposals()
    {
        return $this->hasMany(AllocationProposal::class, 'id_lote');
    }

    public function latestAllocationProposal()
    {
        return $this->hasOne(AllocationProposal::class, 'id_lote')->latestOfMany();
    }

    public function empleados()
    {
        // Pivote correcta para Asignación de Empleados a Lote
        // Estructura esperada: lote_empleado(id_lote, id_empleado, timestamps)
        return $this->belongsToMany(Empleado::class, 'lote_empleado', 'id_lote', 'id_empleado')
                    ->withTimestamps();
    }

    public function maquinarias()
    {
        // Se asume una tabla pivote 'lote_maquinaria' con columnas: id_lote, id_maquinaria
        return $this->belongsToMany(Maquinaria::class, 'lote_maquinaria', 'id_lote', 'id_maquinaria')
                    ->withTimestamps();
    }

    public function cargas()
    {
        return $this->hasMany(Carga::class, 'id_lote');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_lote');
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class, 'id_lote');
    }
}
