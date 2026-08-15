<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CompletarOrdenModal;
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

class CompletarOrdenModalTest extends TestCase
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
            ->test(CompletarOrdenModal::class)
            ->assertStatus(200);
    }

    public function test_mount_inicializa_modal_cerrado(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class)
            ->assertSet('mostrarModal', false)
            ->assertSet('orden_completar_id', null)
            ->assertSet('orden_completar_info', [])
            ->assertSet('orden_es_correctivo', false)
            ->assertSet('insumos_usados', []);
    }

    // =========================================================================
    // Apertura de modal via evento
    // =========================================================================

    public function test_evento_abrir_completar_orden_carga_info(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo', 'modelo' => 'MODAL-CHILD']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->toDateString(),
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class)
            ->dispatch('abrirCompletarOrden', $mantenimiento->id_mantenimiento);

        $component->assertSet('mostrarModal', true)
            ->assertSet('orden_completar_id', $mantenimiento->id_mantenimiento);

        $info = $component->get('orden_completar_info');
        $this->assertEquals('MODAL-CHILD', $info['maquinaria']);
        $this->assertEquals($mantenimiento->fecha_inicio, $info['fecha_inicio']);
    }

    public function test_evento_abrir_orden_preventivo_carga_insumos_kit(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();
        $insumo = Insumo::factory()->create(['nombre' => 'Aceite Preventivo']);
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        KitMantenimientoPreventivo::factory()->paraMaquinaria($maquinaria)->create([
            'id_insumo' => $insumo->id_insumo,
            'cantidad_requerida' => 3,
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class)
            ->dispatch('abrirCompletarOrden', $mantenimiento->id_mantenimiento);

        $insumos = $component->get('insumos_usados');
        $this->assertCount(1, $insumos);
        $this->assertEquals($insumo->id_insumo, $insumos[0]['id_insumo']);
        $this->assertEquals(3, $insumos[0]['cantidad']);
    }

    public function test_evento_abrir_orden_correctivo_inicia_con_campo_vacio(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class)
            ->dispatch('abrirCompletarOrden', $mantenimiento->id_mantenimiento);

        $component->assertSet('orden_es_correctivo', true);

        $insumos = $component->get('insumos_usados');
        $this->assertCount(1, $insumos);
        $this->assertEquals('', $insumos[0]['id_insumo']);
    }

    public function test_evento_abrir_orden_preventivo_sin_kit_inicia_con_campo_vacio(): void
    {
        $tipo = TipoMantenimiento::factory()->preventivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class)
            ->dispatch('abrirCompletarOrden', $mantenimiento->id_mantenimiento);

        $component->assertSet('orden_es_correctivo', false);

        $insumos = $component->get('insumos_usados');
        $this->assertCount(1, $insumos);
        $this->assertEquals('', $insumos[0]['id_insumo']);
    }

    // =========================================================================
    // Cierre de modal
    // =========================================================================

    public function test_cerrar_modal_resetea_estado(): void
    {
        $mantenimiento = Mantenimiento::factory()->enCurso()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class)
            ->dispatch('abrirCompletarOrden', $mantenimiento->id_mantenimiento)
            ->assertSet('mostrarModal', true)
            ->call('cerrarModal')
            ->assertSet('mostrarModal', false)
            ->assertSet('orden_completar_id', null)
            ->assertSet('insumos_usados', []);
    }

    // =========================================================================
    // Agregar / Eliminar insumos
    // =========================================================================

    public function test_agregar_insumo_a_lista(): void
    {
        $mantenimiento = Mantenimiento::factory()->enCurso()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class)
            ->dispatch('abrirCompletarOrden', $mantenimiento->id_mantenimiento);

        $initialCount = count($component->get('insumos_usados'));

        $component->call('agregarInsumo');

        $this->assertCount($initialCount + 1, $component->get('insumos_usados'));
    }

    public function test_eliminar_insumo_de_lista(): void
    {
        $mantenimiento = Mantenimiento::factory()->enCurso()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class)
            ->dispatch('abrirCompletarOrden', $mantenimiento->id_mantenimiento)
            ->call('agregarInsumo')
            ->call('agregarInsumo');

        $beforeCount = count($component->get('insumos_usados'));

        $component->call('eliminarInsumo', 0);

        $this->assertCount($beforeCount - 1, $component->get('insumos_usados'));
    }

    // =========================================================================
    // Completar orden - Happy path
    // =========================================================================

    public function test_completar_orden_despacha_evento_orden_completada(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        $this->mock(MantenimientoService::class, function ($mock) {
            $mock->shouldReceive('completarMantenimientoConFifo')
                ->once()
                ->andReturn([
                    'costo_total' => 1500.00,
                    'costo_insumos' => 500.00,
                ]);
        });

        Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class)
            ->dispatch('abrirCompletarOrden', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->toDateString())
            ->set('costo_total_completar', 1000)
            ->set('insumos_usados', [])
            ->call('completarOrden')
            ->assertDispatched('ordenCompletada');
    }

    public function test_completar_orden_cierra_modal(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        $this->mock(MantenimientoService::class, function ($mock) {
            $mock->shouldReceive('completarMantenimientoConFifo')
                ->once()
                ->andReturn([
                    'costo_total' => 100.00,
                    'costo_insumos' => 0.00,
                ]);
        });

        Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class)
            ->dispatch('abrirCompletarOrden', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->toDateString())
            ->set('costo_total_completar', 100)
            ->set('insumos_usados', [])
            ->call('completarOrden')
            ->assertSet('mostrarModal', false);
    }

    // =========================================================================
    // Completar orden - Validaciones
    // =========================================================================

    public function test_validacion_fecha_fin_requerida(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class)
            ->dispatch('abrirCompletarOrden', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', '')
            ->set('costo_total_completar', 100)
            ->set('insumos_usados', [])
            ->call('completarOrden')
            ->assertHasErrors(['fecha_fin_completar']);
    }

    public function test_validacion_fecha_fin_no_anterior_a_fecha_inicio(): void
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
            ->test(CompletarOrdenModal::class)
            ->dispatch('abrirCompletarOrden', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->subDays(5)->toDateString())
            ->set('costo_total_completar', 100)
            ->set('insumos_usados', [])
            ->call('completarOrden')
            ->assertHasErrors(['fecha_fin_completar']);
    }

    public function test_validacion_costo_total_no_negativo(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class)
            ->dispatch('abrirCompletarOrden', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->toDateString())
            ->set('costo_total_completar', -100)
            ->set('insumos_usados', [])
            ->call('completarOrden')
            ->assertHasErrors(['costo_total_completar']);
    }

    // =========================================================================
    // Completar orden - Error handling
    // =========================================================================

    public function test_completar_orden_con_error_servicio_muestra_error(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        $this->mock(MantenimientoService::class, function ($mock) {
            $mock->shouldReceive('completarMantenimientoConFifo')
                ->andThrow(new \Exception('Stock insuficiente'));
        });

        Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class)
            ->dispatch('abrirCompletarOrden', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->toDateString())
            ->set('costo_total_completar', 0)
            ->set('insumos_usados', [['id_insumo' => 999, 'cantidad' => 100]])
            ->call('completarOrden');

        $this->assertDatabaseMissing('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'completado',
        ]);
    }

    public function test_completar_orden_no_despacha_evento_si_falla(): void
    {
        $tipo = TipoMantenimiento::factory()->correctivo()->create();
        $mantenimiento = Mantenimiento::factory()->enCurso()->create([
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_inicio' => now()->subDay()->toDateString(),
        ]);

        $this->mock(MantenimientoService::class, function ($mock) {
            $mock->shouldReceive('completarMantenimientoConFifo')
                ->andThrow(new \Exception('Error'));
        });

        Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class)
            ->dispatch('abrirCompletarOrden', $mantenimiento->id_mantenimiento)
            ->set('fecha_fin_completar', now()->toDateString())
            ->set('costo_total_completar', 0)
            ->set('insumos_usados', [])
            ->call('completarOrden')
            ->assertNotDispatched('ordenCompletada');
    }

    // =========================================================================
    // Insumos disponibles
    // =========================================================================

    public function test_mount_carga_insumos_disponibles(): void
    {
        $insumo1 = Insumo::factory()->create(['nombre' => 'Insumo Alpha']);
        $insumo2 = Insumo::factory()->create(['nombre' => 'Insumo Beta']);

        $component = Livewire::actingAs($this->usuario)
            ->test(CompletarOrdenModal::class);

        $insumos = $component->get('insumosDisponibles');
        $this->assertTrue($insumos->contains('id_insumo', $insumo1->id_insumo));
        $this->assertTrue($insumos->contains('id_insumo', $insumo2->id_insumo));
    }
}
