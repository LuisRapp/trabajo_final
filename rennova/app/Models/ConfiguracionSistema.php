<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionSistema extends Model
{
    protected $table = 'configuracion_sistema';
    
    protected $fillable = [
        'clave',
        'valor',
        'descripcion',
        'tipo'
    ];

    /**
     * Obtener valor de configuración por clave
     */
    public static function obtener($clave, $default = null)
    {
        $config = self::where('clave', $clave)->first();
        return $config ? $config->valor : $default;
    }

    /**
     * Actualizar o crear configuración
     */
    public static function establecer($clave, $valor, $descripcion = null, $tipo = 'string')
    {
        return self::updateOrCreate(
            ['clave' => $clave],
            [
                'valor' => $valor,
                'descripcion' => $descripcion,
                'tipo' => $tipo
            ]
        );
    }
}
