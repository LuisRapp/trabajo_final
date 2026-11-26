<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\GestionStock;
use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\MovimientoStock;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GestionStockTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = usuarioConPermisos(['crear-gestion-stock']);
    }

    // =========================================================================
    // Ajuste de salida (FIFO)
    // =========================================================================

    public function test_ajuste_de_salida_descuenta_por_fifo(): void
    {
        $insumo = Insumo::factory()->create();
        LoteInventario::create([
            'id_insumo' => $insumo->id_insumo,
            'cantidad_inicial' => 10,
            'cantidad_disponible' => 10,
            'precio_unitario' => 50,
            'costo_total' => 500,
            'fecha_compra' => now()->toDateString(),
            'agotado' => false,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->call('abrirModal')
            ->set('id_insumo', $insumo->id_insumo)
            ->set('tipo_movimiento', 'ajuste_salida')
            ->set('cantidad', 4)
            ->set('fecha_compra', now()->toDateString())
            ->call('guardar')
            ->assertHasNoErrors();

        expect(
            (float) LoteInventario::where('id_insumo', $insumo->id_insumo)->sum('cantidad_disponible')
        )->toBe(6.0);
    }

    public function test_una_devolucion_descuenta_stock(): void
    {
        $insumo = Insumo::factory()->create();
        LoteInventario::create([
            'id_insumo' => $insumo->id_insumo,
            'cantidad_inicial' => 10,
            'cantidad_disponible' => 10,
            'precio_unitario' => 50,
            'costo_total' => 500,
            'fecha_compra' => now()->toDateString(),
            'agotado' => false,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->call('abrirModal')
            ->set('id_insumo', $insumo->id_insumo)
            ->set('tipo_movimiento', 'devolucion')
            ->set('cantidad', 4)
            ->set('fecha_compra', now()->toDateString())
            ->call('guardar')
            ->assertHasNoErrors();

        expect(
            (float) LoteInventario::where('id_insumo', $insumo->id_insumo)->sum('cantidad_disponible')
        )->toBe(6.0);
    }

    public function test_la_pantalla_no_habla_de_fifo(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->assertDontSee('FIFO');
    }

    public function test_ajuste_de_salida_mayor_al_stock_se_rechaza_y_no_cambia_nada(): void
    {
        $insumo = Insumo::factory()->create();
        LoteInventario::create([
            'id_insumo' => $insumo->id_insumo,
            'cantidad_inicial' => 5,
            'cantidad_disponible' => 5,
            'precio_unitario' => 50,
            'costo_total' => 250,
            'fecha_compra' => now()->toDateString(),
            'agotado' => false,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->call('abrirModal')
            ->set('id_insumo', $insumo->id_insumo)
            ->set('tipo_movimiento', 'ajuste_salida')
            ->set('cantidad', 10)
            ->set('fecha_compra', now()->toDateString())
            ->call('guardar')
            ->assertSee('Stock insuficiente');

        expect(
            (float) LoteInventario::where('id_insumo', $insumo->id_insumo)->sum('cantidad_disponible')
        )->toBe(5.0);
    }

    // =========================================================================
    // Render / Mount
    // =========================================================================

    public function test_component_renders_successfully(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->assertStatus(200);
    }

    public function test_mount_setea_fechas_por_defecto(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(GestionStock::class);

        $this->assertEquals(
            now()->format('Y-m-d'),
            $component->get('fecha_compra')
        );
        $this->assertEquals(
            now()->subMonths(1)->format('Y-m-d'),
            $component->get('filtro_fecha_inicio')
        );
        $this->assertEquals(
            now()->format('Y-m-d'),
            $component->get('filtro_fecha_fin')
        );
    }

    // =========================================================================
    // Validaciones
    // =========================================================================

    public function test_validacion_campos_requeridos(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->call('abrirModal')
            ->set('id_insumo', '')
            ->set('cantidad', '')
            ->set('precio_unitario', '')
            ->set('fecha_compra', '')
            ->call('guardar')
            ->assertHasErrors(['id_insumo', 'cantidad', 'precio_unitario', 'fecha_compra']);
    }

    public function test_validacion_insumo_debe_existir(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->call('abrirModal')
            ->set('id_insumo', 99999)
            ->set('cantidad', 10)
            ->set('precio_unitario', 100)
            ->set('fecha_compra', now()->format('Y-m-d'))
            ->set('tipo_movimiento', 'compra')
            ->call('guardar')
            ->assertHasErrors(['id_insumo' => 'exists']);
    }

    public function test_validacion_cantidad_minima(): void
    {
        $insumo = Insumo::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->call('abrirModal')
            ->set('id_insumo', $insumo->id_insumo)
            ->set('cantidad', 0)
            ->set('precio_unitario', 100)
            ->set('fecha_compra', now()->format('Y-m-d'))
            ->set('tipo_movimiento', 'compra')
            ->call('guardar')
            ->assertHasErrors(['cantidad' => 'min']);
    }

    public function test_validacion_precio_minimo(): void
    {
        $insumo = Insumo::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->call('abrirModal')
            ->set('id_insumo', $insumo->id_insumo)
            ->set('cantidad', 10)
            ->set('precio_unitario', 0)
            ->set('fecha_compra', now()->format('Y-m-d'))
            ->set('tipo_movimiento', 'compra')
            ->call('guardar')
            ->assertHasErrors(['precio_unitario' => 'min']);
    }

    public function test_validacion_tipo_movimiento_valido(): void
    {
        $insumo = Insumo::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->call('abrirModal')
            ->set('id_insumo', $insumo->id_insumo)
            ->set('cantidad', 10)
            ->set('precio_unitario', 100)
            ->set('fecha_compra', now()->format('Y-m-d'))
            ->set('tipo_movimiento', 'invalido')
            ->call('guardar')
            ->assertHasErrors(['tipo_movimiento' => 'in']);
    }

    // =========================================================================
    // Guardar
    // =========================================================================

    public function test_guardar_entrada_exitosa(): void
    {
        $insumo = Insumo::factory()->create();
        $proveedor = Proveedor::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->call('abrirModal')
            ->set('id_insumo', $insumo->id_insumo)
            ->set('id_proveedor', $proveedor->id_proveedor)
            ->set('cantidad', 100)
            ->set('precio_unitario', 50)
            ->set('fecha_compra', now()->format('Y-m-d'))
            ->set('tipo_movimiento', 'compra')
            ->set('numero_factura', 'FA-12345')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('lotes_inventario', [
            'id_insumo' => $insumo->id_insumo,
            'cantidad_inicial' => 100,
            'precio_unitario' => 50,
        ]);
    }

    public function test_guardar_con_tipo_invalido_falla(): void
    {
        $insumo = Insumo::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->call('abrirModal')
            ->set('id_insumo', $insumo->id_insumo)
            ->set('cantidad', 10)
            ->set('precio_unitario', 100)
            ->set('fecha_compra', now()->format('Y-m-d'))
            ->set('tipo_movimiento', 'venta')
            ->call('guardar')
            ->assertHasErrors(['tipo_movimiento']);
    }

    public function test_guardar_con_cantidad_negativa_falla(): void
    {
        $insumo = Insumo::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->call('abrirModal')
            ->set('id_insumo', $insumo->id_insumo)
            ->set('cantidad', -5)
            ->set('precio_unitario', 100)
            ->set('fecha_compra', now()->format('Y-m-d'))
            ->set('tipo_movimiento', 'compra')
            ->call('guardar')
            ->assertHasErrors(['cantidad']);
    }

    // =========================================================================
    // Filtros
    // =========================================================================

    public function test_filtro_por_estado_disponibles(): void
    {
        $insumo = Insumo::factory()->create();
        LoteInventario::factory()->disponible()->create(['id_insumo' => $insumo->id_insumo]);
        LoteInventario::factory()->agotado()->create(['id_insumo' => $insumo->id_insumo]);

        $component = Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->set('filtro_estado', 'disponibles');

        $lotes = $component->viewData('lotes');
        $this->assertTrue($lotes->every(fn ($l) => ! $l->agotado));
    }

    public function test_filtro_por_estado_agotados(): void
    {
        $insumo = Insumo::factory()->create();
        LoteInventario::factory()->disponible()->create(['id_insumo' => $insumo->id_insumo]);
        LoteInventario::factory()->agotado()->create(['id_insumo' => $insumo->id_insumo]);

        $component = Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->set('filtro_estado', 'agotados');

        $lotes = $component->viewData('lotes');
        $this->assertTrue($lotes->every(fn ($l) => $l->agotado));
    }

    public function test_filtro_por_estado_todos(): void
    {
        $insumo = Insumo::factory()->create();
        LoteInventario::factory()->disponible()->create([
            'id_insumo' => $insumo->id_insumo,
            'fecha_compra' => now()->subDays(5),
        ]);
        LoteInventario::factory()->agotado()->create([
            'id_insumo' => $insumo->id_insumo,
            'fecha_compra' => now()->subDays(3),
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->set('filtro_estado', 'todos')
            ->call('$refresh');

        $lotes = $component->viewData('lotes');
        $this->assertGreaterThanOrEqual(2, $lotes->count());
    }

    public function test_filtro_por_insumo(): void
    {
        $insumo1 = Insumo::factory()->create();
        $insumo2 = Insumo::factory()->create();
        LoteInventario::factory()->create(['id_insumo' => $insumo1->id_insumo]);
        LoteInventario::factory()->create(['id_insumo' => $insumo2->id_insumo]);

        $component = Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->set('filtro_insumo', $insumo1->id_insumo)
            ->set('filtro_estado', 'todos');

        $lotes = $component->viewData('lotes');
        $this->assertTrue($lotes->every(fn ($l) => $l->id_insumo === $insumo1->id_insumo));
    }

    public function test_filtro_por_proveedor(): void
    {
        $proveedor1 = Proveedor::factory()->create();
        $proveedor2 = Proveedor::factory()->create();
        $insumo = Insumo::factory()->create();
        LoteInventario::factory()->create(['id_insumo' => $insumo->id_insumo, 'id_proveedor' => $proveedor1->id_proveedor]);
        LoteInventario::factory()->create(['id_insumo' => $insumo->id_insumo, 'id_proveedor' => $proveedor2->id_proveedor]);

        $component = Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->set('filtro_proveedor', $proveedor1->id_proveedor)
            ->set('filtro_estado', 'todos');

        $lotes = $component->viewData('lotes');
        $this->assertTrue($lotes->every(fn ($l) => $l->id_proveedor === $proveedor1->id_proveedor));
    }

    public function test_filtro_por_rango_fechas(): void
    {
        $insumo = Insumo::factory()->create();
        LoteInventario::factory()->create([
            'id_insumo' => $insumo->id_insumo,
            'fecha_compra' => now()->subDays(5)->format('Y-m-d'),
        ]);
        LoteInventario::factory()->create([
            'id_insumo' => $insumo->id_insumo,
            'fecha_compra' => now()->subDays(60)->format('Y-m-d'),
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->set('filtro_fecha_inicio', now()->subDays(7)->format('Y-m-d'))
            ->set('filtro_fecha_fin', now()->format('Y-m-d'))
            ->set('filtro_estado', 'todos');

        $lotes = $component->viewData('lotes');
        $this->assertTrue($lotes->every(fn ($l) => $l->fecha_compra->gte(now()->subDays(7))));
    }

    public function test_limpiar_filtros_resetea_valores(): void
    {
        $insumo = Insumo::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->set('filtro_insumo', $insumo->id_insumo)
            ->set('filtro_proveedor', '1')
            ->set('filtro_tipo', 'compra')
            ->set('filtro_estado', 'agotados')
            ->call('limpiarFiltros')
            ->assertSet('filtro_insumo', '')
            ->assertSet('filtro_proveedor', '')
            ->assertSet('filtro_tipo', '')
            ->assertSet('filtro_estado', 'disponibles');
    }

    // =========================================================================
    // Estadísticas
    // =========================================================================

    public function test_estadisticas_calcula_correctamente(): void
    {
        $insumo = Insumo::factory()->create();
        LoteInventario::factory()->create([
            'id_insumo' => $insumo->id_insumo,
            'cantidad_inicial' => 100,
            'cantidad_disponible' => 100,
            'precio_unitario' => 50,
            'agotado' => false,
        ]);
        LoteInventario::factory()->create([
            'id_insumo' => $insumo->id_insumo,
            'cantidad_inicial' => 200,
            'cantidad_disponible' => 200,
            'precio_unitario' => 25,
            'agotado' => false,
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(GestionStock::class);

        $estadisticas = $component->viewData('estadisticas');
        $this->assertEquals(2, $estadisticas['total_lotes']);
        $this->assertEquals(300, $estadisticas['stock_total']);
        $this->assertEquals(10000, $estadisticas['valor_inventario']);
    }

    public function test_lotes_proximos_a_agotar(): void
    {
        $insumo = Insumo::factory()->create();
        LoteInventario::factory()->proximoAgotar()->create(['id_insumo' => $insumo->id_insumo]);
        LoteInventario::factory()->disponible()->create([
            'id_insumo' => $insumo->id_insumo,
            'cantidad_inicial' => 100,
            'cantidad_disponible' => 80,
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(GestionStock::class);

        $estadisticas = $component->viewData('estadisticas');
        $this->assertEquals(1, $estadisticas['lotes_proximos_agotar']);
    }

    // =========================================================================
    // Detalle
    // =========================================================================

    public function test_ver_detalle_carga_lote_con_movimientos(): void
    {
        $insumo = Insumo::factory()->create();
        $lote = LoteInventario::factory()->create(['id_insumo' => $insumo->id_insumo]);
        MovimientoStock::factory()->create([
            'id_insumo' => $insumo->id_insumo,
            'id_lote_inventario' => $lote->id_lote_inventario,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->call('verDetalle', $lote->id_lote_inventario)
            ->assertSet('loteSeleccionado.id_lote_inventario', $lote->id_lote_inventario);
    }

    public function test_cerrar_detalle_limpia_estado(): void
    {
        $insumo = Insumo::factory()->create();
        $lote = LoteInventario::factory()->create(['id_insumo' => $insumo->id_insumo]);

        Livewire::actingAs($this->usuario)
            ->test(GestionStock::class)
            ->call('verDetalle', $lote->id_lote_inventario)
            ->call('cerrarDetalle')
            ->assertSet('loteSeleccionado', null);
    }

    // =========================================================================
    // Edge cases
    // =========================================================================

    public function test_exportar_reporte_redirige_a_la_descarga_con_los_filtros(): void
    {
        $usuario = usuarioConPermisos(['ver-gestion-stock', 'crear-gestion-stock']);

        $componente = Livewire::actingAs($usuario)->test(GestionStock::class);

        $esperado = route('reportes.stock', array_filter([
            'estado' => 'disponibles',
            'desde' => $componente->get('filtro_fecha_inicio') ?: null,
            'hasta' => $componente->get('filtro_fecha_fin') ?: null,
        ]));

        $componente->call('exportarReporte')->assertRedirect($esperado);
    }

    public function test_la_descarga_de_stock_devuelve_un_csv_con_los_lotes(): void
    {
        $usuario = usuarioConPermisos(['ver-gestion-stock']);
        $insumo = Insumo::factory()->create(['nombre' => 'Insumo Exportable']);

        LoteInventario::create([
            'id_insumo' => $insumo->id_insumo,
            'cantidad_inicial' => 10,
            'cantidad_disponible' => 10,
            'precio_unitario' => 5,
            'costo_total' => 50,
            'fecha_compra' => now()->toDateString(),
            'agotado' => false,
        ]);

        $respuesta = $this->actingAs($usuario)->get(route('reportes.stock'));

        $respuesta->assertOk();
        $this->assertStringContainsString('text/csv', $respuesta->headers->get('Content-Type'));
        $this->assertStringContainsString('stock-', $respuesta->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Insumo Exportable', $respuesta->streamedContent());
    }
}
