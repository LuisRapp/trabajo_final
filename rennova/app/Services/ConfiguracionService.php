<?php

namespace App\Services;

use App\Models\ConfiguracionSistema;

class ConfiguracionService
{
    /**
     * Get a configuration value by its key.
     *
     * @param  string  $clave  The configuration key to look up
     * @param  mixed  $default  Value to return if the key does not exist
     * @return mixed The configuration value, or $default if not found
     */
    public static function obtener(string $clave, $default = null)
    {
        $config = ConfiguracionSistema::where('clave', $clave)->first();

        return $config ? $config->valor : $default;
    }

    /**
     * Create or update a configuration entry.
     *
     * @param  string  $clave  Unique configuration key
     * @param  mixed  $valor  The value to store
     * @param  string|null  $descripcion  Optional human-readable description
     * @param  string  $tipo  Value type hint (default: 'string')
     * @return \App\Models\ConfiguracionSistema The created or updated model
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
