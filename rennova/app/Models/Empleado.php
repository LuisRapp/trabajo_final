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

    /**
     * Calcular pagos de un empleado en un rango de fechas.
     *
     * Lógica:
     * - cantidad_dias_caidos: contar días en que existe un recibo de jornal para el empleado
     *   relacionado con un ParteDiario con es_dia_caido = true.
     * - total_peso_neto: sumar peso_neto de cada Carga en las fechas de los Partes en las que
     *   exista un recibo asociado al empleado que haga referencia a la carga (observaciones).
     *
     * @param  string|\DateTimeInterface  $fechaInicio
     * @param  string|\DateTimeInterface  $fechaFin
     * @return array {
     *     @type int cantidad_dias_caidos
     *     @type float total_peso_neto
     *     @type float valor_jornal
     *     @type float tarifa_fija_por_tonelada
     *     @type float total_pagar_jornales
     *     @type float total_pagar_produccion
     *     @type float total_pagar_final
     * }
     */
    public function calcularPagoRango($fechaInicio, $fechaFin)
    {
        // Importar modelos localmente para evitar dependencias en la cabecera
        $parteModel = \App\Models\ParteDiario::class;
        $cargaModel = \App\Models\Carga::class;
        $reciboModel = \App\Models\Recibo::class;

        $cantidad_dias_caidos = 0;
        $total_peso_neto = 0.0;

        $empleado = $this; // ya tenemos la instancia

        // Obtener valores de jornal y tarifa desde el rol laboral (fallbacks por compatibilidad)
        // Protecciones por si no existe rolLaboral relacionado
        if ($empleado->relationLoaded('rolLaboral') || $empleado->rolLaboral) {
            $valorJornal = $empleado->rolLaboral->jornal_diario ?? $empleado->rolLaboral->costo_diario ?? 0;
            $tarifaFija = $empleado->rolLaboral->precio_tonelada ?? $empleado->rolLaboral->tarifa_fija_tonelada ?? 0;
        } else {
            $valorJornal = 0;
            $tarifaFija = 0;
        }

        // Obtener Partes en el rango de fechas
        $partes = \App\Models\ParteDiario::whereBetween('fecha', [$fechaInicio, $fechaFin])->get();

        foreach ($partes as $parte) {
            // Si el parte es día caído, verificamos si existe un recibo de jornal para el empleado
            if ($parte->es_dia_caido) {
                $tieneReciboJornal = \App\Models\Recibo::where('id_empleado', $empleado->id_empleado)
                    ->whereDate('fecha_emision', $parte->fecha)
                    ->where('observaciones', 'ILIKE', '%Pago por jornal%')
                    ->exists();

                if ($tieneReciboJornal) {
                    $cantidad_dias_caidos += 1;
                }

                continue; // pasar al siguiente parte
            }

            // Modo producción: buscamos cargas del mismo día
            $cargasDelDia = \App\Models\Carga::whereDate('fecha_carga', $parte->fecha)->get();

            foreach ($cargasDelDia as $carga) {
                // Verificar si existe un recibo para este empleado y que haga referencia a esta carga
                // Observaciones al guardar usan el patrón: 'Pago por destajo - Parte Diario #<id> - Carga #<id> (...)'
                $referenciaCarga = sprintf('Carga #%d', $carga->id_carga);

                $tieneReciboPorCarga = \App\Models\Recibo::where('id_empleado', $empleado->id_empleado)
                    ->whereDate('fecha_emision', $parte->fecha)
                    ->where('observaciones', 'ILIKE', "%{$referenciaCarga}%")
                    ->exists();

                if ($tieneReciboPorCarga) {
                    $total_peso_neto += (float) ($carga->peso_neto ?? 0);
                }
            }
        }

        // Cálculos finales
        $total_pagar_jornales = $cantidad_dias_caidos * (float) $valorJornal;
        $total_pagar_produccion = $total_peso_neto * (float) $tarifaFija;
        $total_pagar_final = $total_pagar_jornales + $total_pagar_produccion;

        return [
            'cantidad_dias_caidos' => $cantidad_dias_caidos,
            'total_peso_neto' => $total_peso_neto,
            'valor_jornal' => (float) $valorJornal,
            'tarifa_fija_por_tonelada' => (float) $tarifaFija,
            'total_pagar_jornales' => $total_pagar_jornales,
            'total_pagar_produccion' => $total_pagar_produccion,
            'total_pagar_final' => $total_pagar_final,
        ];
    }
}
