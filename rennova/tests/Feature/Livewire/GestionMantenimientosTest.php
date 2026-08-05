<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\GestionMantenimientos;
use App\Models\Insumo;
use App\Models\KitMantenimientoPreventivo;
use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\TipoMantenimiento;
use App\Models\Usuario;
use App\Services\MantenimientoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GestionMantenimientosTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = Usuario::factory()->create();
    }

    private function mockService(): \Mockery\MockInterface
    {
        $mock = \Mockery::mock(MantenimientoService::class);
        $this->app->instance(MantenimientoService::class, $mock);

        return $mock;
    }

    // =========================================================================
    // Render / Mount
    // =========================================================================

    public function test_component_renders_successfully(): void
    {
        $this->mockService();

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->assertStatus(200);
    }

    public function test_mount_setea_rango_fechas_ultimo_mes(): void
    {
        $this->mockService();

        $component = Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class);

        $this->assertEquals(
            now()->subMonth()->format('Y-m-d'),
            $component->get('filtro_fecha_desde')
        );
        $this->assertEquals(
            now()->format('Y-m-d'),
            $component->get('filtro_fecha_hasta')
        );
    }

    public function test_mount_carga_datos_iniciales(): void
    {
        $this->mockService();

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->assertSet('tab_activo', 'ordenes')
            ->assertSet('filtro_estado', '')
            ->assertSet('filtro_maquinaria', '')
            ->assertSet('filtro_tipo', '')
            ->assertSet('modal_aprobar', false)
            ->assertSet('modal_completar', false)
            ->assertSet('modal_detalle', false);
    }

    // =========================================================================
    // Tabs y filtros
    // =========================================================================

    public function test_cambiar_tab_ordenes(): void
    {
        $this->mockService();

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->set('tab_activo', 'completadas')
            ->call('cambiarTab', 'ordenes')
            ->assertSet('tab_activo', 'ordenes');
    }

    public function test_cambiar_tab_completadas(): void
    {
        $this->mockService();

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('cambiarTab', 'completadas')
            ->assertSet('tab_activo', 'completadas');
    }

    public function test_resetear_filtros(): void
    {
        $this->mockService();

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->set('filtro_estado', 'programado')
            ->set('filtro_maquinaria', '5')
            ->set('filtro_tipo', 'preventivo')
            ->call('resetearFiltros')
            ->assertSet('filtro_estado', '')
            ->assertSet('filtro_maquinaria', '')
            ->assertSet('filtro_tipo', '');
    }

    public function test_filtro_por_estado(): void
    {
        $this->mockService();

        $maquinaria = Maquinaria::factory()->create();
        $tipo = TipoMantenimiento::factory()->create();

        Mantenimiento::factory()->programado()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);
        Mantenimiento::factory()->enCurso()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->set('filtro_estado', 'programado');

        $ordenes = $component->viewData('ordenes');
        $this->assertTrue($ordenes->every(fn ($o) => $o->estado === 'programado'));
    }

    // =========================================================================
    // Aprobar orden
    // =========================================================================

    public function test_aprobar_orden_con_stock_suficiente(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $tipo = TipoMantenimiento::factory()->create();
        $orden = Mantenimiento::factory()->programado()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $mock = $this->mockService();
        $mock->shouldReceive('verificarStockParaAprobacion')
            ->andReturn(['puede_aprobar' => true, 'insuficientes' => [], 'kit' => []]);

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('abrirModalAprobar', $orden->id_mantenimiento)
            ->call('aprobarOrden');

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $orden->id_mantenimiento,
            'estado' => 'en curso',
        ]);
    }

    public function test_aprobar_orden_con_stock_insuficiente_falla(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $tipo = TipoMantenimiento::factory()->create();
        $orden = Mantenimiento::factory()->programado()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $mock = $this->mockService();
        $mock->shouldReceive('verificarStockParaAprobacion')
            ->andReturn([
                'puede_aprobar' => false,
                'insuficientes' => [['nombre' => 'Aceite']],
                'kit' => [],
            ]);

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('abrirModalAprobar', $orden->id_mantenimiento)
            ->call('aprobarOrden');

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $orden->id_mantenimiento,
            'estado' => 'programado',
        ]);
    }

    public function test_aprobar_orden_inexistente_no_falla(): void
    {
        $mock = $this->mockService();
        $mock->shouldReceive('verificarStockParaAprobacion')
            ->andThrow(new \Exception('Not found'));

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('abrirModalAprobar', 99999)
            ->assertStatus(200);
    }

    public function test_doble_verificacion_stock_entre_modal_y_aprobar(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $tipo = TipoMantenimiento::factory()->create();
        $orden = Mantenimiento::factory()->programado()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $mock = $this->mockService();
        $mock->shouldReceive('verificarStockParaAprobacion')
            ->twice()
            ->andReturn(['puede_aprobar' => true, 'insuficientes' => [], 'kit' => []]);

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('abrirModalAprobar', $orden->id_mantenimiento)
            ->call('aprobarOrden');
    }

    public function test_aprobar_orden_ya_en_curso_no_falla(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $tipo = TipoMantenimiento::factory()->create();
        $orden = Mantenimiento::factory()->enCurso()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $mock = $this->mockService();
        $mock->shouldReceive('verificarStockParaAprobacion')
            ->andReturn(['puede_aprobar' => true, 'insuficientes' => [], 'kit' => []]);

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('abrirModalAprobar', $orden->id_mantenimiento)
            ->call('aprobarOrden')
            ->assertStatus(200);
    }

    // =========================================================================
    // Completar mantenimiento
    // =========================================================================

    public function test_completar_mantenimiento_con_insumos(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $tipo = TipoMantenimiento::factory()->create();
        $orden = Mantenimiento::factory()->enCurso()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $insumo = Insumo::factory()->create();

        $mock = $this->mockService();
        $mock->shouldReceive('completarMantenimiento')
            ->once()
            ->withArgs(function ($id, $insumos) use ($orden) {
                return $id === $orden->id_mantenimiento
                    && count($insumos) === 1
                    && $insumos[0]['id_insumo'] === 1
                    && $insumos[0]['cantidad_utilizada'] === 5;
            })
            ->andReturn(['success' => true, 'costo_total' => 500]);

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('abrirModalCompletar', $orden->id_mantenimiento)
            ->set('insumos_usados', [[
                'insumo_id' => $insumo->id_insumo,
                'cantidad' => 5,
                'nombre' => $insumo->nombre,
                'stock_disponible' => 100,
                'es_obligatorio' => false,
            ]])
            ->set('costo_mano_obra', 100)
            ->call('completarMantenimiento')
            ->assertHasNoErrors();
    }

    public function test_completar_mantenimiento_sin_insumos(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $tipo = TipoMantenimiento::factory()->create();
        $orden = Mantenimiento::factory()->enCurso()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $mock = $this->mockService();
        $mock->shouldReceive('completarMantenimiento')
            ->once()
            ->withArgs(function ($id, $insumos) use ($orden) {
                return $id === $orden->id_mantenimiento && empty($insumos);
            })
            ->andReturn(['success' => true, 'costo_total' => 100]);

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('abrirModalCompletar', $orden->id_mantenimiento)
            ->set('insumos_usados', [])
            ->set('costo_mano_obra', 100)
            ->call('completarMantenimiento')
            ->assertHasNoErrors();
    }

    public function test_completar_mantenimiento_preventivo_con_kit(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $tipo = TipoMantenimiento::factory()->create(['nombre' => 'Mantenimiento Preventivo']);
        $orden = Mantenimiento::factory()->enCurso()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $insumo = Insumo::factory()->create();
        KitMantenimientoPreventivo::factory()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_insumo' => $insumo->id_insumo,
            'cantidad_requerida' => 3,
            'es_obligatorio' => true,
        ]);

        $mock = $this->mockService();
        $mock->shouldReceive('obtenerKitPreventivo')
            ->once()
            ->with($maquinaria->id_tipo_maquinaria)
            ->andReturn(collect([(object) [
                'id_insumo' => $insumo->id_insumo,
                'cantidad_requerida' => 3,
                'es_obligatorio' => true,
                'insumo' => $insumo,
            ]]));

        $component = Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('abrirModalCompletar', $orden->id_mantenimiento);

        $component->assertSet('modal_completar', true);
        $component->assertSet('insumos_usados', function ($insumos) use ($insumo) {
            return count($insumos) === 1
                && $insumos[0]['insumo_id'] === $insumo->id_insumo
                && $insumos[0]['cantidad'] === 3
                && $insumos[0]['es_obligatorio'] === true;
        });
    }

    public function test_validacion_costo_mano_obra_minimo(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $tipo = TipoMantenimiento::factory()->create();
        $orden = Mantenimiento::factory()->enCurso()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $this->mockService();

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('abrirModalCompletar', $orden->id_mantenimiento)
            ->set('insumos_usados', [])
            ->set('costo_mano_obra', -5)
            ->call('completarMantenimiento')
            ->assertHasErrors(['costo_mano_obra' => 'min']);
    }

    public function test_validacion_insumo_requerido(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $tipo = TipoMantenimiento::factory()->create();
        $orden = Mantenimiento::factory()->enCurso()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $this->mockService();

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('abrirModalCompletar', $orden->id_mantenimiento)
            ->set('insumos_usados', [[
                'insumo_id' => '',
                'cantidad' => 5,
                'nombre' => '',
                'stock_disponible' => 0,
                'es_obligatorio' => false,
            ]])
            ->set('costo_mano_obra', 100)
            ->call('completarMantenimiento')
            ->assertHasErrors(['insumos_usados.0.insumo_id' => 'required']);
    }

    public function test_rollback_si_falla_completar(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $tipo = TipoMantenimiento::factory()->create();
        $orden = Mantenimiento::factory()->enCurso()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $mock = $this->mockService();
        $mock->shouldReceive('completarMantenimiento')
            ->once()
            ->andThrow(new \Exception('FIFO error'));

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('abrirModalCompletar', $orden->id_mantenimiento)
            ->set('insumos_usados', [])
            ->set('costo_mano_obra', 100)
            ->call('completarMantenimiento');

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $orden->id_mantenimiento,
            'estado' => 'en curso',
        ]);
    }

    // =========================================================================
    // Insumos usados
    // =========================================================================

    public function test_agregar_insumo_a_lista(): void
    {
        $this->mockService();

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->assertSet('insumos_usados', [])
            ->call('agregarInsumo')
            ->assertSet('insumos_usados', function ($insumos) {
                return count($insumos) === 1
                    && $insumos[0]['insumo_id'] === ''
                    && $insumos[0]['es_obligatorio'] === false;
            });
    }

    public function test_eliminar_insumo_no_obligatorio(): void
    {
        $this->mockService();

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('agregarInsumo')
            ->assertSet('insumos_usados', function ($insumos) {
                return count($insumos) === 1;
            })
            ->call('eliminarInsumo', 0)
            ->assertSet('insumos_usados', function ($insumos) {
                return count($insumos) === 0;
            });
    }

    public function test_eliminar_insumo_obligatorio_falla(): void
    {
        $this->mockService();

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->set('insumos_usados', [[
                'insumo_id' => '1',
                'cantidad' => 5,
                'nombre' => 'Aceite',
                'stock_disponible' => 100,
                'es_obligatorio' => true,
            ]])
            ->call('eliminarInsumo', 0)
            ->assertSet('insumos_usados', function ($insumos) {
                return count($insumos) === 1;
            });
    }

    // =========================================================================
    // Modal detalle
    // =========================================================================

    public function test_ver_detalle_orden(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $tipo = TipoMantenimiento::factory()->create();
        $orden = Mantenimiento::factory()->enCurso()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $this->mockService();

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('verDetalle', $orden->id_mantenimiento)
            ->assertSet('modal_detalle', true)
            ->assertSet('detalle_orden', function ($detalle) use ($orden) {
                return $detalle !== null && $detalle->id_mantenimiento === $orden->id_mantenimiento;
            });
    }

    public function test_detalle_orden_inexistente_no_falla(): void
    {
        $this->mockService();

        Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->call('verDetalle', 99999)
            ->assertStatus(200);
    }

    // =========================================================================
    // Computed
    // =========================================================================

    public function test_computed_ordenes_filtradas_por_tab(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $tipo = TipoMantenimiento::factory()->create();

        Mantenimiento::factory()->programado()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);
        Mantenimiento::factory()->completado()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $this->mockService();

        $component = Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class)
            ->set('tab_activo', 'ordenes');

        $ordenes = $component->viewData('ordenes');
        $this->assertTrue($ordenes->every(fn ($o) => in_array($o->estado, ['programado', 'en curso'])));
    }

    public function test_computed_maquinarias_solo_activas(): void
    {
        $maqActiva = Maquinaria::factory()->create(['estado' => 'activo']);
        $maqMantenimiento = Maquinaria::factory()->create(['estado' => 'mantenimiento']);

        $this->mockService();

        $component = Livewire::actingAs($this->usuario)
            ->test(GestionMantenimientos::class);

        $maquinarias = $component->viewData('maquinarias');
        $this->assertTrue($maquinarias->contains('id_maquinaria', $maqActiva->id_maquinaria));
        $this->assertFalse($maquinarias->contains('id_maquinaria', $maqMantenimiento->id_maquinaria));
    }
}
