<?php

use App\Models\Carga;
use App\Models\Lote;
use App\Models\ParteDiario;
use App\Models\Usuario;
use App\Models\Venta;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = Usuario::factory()->create();
    $this->admin->assignRole('Administrador');
});

function sembrarLoteConProduccion(): Lote
{
    $lote = Lote::factory()->create(['superficie' => 100]);

    ParteDiario::factory()->create([
        'id_lote' => $lote->id_lote,
        'fecha' => now()->subDays(5),
        'es_dia_caido' => false,
        'costo_total_dia' => 1000,
    ]);

    $carga = Carga::factory()->create([
        'id_lote' => $lote->id_lote,
        'fecha_carga' => now()->subDays(5),
        'peso_neto' => 50000,
    ]);

    $venta = Venta::factory()->create([
        'fecha_emision' => now()->subDays(5),
        'monto' => 10000,
    ]);
    $venta->cargas()->attach($carga->id_carga, [
        'precio_unitario' => 200,
        'peso_toneladas' => 50,
        'subtotal' => 10000,
    ]);

    return $lote;
}

it('renderiza los KPIs de T3 y los indicadores nuevos en la vista', function () {
    sembrarLoteConProduccion();

    $this->actingAs($this->admin)
        ->get(route('reportes.estadisticas-forestales'))
        ->assertOk()
        // KPIs de T3
        ->assertSee('Ingresos Totales')
        ->assertSee('$10,000.00')
        ->assertSee('0.50 tn/ha')
        ->assertSee('90.00%')
        ->assertSee('Margen Bruto')
        // Indicadores nuevos
        ->assertSee('Prod. por Día Operativo')
        ->assertSee('50.00 tn/día')
        ->assertSee('Tendencia Mensual de Producción')
        ->assertSee('(parcial)');
});

it('renderiza los KPIs nuevos en la plantilla del PDF global', function () {
    $data = [
        'reporte_nombre' => 'Indicadores Financieros Globales',
        'tipo' => 'global',
        'periodo' => '01/01/2026 - 31/01/2026',
        'generado_por' => 'Admin',
        'fecha_hora' => '01/01/2026 00:00',
        'produccion_total' => 50.0,
        'rendimiento_promedio' => 0.5,
        'estadisticas_globales' => [
            'costo_promedio' => 20.0,
            'ingresos_totales' => 10000.0,
            'margen_bruto_pct' => 90.0,
            'tn_dia_operativo' => 50.0,
        ],
        'ingresos_periodo' => 10000.0,
        'gastos_periodo' => 1000.0,
        'lotes_estadisticas' => [],
        'tendencia_mensual' => [
            ['periodo' => '2026-09', 'label' => 'Sep 2026', 'toneladas' => 100.0, 'variacion_pct' => null, 'parcial' => false],
            ['periodo' => '2026-10', 'label' => 'Oct 2026', 'toneladas' => 50.0, 'variacion_pct' => -50.0, 'parcial' => false],
            ['periodo' => '2026-11', 'label' => 'Nov 2026', 'toneladas' => 20.0, 'variacion_pct' => null, 'parcial' => true],
        ],
    ];

    $html = view('reportes.pdf.estadisticas-forestales', $data)->render();

    expect($html)
        ->toContain('Ingresos Totales')
        ->toContain('Margen Bruto')
        ->toContain('90.00%')
        ->toContain('Prod. por Día Operativo')
        ->toContain('50.00 Tn/día')
        ->toContain('Tendencia Mensual de Producción')
        ->toContain('Nov 2026 (parcial)');
});

it('genera un PDF válido desde la ruta global', function () {
    Http::fake([
        'quickchart.io/*' => Http::response(
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='),
            200,
            ['Content-Type' => 'image/png']
        ),
    ]);

    sembrarLoteConProduccion();

    $response = $this->actingAs($this->admin)
        ->get(route('reportes.estadisticas-forestales.pdf'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    $content = $response->getContent();

    expect(substr($content, 0, 5))->toBe('%PDF-')
        ->and(strlen($content))->toBeGreaterThan(1000);
});
