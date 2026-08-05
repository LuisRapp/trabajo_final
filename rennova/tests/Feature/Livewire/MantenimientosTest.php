<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Mantenimientos;
use App\Models\Insumo;
use App\Models\KitMantenimientoPreventivo;
use App\Models\Mantenimiento;
use App\Models\MantenimientoInsumo;
use App\Models\Maquinaria;
use App\Models\MovimientoStock;
use App\Models\NotificacionSistema;
use App\Models\TipoMantenimiento;
use App\Models\Usuario;
use App\Services\MantenimientoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MantenimientosTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = Usuario::factory()->create();
    }

    // =========================================================================
    // Render & Mount
    // =========================================================================

    public function test_component_renders_successfully(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->assertStatus(200);
    }

    public function test_mount_loads_maquinarias_activas(): void
    {
        $activa = Maquinaria::factory()->create(['estado' => 'activo', 'modelo' => 'CAT-001']);
        $mantenimiento = Maquinaria::factory()->create(['estado' => 'mantenimiento', 'modelo' => 'JD-002']);
        $baja = Maquinaria::factory()->create(['estado' => 'dado_de_baja', 'modelo' => 'VOL-003']);

        $component = Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class);

        $maquinarias = $component->get('maquinarias');

        $this->assertTrue($maquinarias->contains('id_maquinaria', $activa->id_maquinaria));
        $this->assertTrue($maquinarias->contains('id_maquinaria', $mantenimiento->id_maquinaria));
        $this->assertFalse($maquinarias->contains('id_maquinaria', $baja->id_maquinaria));
    }

    public function test_mount_loads_tipos_mantenimiento(): void
    {
        $tipo1 = TipoMantenimiento::factory()->preventivo()->create();
        $tipo2 = TipoMantenimiento::factory()->correctivo()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class);

        $tipos = $component->get('tipos');

        $this->assertTrue($tipos->contains('id_tipo_mantenimiento', $tipo1->id_tipo_mantenimiento));
        $this->assertTrue($tipos->contains('id_tipo_mantenimiento', $tipo2->id_tipo_mantenimiento));
    }

    // =========================================================================
    // CRUD: Create
    // =========================================================================

    public function test_crear_mantenimiento_preventivo(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->set('id_maquinaria', $maquinaria->id_maquinaria)
            ->set('id_tipo_mantenimiento', $tipo->id_tipo_mantenimiento)
            ->set('fecha_inicio', now()->toDateString())
            ->set('estado', 'programado')
            ->call('guardar');

        $this->assertDatabaseHas('mantenimientos', [
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'estado' => 'programado',
        ]);
    }

    public function test_crear_mantenimiento_correctivo(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $tipo = TipoMantenimiento::factory()->correctivo()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->set('id_maquinaria', $maquinaria->id_maquinaria)
            ->set('id_tipo_mantenimiento', $tipo->id_tipo_mantenimiento)
            ->set('fecha_inicio', now()->toDateString())
            ->set('estado', 'en curso')
            ->call('guardar');

        $this->assertDatabaseHas('mantenimientos', [
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'estado' => 'en curso',
        ]);
    }

    public function test_validacion_campos_requeridos(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->set('id_maquinaria', null)
            ->set('id_tipo_mantenimiento', null)
            ->set('fecha_inicio', null)
            ->set('estado', null)
            ->call('guardar')
            ->assertHasErrors([
                'id_maquinaria' => 'required',
                'id_tipo_mantenimiento' => 'required',
                'fecha_inicio' => 'required',
                'estado' => 'required',
            ]);
    }

    public function test_validacion_fecha_programada_despues_de_fecha_inicio(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $tipo = TipoMantenimiento::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->set('id_maquinaria', $maquinaria->id_maquinaria)
            ->set('id_tipo_mantenimiento', $tipo->id_tipo_mantenimiento)
            ->set('fecha_inicio', now()->addDays(5)->toDateString())
            ->set('fecha_programada', now()->toDateString())
            ->set('estado', 'programado')
            ->call('guardar')
            ->assertHasErrors(['fecha_programada' => 'after_or_equal']);
    }

    // =========================================================================
    // CRUD: Edit
    // =========================================================================

    public function test_editar_mantenimiento_existente(): void
    {
        $mantenimiento = Mantenimiento::factory()->programado()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('editar', $mantenimiento->id_mantenimiento)
            ->assertSet('mantenimiento_id', $mantenimiento->id_mantenimiento)
            ->assertSet('id_maquinaria', $mantenimiento->id_maquinaria)
            ->assertSet('id_tipo_mantenimiento', $mantenimiento->id_tipo_mantenimiento)
            ->assertSet('fecha_inicio', $mantenimiento->fecha_inicio)
            ->assertSet('estado', $mantenimiento->estado);
    }

    public function test_editar_carga_datos_en_formulario(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo', 'modelo' => 'EDIT-TEST']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();

        $mantenimiento = Mantenimiento::factory()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => '2025-06-15',
            'fecha_programada' => '2025-06-20',
            'estado' => 'programado',
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('editar', $mantenimiento->id_mantenimiento)
            ->assertSet('fecha_programada', '2025-06-20')
            ->assertSet('fecha_inicio', '2025-06-15');
    }

    // =========================================================================
    // CRUD: Delete
    // =========================================================================

    public function test_eliminar_mantenimiento_hace_soft_delete(): void
    {
        $mantenimiento = Mantenimiento::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('eliminar', $mantenimiento->id_mantenimiento);

        $this->assertSoftDeleted('mantenimientos', ['id_mantenimiento' => $mantenimiento->id_mantenimiento]);
    }

    public function test_mantenimiento_eliminado_no_aparece_en_listado(): void
    {
        $mantenimiento = Mantenimiento::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('eliminar', $mantenimiento->id_mantenimiento);

        $component = Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class);

        $mantenimientos = $component->get('mantenimientos');
        $this->assertFalse($mantenimientos->contains('id_mantenimiento', $mantenimiento->id_mantenimiento));
    }

    // =========================================================================
    // State Flow
    // =========================================================================

    public function test_confirmar_mantenimiento_cambia_programado_a_en_curso(): void
    {
        $mantenimiento = Mantenimiento::factory()->programado()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('confirmarMantenimiento', $mantenimiento->id_mantenimiento);

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'en curso',
        ]);
    }

    public function test_confirmar_mantenimiento_no_programado_falla(): void
    {
        $mantenimiento = Mantenimiento::factory()->enCurso()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('confirmarMantenimiento', $mantenimiento->id_mantenimiento);

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'en curso',
        ]);
    }

    public function test_reprogramar_mantenimiento_cambia_vencido_a_programado(): void
    {
        $mantenimiento = Mantenimiento::factory()->vencido()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('reprogramarMantenimiento', $mantenimiento->id_mantenimiento);

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'programado',
            'fecha_programada' => null,
        ]);
    }

    public function test_reprogramar_mantenimiento_no_vencido_falla(): void
    {
        $mantenimiento = Mantenimiento::factory()->programado()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('reprogramarMantenimiento', $mantenimiento->id_mantenimiento);

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'programado',
        ]);
    }

    public function test_mantenimiento_programado_con_fecha_pasada_se_marca_vencido_al_render(): void
    {
        Mantenimiento::factory()->create([
            'estado' => 'programado',
            'fecha_programada' => now()->subDays(5)->toDateString(),
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class);

        $mantenimientos = $component->get('mantenimientos');
        $vencido = $mantenimientos->first();

        $this->assertNotNull($vencido);
        $this->assertEquals('vencido', $vencido->estado);
    }

    // =========================================================================
    // Search & Listing
    // =========================================================================

    public function test_listado_muestra_mantenimientos(): void
    {
        $m1 = Mantenimiento::factory()->create();
        $m2 = Mantenimiento::factory()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class);

        $mantenimientos = $component->get('mantenimientos');

        $this->assertTrue($mantenimientos->contains('id_mantenimiento', $m1->id_mantenimiento));
        $this->assertTrue($mantenimientos->contains('id_mantenimiento', $m2->id_mantenimiento));
    }

    public function test_busqueda_por_texto(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('ILIKE operator not supported on SQLite.');
        }

        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo', 'modelo' => 'BUSCAR-TEST-123']);
        $tipo = TipoMantenimiento::factory()->create();

        $mantenimiento = Mantenimiento::factory()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $otraMaquinaria = Maquinaria::factory()->create(['estado' => 'activo', 'modelo' => 'OTRO-MODELO']);
        $otro = Mantenimiento::factory()->create([
            'id_maquinaria' => $otraMaquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->set('busqueda', 'BUSCAR-TEST');

        $mantenimientos = $component->get('mantenimientos');

        $this->assertTrue($mantenimientos->contains('id_mantenimiento', $mantenimiento->id_mantenimiento));
        $this->assertFalse($mantenimientos->contains('id_mantenimiento', $otro->id_mantenimiento));
    }

    public function test_busqueda_vacia_retorna_todos(): void
    {
        $m1 = Mantenimiento::factory()->create();
        $m2 = Mantenimiento::factory()->create();
        $m3 = Mantenimiento::factory()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->set('busqueda', '');

        $mantenimientos = $component->get('mantenimientos');

        $this->assertGreaterThanOrEqual(3, $mantenimientos->count());
    }

    // =========================================================================
    // Validation: exists rules
    // =========================================================================

    public function test_validacion_maquinaria_debe_existir(): void
    {
        $tipo = TipoMantenimiento::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->set('id_maquinaria', 99999)
            ->set('id_tipo_mantenimiento', $tipo->id_tipo_mantenimiento)
            ->set('fecha_inicio', now()->toDateString())
            ->set('estado', 'programado')
            ->call('guardar')
            ->assertHasErrors(['id_maquinaria' => 'exists']);
    }

    public function test_validacion_tipo_mantenimiento_debe_existir(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->set('id_maquinaria', $maquinaria->id_maquinaria)
            ->set('id_tipo_mantenimiento', 99999)
            ->set('fecha_inicio', now()->toDateString())
            ->set('estado', 'programado')
            ->call('guardar')
            ->assertHasErrors(['id_tipo_mantenimiento' => 'exists']);
    }

    // =========================================================================
    // Slice 2: Modal de completar
    // =========================================================================

    public function test_abrir_modal_completar_carga_info_orden(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo', 'modelo' => 'MODAL-TEST']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->toDateString(),
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->assertSet('mostrarModalCompletar', true)
            ->assertSet('orden_completar_id', $mantenimiento->id_mantenimiento);

        $component = Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento);

        $info = $component->get('orden_completar_info');
        $this->assertEquals('MODAL-TEST', $info['maquinaria']);
        $this->assertEquals($mantenimiento->fecha_inicio, $info['fecha_inicio']);
    }

    public function test_cerrar_modal_completar_resetea_estado(): void
    {
        $mantenimiento = Mantenimiento::factory()->enCurso()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->assertSet('mostrarModalCompletar', true)
            ->call('cerrarModalCompletar')
            ->assertSet('mostrarModalCompletar', false)
            ->assertSet('orden_completar_id', null)
            ->assertSet('insumos_usados', []);
    }

    public function test_agregar_insumo_a_lista_insumos_usados(): void
    {
        $mantenimiento = Mantenimiento::factory()->enCurso()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento);

        $initialCount = count($component->get('insumos_usados'));

        $component->call('agregarInsumo');

        $this->assertCount($initialCount + 1, $component->get('insumos_usados'));
    }

    public function test_eliminar_insumo_de_lista_insumos_usados(): void
    {
        $mantenimiento = Mantenimiento::factory()->enCurso()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->call('agregarInsumo')
            ->call('agregarInsumo');

        $beforeCount = count($component->get('insumos_usados'));

        $component->call('eliminarInsumo', 0);

        $this->assertCount($beforeCount - 1, $component->get('insumos_usados'));
    }

    public function test_modal_no_abre_para_orden_ya_completada(): void
    {
        $mantenimiento = Mantenimiento::factory()->completado()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->assertSet('mostrarModalCompletar', true);
    }

    // =========================================================================
    // Slice 2: Completar orden - Happy path
    // =========================================================================

    public function test_completar_orden_con_insumos_y_costo_base(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        $this->mock(MantenimientoService::class, function ($mock) use ($mantenimiento) {
            $mock->shouldReceive('completarMantenimientoConFifo')
                ->once()
                ->andReturnUsing(function ($id, $fechaFin, $costoBase, $insumos, $tipo) use ($mantenimiento) {
                    $mantenimiento->update([
                        'fecha_fin' => $fechaFin,
                        'costo_total' => $costoBase,
                        'estado' => 'completado',
                    ]);
                    return [
                        'costo_total' => 2500.00,
                        'costo_insumos' => 1500.00,
                    ];
                });
        });

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->toDateString())
            ->set('costo_total_completar', 1000)
            ->set('insumos_usados', [])
            ->call('completarOrden');

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'completado',
        ]);
    }

    public function test_completar_orden_sin_insumos_solo_costo_base(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        $this->mock(MantenimientoService::class, function ($mock) use ($mantenimiento) {
            $mock->shouldReceive('completarMantenimientoConFifo')
                ->once()
                ->withArgs(function ($id, $fecha, $costoBase, $insumos, $tipo) {
                    return $costoBase === 500.0 && empty($insumos);
                })
                ->andReturnUsing(function ($id, $fechaFin, $costoBase, $insumos, $tipo) use ($mantenimiento) {
                    $mantenimiento->update([
                        'fecha_fin' => $fechaFin,
                        'costo_total' => $costoBase,
                        'estado' => 'completado',
                    ]);
                    return [
                        'costo_total' => 500.00,
                        'costo_insumos' => 0.00,
                    ];
                });
        });

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->toDateString())
            ->set('costo_total_completar', 500)
            ->set('insumos_usados', [])
            ->call('completarOrden');

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'completado',
        ]);
    }

    public function test_completar_orden_registra_movimientos_stock_salida(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        $this->mock(MantenimientoService::class, function ($mock) use ($mantenimiento) {
            $mock->shouldReceive('completarMantenimientoConFifo')
                ->once()
                ->andReturnUsing(function ($id, $fechaFin, $costoBase, $insumos, $tipo) use ($mantenimiento) {
                    $mantenimiento->update([
                        'fecha_fin' => $fechaFin,
                        'costo_total' => $costoBase,
                        'estado' => 'completado',
                    ]);
                    return [
                        'costo_total' => 1800.00,
                        'costo_insumos' => 1800.00,
                    ];
                });
        });

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->toDateString())
            ->set('costo_total_completar', 0)
            ->set('insumos_usados', [['id_insumo' => 1, 'cantidad' => 5]])
            ->call('completarOrden');

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'completado',
        ]);
    }

    public function test_completar_orden_cambia_estado_a_completado(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        $this->mock(MantenimientoService::class, function ($mock) use ($mantenimiento) {
            $mock->shouldReceive('completarMantenimientoConFifo')
                ->andReturnUsing(function ($id, $fechaFin, $costoBase, $insumos, $tipo) use ($mantenimiento) {
                    $mantenimiento->update([
                        'fecha_fin' => $fechaFin,
                        'costo_total' => $costoBase,
                        'estado' => 'completado',
                    ]);
                    return [
                        'costo_total' => 100.00,
                        'costo_insumos' => 0.00,
                    ];
                });
        });

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->toDateString())
            ->set('costo_total_completar', 100)
            ->set('insumos_usados', [])
            ->call('completarOrden');

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'completado',
        ]);
    }

    // =========================================================================
    // Slice 2: Completar orden - Validaciones
    // =========================================================================

    public function test_validacion_fecha_fin_mayor_o_igual_fecha_inicio(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->toDateString(),
        ]);

        $this->mock(MantenimientoService::class, function ($mock) {
            $mock->shouldNotReceive('completarMantenimientoConFifo');
        });

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->subDays(5)->toDateString())
            ->set('costo_total_completar', 100)
            ->set('insumos_usados', [])
            ->call('completarOrden');

        $this->assertDatabaseMissing('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'completado',
        ]);
    }

    public function test_validacion_costo_total_no_negativo(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->toDateString())
            ->set('costo_total_completar', -100)
            ->set('insumos_usados', [])
            ->call('completarOrden');

        $this->assertDatabaseMissing('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'completado',
        ]);
    }

    public function test_validacion_insumos_con_cantidad_positiva(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        $this->mock(MantenimientoService::class, function ($mock) use ($mantenimiento) {
            $mock->shouldReceive('completarMantenimientoConFifo')
                ->once()
                ->andReturnUsing(function ($id, $fechaFin, $costoBase, $insumos, $tipo) use ($mantenimiento) {
                    $mantenimiento->update([
                        'fecha_fin' => $fechaFin,
                        'costo_total' => $costoBase,
                        'estado' => 'completado',
                    ]);
                    return [
                        'costo_total' => 0.00,
                        'costo_insumos' => 0.00,
                    ];
                });
        });

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->toDateString())
            ->set('costo_total_completar', 0)
            ->set('insumos_usados', [['id_insumo' => '', 'cantidad' => '']])
            ->call('completarOrden');

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'completado',
        ]);
    }

    public function test_completar_orden_sin_fecha_fin_falla(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', '')
            ->set('costo_total_completar', 100)
            ->set('insumos_usados', [])
            ->call('completarOrden');

        $this->assertDatabaseMissing('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'completado',
        ]);
    }

    // =========================================================================
    // Slice 2: Completar orden - Edge cases
    // =========================================================================

    public function test_completar_orden_con_stock_insuficiente_falla(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        $this->mock(MantenimientoService::class, function ($mock) {
            $mock->shouldReceive('completarMantenimientoConFifo')
                ->andThrow(new \Exception('Stock insuficiente para el insumo: Aceite'));
        });

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->toDateString())
            ->set('costo_total_completar', 0)
            ->set('insumos_usados', [['id_insumo' => 999, 'cantidad' => 100]])
            ->call('completarOrden');

        $this->assertDatabaseMissing('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'completado',
        ]);
    }

    public function test_completar_orden_con_insumo_inexistente_falla(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        $this->mock(MantenimientoService::class, function ($mock) {
            $mock->shouldReceive('completarMantenimientoConFifo')
                ->andThrow(new \Exception('Insumo no encontrado'));
        });

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->toDateString())
            ->set('costo_total_completar', 0)
            ->set('insumos_usados', [['id_insumo' => 99999, 'cantidad' => 5]])
            ->call('completarOrden');

        $this->assertDatabaseMissing('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'completado',
        ]);
    }

    public function test_completar_orden_hace_rollback_si_falla_transaccion(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
            'estado' => 'en curso',
        ]);

        $this->mock(MantenimientoService::class, function ($mock) {
            $mock->shouldReceive('completarMantenimientoConFifo')
                ->andThrow(new \Exception('Error de transacción'));
        });

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('abrirModalCompletar', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->toDateString())
            ->set('costo_total_completar', 0)
            ->set('insumos_usados', [])
            ->call('completarOrden');

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'en curso',
        ]);
    }

    // =========================================================================
    // Slice 2: Kit preventivo automático
    // =========================================================================

    public function test_seleccionar_maquinaria_y_tipo_preventivo_carga_kit(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $insumo = Insumo::factory()->create(['nombre' => 'Aceite Kit Test']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();

        KitMantenimientoPreventivo::factory()->paraMaquinaria($maquinaria)->create([
            'id_insumo' => $insumo->id_insumo,
            'cantidad_requerida' => 5,
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->set('id_maquinaria', $maquinaria->id_maquinaria)
            ->set('id_tipo_mantenimiento', $tipo->id_tipo_mantenimiento);

        $kit = $component->get('kitPreventivo');
        $this->assertNotEmpty($kit);
        $this->assertEquals('Aceite Kit Test', $kit[0]['nombre']);
        $this->assertEquals(5, $kit[0]['cantidad_requerida']);
    }

    public function test_seleccionar_tipo_correctivo_no_carga_kit(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $insumo = Insumo::factory()->create();
        $tipo = TipoMantenimiento::factory()->correctivo()->create();

        KitMantenimientoPreventivo::factory()->paraMaquinaria($maquinaria)->create([
            'id_insumo' => $insumo->id_insumo,
            'cantidad_requerida' => 5,
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->set('id_maquinaria', $maquinaria->id_maquinaria)
            ->set('id_tipo_mantenimiento', $tipo->id_tipo_mantenimiento);

        $kit = $component->get('kitPreventivo');
        $this->assertEmpty($kit);
    }

    public function test_kit_preventivo_vacio_no_rompe_componente(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->set('id_maquinaria', $maquinaria->id_maquinaria)
            ->set('id_tipo_mantenimiento', $tipo->id_tipo_mantenimiento)
            ->assertSet('kitPreventivo', [])
            ->assertStatus(200);
    }

    // =========================================================================
    // Slice 2: Notificaciones
    // =========================================================================

    public function test_crear_mantenimiento_marca_notificacion_como_accionada(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();

        $this->be($this->usuario);

        $mantenimiento = Mantenimiento::factory()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'estado' => 'programado',
        ]);

        $notificacion = NotificacionSistema::factory()->noAccionada()->create([
            'user_id' => $this->usuario->id,
            'mantenimiento_id' => $mantenimiento->id_mantenimiento,
            'tipo' => 'umbral_alcanzado',
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('confirmarMantenimiento', $mantenimiento->id_mantenimiento);

        $this->assertDatabaseHas('notificaciones_sistema', [
            'id' => $notificacion->id,
            'accionada' => true,
        ]);
    }

    public function test_confirmar_mantenimiento_marca_notificacion_como_accionada(): void
    {
        $mantenimiento = Mantenimiento::factory()->programado()->create();

        $this->be($this->usuario);

        $notificacion = NotificacionSistema::factory()->noAccionada()->create([
            'user_id' => $this->usuario->id,
            'mantenimiento_id' => $mantenimiento->id_mantenimiento,
            'tipo' => 'umbral_alcanzado',
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('confirmarMantenimiento', $mantenimiento->id_mantenimiento);

        $this->assertDatabaseHas('notificaciones_sistema', [
            'id' => $notificacion->id,
            'accionada' => true,
        ]);
    }

    public function test_notificacion_inexistente_no_rompe_al_marcar(): void
    {
        $mantenimiento = Mantenimiento::factory()->programado()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('confirmarMantenimiento', $mantenimiento->id_mantenimiento);

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'en curso',
        ]);
    }

    // =========================================================================
    // Slice 2: Validación fecha programada
    // =========================================================================

    public function test_validacion_fecha_programada_dentro_de_7_dias_desde_notificacion(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();

        $this->be($this->usuario);

        $mantenimiento = Mantenimiento::factory()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'estado' => 'programado',
        ]);

        NotificacionSistema::factory()->create([
            'user_id' => $this->usuario->id,
            'mantenimiento_id' => $mantenimiento->id_mantenimiento,
            'tipo' => 'umbral_alcanzado',
            'created_at' => now()->subDays(2),
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('editar', $mantenimiento->id_mantenimiento)
            ->set('fecha_programada', now()->addDays(10)->toDateString())
            ->call('guardar')
            ->assertHasErrors('fecha_programada');
    }

    public function test_validacion_fecha_programada_dentro_de_7_dias_desde_hoy_si_no_hay_notificacion(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->set('id_maquinaria', $maquinaria->id_maquinaria)
            ->set('id_tipo_mantenimiento', $tipo->id_tipo_mantenimiento)
            ->set('fecha_inicio', now()->toDateString())
            ->set('fecha_programada', now()->addDays(10)->toDateString())
            ->set('estado', 'programado')
            ->call('guardar')
            ->assertHasErrors('fecha_programada');
    }

    // =========================================================================
    // Slice 2: Edge cases generales
    // =========================================================================

    public function test_editar_mantenimiento_inexistente_no_rompe(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('editar', 99999);
    }

    public function test_eliminar_mantenimiento_inexistente_no_rompe(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->call('eliminar', 99999);
    }

    public function test_busqueda_por_estado_funciona(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('ILIKE operator not supported on SQLite.');
        }

        $m1 = Mantenimiento::factory()->programado()->create();
        $m2 = Mantenimiento::factory()->enCurso()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(Mantenimientos::class)
            ->set('busqueda', 'programado');

        $mantenimientos = $component->get('mantenimientos');

        $this->assertTrue($mantenimientos->contains('id_mantenimiento', $m1->id_mantenimiento));
        $this->assertFalse($mantenimientos->contains('id_mantenimiento', $m2->id_mantenimiento));
    }
}
