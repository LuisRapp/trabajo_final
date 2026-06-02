<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Carbon\Carbon;


class ParteDiario extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'parte_diarios';
    protected $primaryKey = 'id_parte_diario';
    protected $fillable = [
        'id_lote',
        'fecha',
        'es_dia_caido',
        'observaciones',
        'activo',
        'costo_mano_obra',
        'costo_insumos',
        'costo_maquinaria',
        'costo_total_dia',
        'costo_unitario_calculado'
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'id_lote')->withTrashed();
    }

    public function empleados()
    {
        return $this->belongsToMany(Empleado::class, 'parte_diario_empleado', 'id_parte_diario', 'id_empleado')->withTrashed()->withTimestamps();
    }

    public function cargas()
    {
        return $this->hasMany(Carga::class, 'id_parte_diario');
    }

    /**
     * Calcular y guardar todos los costos del parte diario.
     * 
     * Desglose:
     * - Costo Mano de Obra: empleados que trabajaron (jornal o destajo)
     * - Costo Insumos: movimientos de salida del día
     * - Costo Maquinaria: alquiler + mantenimientos completados ese día
     * 
     * @return void
     */
    public function calcularYGuardarCostos()
    {
        $costoManoObra = 0.0;
        $costoInsumos = 0.0;
        $costoMaquinaria = 0.0;
        $totalToneladas = 0.0;

        // ===== A. COSTO MANO DE OBRA =====
        // Obtener empleados que trabajaron en este parte
        $empleados = $this->empleados()->with('rolLaboral')->get();
        
        if (!$empleados->isEmpty()) {
            foreach ($empleados as $empleado) {
                // Obtener cargas del día donde participó este empleado
                $cargasDelEmpleado = Carga::whereDate('fecha_carga', $this->fecha)
                    ->whereHas('empleados', function($q) use ($empleado) {
                        $q->where('empleados.id_empleado', $empleado->id_empleado);
                    })
                    ->with('empleados')
                    ->get();

                // Usar el trait para calcular costo
                $costoEmpleado = $empleado->calcularCostoDia(
                    $this->fecha,
                    $this->es_dia_caido,
                    $cargasDelEmpleado
                );

                $costoManoObra += $costoEmpleado;
            }
        }

        // ===== B. COSTO INSUMOS =====
        // Buscar movimientos de stock tipo 'salida' vinculados a este parte
        $movimientos = MovimientoStock::where('tipo', 'salida')
            ->whereDate('fecha', $this->fecha)
            ->where('motivo', 'LIKE', 'Parte Diario #' . $this->id_parte_diario . '%')
            ->get();

        foreach ($movimientos as $mov) {
            // Si tiene costo_total_movimiento (FIFO), usarlo; si no, calcular
            if ($mov->costo_total_movimiento) {
                $costoInsumos += (float) $mov->costo_total_movimiento;
            } else {
                $costoInsumos += (float) ($mov->cantidad * ($mov->precio_unitario ?? 0));
            }
        }

        // ===== C. COSTO MAQUINARIA (Alquiler + Mantenimientos) =====
        // C1. Calcular costo de alquiler por destajo
        $cargasDelParte = $this->cargas()->with('maquinarias')->get();
        
        // Calcular total de toneladas del parte
        foreach ($cargasDelParte as $carga) {
            // peso_neto está en kg, convertir a toneladas
            $totalToneladas += ($carga->peso_neto / 1000);
        }

        // Obtener maquinarias únicas usadas en este parte
        $maquinariasUsadas = collect();
        foreach ($cargasDelParte as $carga) {
            foreach ($carga->maquinarias as $maq) {
                if (!$maquinariasUsadas->contains('id_maquinaria', $maq->id_maquinaria)) {
                    $maquinariasUsadas->push($maq);
                }
            }
        }

        // Calcular costo de alquiler por destajo
        // Nota: Asumimos que TipoMaquinaria tiene precio_alquiler_destajo
        foreach ($maquinariasUsadas as $maq) {
            if ($maq->es_alquilada && $maq->tipoMaquinaria) {
                $precioAlquilerPorTon = (float) ($maq->tipoMaquinaria->precio_alquiler_destajo ?? 0);
                $costoMaquinaria += $totalToneladas * $precioAlquilerPorTon;
            }
        }

        // C2. Sumar mantenimientos completados en esta fecha
        $idsMaquinarias = $maquinariasUsadas->pluck('id_maquinaria')->toArray();
        
        if (!empty($idsMaquinarias)) {
            $mantenimientos = Mantenimiento::whereIn('id_maquinaria', $idsMaquinarias)
                ->where('estado', 'completado')
                ->whereNotNull('fecha_fin')
                ->whereDate('fecha_fin', $this->fecha)
                ->get();

            foreach ($mantenimientos as $mant) {
                $costoMaquinaria += (float) ($mant->costo_total ?? 0);
            }
        }

        // ===== D. TOTALES Y GUARDADO =====
        $costoTotalDia = $costoManoObra + $costoInsumos + $costoMaquinaria;
        
        // Calcular costo unitario (por tonelada)
        $costoUnitario = null;
        if (!$this->es_dia_caido && $totalToneladas > 0) {
            $costoUnitario = round($costoTotalDia / $totalToneladas, 2);
        }

        // Actualizar el parte sin disparar eventos (updateQuietly)
        $this->updateQuietly([
            'costo_mano_obra' => round($costoManoObra, 2),
            'costo_insumos' => round($costoInsumos, 2),
            'costo_maquinaria' => round($costoMaquinaria, 2),
            'costo_total_dia' => round($costoTotalDia, 2),
            'costo_unitario_calculado' => $costoUnitario
        ]);
    }

    protected static function booted()
    {
        static::saving(function (self $model) {
            if ($model->fecha && Carbon::parse($model->fecha)->isAfter(Carbon::today())) {
                throw new \InvalidArgumentException('La fecha del parte no puede ser futura.');
            }
        });
    }
}
