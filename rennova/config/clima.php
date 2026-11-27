<?php

/**
 * Umbrales del analisis climatico. Se pueden ajustar por entorno sin tocar
 * codigo; los valores por defecto son los que ya usaba el sistema.
 */
return [
    // Lluvia: una jornada es no operativa si supera este acumulado diario y
    // llueve en la madrugada o durante la jornada.
    'umbral_lluvia_mm' => (float) env('CLIMA_UMBRAL_LLUVIA_MM', 10),
    'lluvia_madrugada_mm' => (float) env('CLIMA_LLUVIA_MADRUGADA_MM', 2),
    'lluvia_diurna_mm' => (float) env('CLIMA_LLUVIA_DIURNA_MM', 5),

    // Barro: saturacion del suelo, nubosidad y secado.
    'saturacion_mm' => (float) env('CLIMA_SATURACION_MM', 12),
    'umbral_nubosidad_pct' => (float) env('CLIMA_UMBRAL_NUBOSIDAD_PCT', 60),
    'secado_viento_kmh' => (float) env('CLIMA_SECADO_VIENTO_KMH', 15),
    'secado_et0_mm' => (float) env('CLIMA_SECADO_ET0_MM', 4),

    // Plan de recuperacion.
    'aumento_maximo_pct' => (float) env('CLIMA_AUMENTO_MAXIMO_PCT', 25),

    // Ventana de pronostico y sugerencia de mantenimiento.
    'forecast_dias' => (int) env('CLIMA_FORECAST_DIAS', 7),
    'mantenimiento_umbral_pct' => (float) env('CLIMA_MANTENIMIENTO_UMBRAL_PCT', 80),
];
