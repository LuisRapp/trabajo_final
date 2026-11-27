<?php

namespace App\Services;

/**
 * Parametros del motor climatico: valores por defecto en config/clima.php y
 * edicion en runtime desde la pantalla de configuracion (tabla configuracions).
 */
class ClimaConfiguracionService
{
    private const PREFIJO = 'clima_';

    /**
     * Claves editables, en el orden de la pantalla.
     *
     * @var array<int, string>
     */
    public const VALORES = [
        'umbral_lluvia_mm',
        'lluvia_madrugada_mm',
        'lluvia_diurna_mm',
        'saturacion_mm',
        'umbral_nubosidad_pct',
        'secado_viento_kmh',
        'secado_et0_mm',
        'aumento_maximo_pct',
        'forecast_dias',
        'mantenimiento_umbral_pct',
    ];

    /**
     * @var array<string, mixed>
     */
    private static array $cache = [];

    public function valor(string $clave, $default = null)
    {
        if (array_key_exists($clave, self::$cache)) {
            return self::$cache[$clave];
        }

        return self::$cache[$clave] = ConfiguracionService::obtener(
            self::PREFIJO.$clave,
            $default ?? config('clima.'.$clave)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function valores(): array
    {
        $valores = [];

        foreach (self::VALORES as $clave) {
            $valores[$clave] = $this->valor($clave);
        }

        return $valores;
    }

    /**
     * @param  array<string, mixed>  $valores
     */
    public function guardar(array $valores): void
    {
        foreach (self::VALORES as $clave) {
            if (! array_key_exists($clave, $valores)) {
                continue;
            }

            ConfiguracionService::establecer(
                self::PREFIJO.$clave,
                $valores[$clave],
                'Clima: '.$clave,
                'number'
            );
        }

        self::limpiarCache();
    }

    public function restaurar(): void
    {
        $this->guardar(config('clima'));
    }

    public static function limpiarCache(): void
    {
        self::$cache = [];
    }
}
