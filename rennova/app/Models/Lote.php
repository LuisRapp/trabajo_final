<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Lote extends Model implements AuditableContract
{
    use Auditable;
    
    protected $table = 'lotes';
    protected $primaryKey = 'id_lote';
    protected $fillable = [
        'propietario',
        'condicion_compra',
        'estado',
        'ubicacion',
        'especie',
        'superficie',
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

    public function parteDiarios()
    {
        return $this->hasMany(ParteDiario::class, 'id_lote');
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
