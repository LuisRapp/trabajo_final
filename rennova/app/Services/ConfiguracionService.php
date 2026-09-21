<?php

namespace App\Services;

use App\Models\ConfiguracionSistema;

class ConfiguracionService
{
    /**
     * Obtiene un valor de configuración por su clave.
     *
     * @param  string  $clave  Clave de configuración a buscar
     * @param  mixed  $default  Valor a devolver si la clave no existe
     * @return mixed Valor de configuración, o $default si no se encuentra
     */
    public static function obtener(string $clave, $default = null)
    {
        $config = ConfiguracionSistema::where('clave', $clave)->first();

        return $config ? $config->valor : $default;
    }

    /**
     * Crea o actualiza una entrada de configuración.
     *
     * @param  string  $clave  Clave única de configuración
     * @param  mixed  $valor  Valor a almacenar
     * @param  string|null  $descripcion  Descripción legible opcional
     * @param  string  $tipo  Pista de tipo de valor (por defecto: 'string')
     * @return \App\Models\ConfiguracionSistema Modelo creado o actualizado
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
