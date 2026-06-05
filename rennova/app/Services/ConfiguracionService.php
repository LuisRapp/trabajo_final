<?php

namespace App\Services;

use App\Models\ConfiguracionSistema;

class ConfiguracionService
{
    /**
     * Obtener valor de configuración por clave
     */
    public static function obtener(string $clave, $default = null)
    {
        $config = ConfiguracionSistema::where('clave', $clave)->first();

        return $config ? $config->valor : $default;
    }

    /**
     * Actualizar o crear configuración
     */
    public static function establecer(string $clave, $valor, ?string $descripcion = null, string $tipo = 'string')
    {
        return ConfiguracionSistema::updateOrCreate(
            ['clave' => $clave],
            [
                'valor' => $valor,
                'descripcion' => $descripcion,
                'tipo' => $tipo,
            ]
        );
    }
}
