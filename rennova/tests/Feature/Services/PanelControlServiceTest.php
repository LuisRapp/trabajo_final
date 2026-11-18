<?php

use App\Models\Mantenimiento;
use App\Services\PanelControlService;

it('cuenta mantenimientos pendientes como programado y en curso', function () {
    Mantenimiento::factory()->create(['estado' => 'programado']);
    Mantenimiento::factory()->create(['estado' => 'en curso']);
    Mantenimiento::factory()->create(['estado' => 'completado']);
    Mantenimiento::factory()->create(['estado' => 'vencido']);

    $metricas = app(PanelControlService::class)->obtenerMetricas();

    // Pendiente = programado + en curso (no completado ni vencido)
    expect($metricas['mantenimientosPendientes'])->toBe(2);
});
