<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Empleado extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'empleados';
    protected $primaryKey = 'id_empleado';
    protected $fillable = [
        'id_rol_laboral',
        'dni',
        'apellido',
        'nombre',
        'fecha_nacimiento',
        'fecha_inicio_actividades',
        'fecha_fin_actividades'
    ];

    public function rolLaboral()
    {
        return $this->belongsTo(RolLaboral::class, 'id_rol_laboral', 'id_rol_laboral');
    }

    public function adelantos()
    {
        return $this->hasMany(Adelanto::class, 'id_empleado', 'id_empleado');
    }

    public function cargas()
    {
        return $this->belongsToMany(Carga::class, 'carga_empleado', 'id_empleado', 'id_carga')->withTimestamps();
    }

    public function partesDiarios()
    {
        return $this->belongsToMany(ParteDiario::class, 'parte_diario_empleado', 'id_empleado', 'id_parte_diario')->withTimestamps();
    }

    public function recibos()
    {
        return $this->hasMany(Recibo::class, 'id_empleado', 'id_empleado');
    }

    /**
     * Calcular pagos de un empleado en un rango de fechas.
     *
     * Lógica CORRECTA (desde ParteDiario y Carga directamente):
     * - Si ParteDiario->es_dia_caido == true → Contar 1 día de jornal
     * - Si ParteDiario->es_dia_caido == false → Buscar Cargas de ese día asignadas a este empleado
     *   y sumar su peso_neto dividido por la cantidad de empleados asignados a cada carga
     *
     * @param  string|\DateTimeInterface  $fechaInicio
     * @param  string|\DateTimeInterface  $fechaFin
     * @return array {
     *     @type int cantidad_dias_caidos
     *     @type float total_peso_neto (toneladas asignadas al empleado)
     *     @type float valor_jornal
     *     @type float tarifa_fija_por_tonelada
     *     @type float total_pagar_jornales
     *     @type float total_pagar_produccion
     *     @type float total_pagar_final
     * }
     */
    public function calcularPagoRango($fechaInicio, $fechaFin)
    {
        $cantidad_dias_caidos = 0;
        $total_peso_neto = 0.0;

        // Obtener valores de jornal y tarifa desde el histórico de rol laboral (vigente en fechaFin)
        $valorJornal = 0;
        $tarifaFija = 0;

        $rolId = null;
        if ($this->relationLoaded('rolLaboral') || $this->rolLaboral) {
            $rolId = $this->rolLaboral->id_rol_laboral ?? $this->id_rol_laboral ?? null;
        } else {
            $rolId = $this->id_rol_laboral ?? null;
        }

        if ($rolId) {
            $hist = \App\Models\HistoricoRolLaboral::where('rol_laboral_id', $rolId)
                ->whereDate('fecha_inicio', '<=', $fechaFin)
                ->where(function ($q) use ($fechaFin) {
                    $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $fechaFin);
                })
                ->orderBy('fecha_inicio', 'desc')
                ->first();

            if (!$hist) {
                // intentar con fechaInicio si no hay match exacto en fechaFin
                $hist = \App\Models\HistoricoRolLaboral::where('rol_laboral_id', $rolId)
                    ->whereDate('fecha_inicio', '<=', $fechaInicio)
                    ->where(function ($q) use ($fechaInicio) {
                        $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $fechaInicio);
                    })
                    ->orderBy('fecha_inicio', 'desc')
                    ->first();
            }

            if ($hist) {
                $valorJornal = (float) ($hist->jornal_diario ?? 0);
                $tarifaFija = (float) ($hist->precio_tonelada ?? 0);
            } else {
                // fallback a campos actuales del rol si existen
                if ($this->rolLaboral) {
                    $valorJornal = (float) ($this->rolLaboral->jornal_diario ?? $this->rolLaboral->costo_diario ?? 0);
                    $tarifaFija = (float) ($this->rolLaboral->precio_tonelada ?? 0);
                }
            }
        }

        // Obtener Partes en el rango de fechas
        $partes = \App\Models\ParteDiario::whereBetween('fecha', [$fechaInicio, $fechaFin])->get();

        foreach ($partes as $parte) {
            if ($parte->es_dia_caido) {
                // DÍA CAÍDO: Verificar si este empleado trabajó ese día (tabla pivote)
                $trabajoEseDia = \DB::table('parte_diario_empleado')
                    ->where('id_parte_diario', $parte->id_parte_diario)
                    ->where('id_empleado', $this->id_empleado)
                    ->exists();
                
                if ($trabajoEseDia) {
                    $cantidad_dias_caidos += 1;
                }
            } else {
                // PRODUCCIÓN: Buscar cargas del día asignadas a este empleado mediante tabla pivote
                $cargasDelDia = \App\Models\Carga::whereDate('fecha_carga', $parte->fecha)
                    ->whereHas('empleados', function($query) {
                        // Evitar ambigüedad de columna en el join con pivote
                        $query->where('empleados.id_empleado', $this->id_empleado);
                    })
                    ->with('empleados')
                    ->get();

                foreach ($cargasDelDia as $carga) {
                    // Dividir el peso_neto entre los empleados asignados
                    $cantidadEmpleados = $carga->empleados->count();
                    $pesoAsignado = $cantidadEmpleados > 0 ? $carga->peso_neto / $cantidadEmpleados : 0;
                    $total_peso_neto += $pesoAsignado;
                }
            }
        }

        // Cálculos finales
        // IMPORTANTE: peso_neto está en kilos, convertir a toneladas (dividir por 1000) y redondear a 2 decimales
        $total_peso_toneladas = round($total_peso_neto / 1000, 2);
        
        $total_pagar_jornales = $cantidad_dias_caidos * (float) $valorJornal;
        $total_pagar_produccion = $total_peso_toneladas * (float) $tarifaFija;
        $total_pagar_final = $total_pagar_jornales + $total_pagar_produccion;

        return [
            'cantidad_dias_caidos' => $cantidad_dias_caidos,
            'total_peso_neto' => round($total_peso_neto, 2), // en kilos
            'total_peso_toneladas' => round($total_peso_toneladas, 2), // en toneladas
            'valor_jornal' => (float) $valorJornal,
            'tarifa_fija_por_tonelada' => (float) $tarifaFija,
            'total_pagar_jornales' => round($total_pagar_jornales, 2),
            'total_pagar_produccion' => round($total_pagar_produccion, 2),
            'total_pagar_final' => round($total_pagar_final, 2),
        ];
    }
}
