<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\ProgramarMantenimiento;
use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\NotificacionSistema;
use App\Models\TipoMantenimiento;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProgramarMantenimientoTest extends TestCase
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

    public function test_sin_notificacion_renders_formulario_de_alta(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(ProgramarMantenimiento::class);

        $component->assertStatus(200)
            ->assertSet('notificacionId', null);
    }

    public function test_sin_notificacion_carga_maquinarias_activas(): void
    {
        $activa = Maquinaria::factory()->create(['estado' => 'activo', 'modelo' => 'CAT-ALTA']);
        $baja = Maquinaria::factory()->create(['estado' => 'dado_de_baja', 'modelo' => 'VOL-BAJA']);

        $component = Livewire::actingAs($this->usuario)
            ->test(ProgramarMantenimiento::class);

        $maquinarias = $component->get('maquinarias');

        $this->assertTrue($maquinarias->contains('id_maquinaria', $activa->id_maquinaria));
        $this->assertFalse($maquinarias->contains('id_maquinaria', $baja->id_maquinaria));
    }

    public function test_sin_notificacion_carga_tipos_mantenimiento(): void
    {
        $tipo = TipoMantenimiento::factory()->preventivo()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(ProgramarMantenimiento::class);

        $tipos = $component->get('tipos');

        $this->assertTrue($tipos->contains('id_tipo_mantenimiento', $tipo->id_tipo_mantenimiento));
    }

    // =========================================================================
    // Alta: programar una nueva orden
    // =========================================================================

    public function test_programar_orden_crea_mantenimiento_programado(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();

        Livewire::actingAs($this->usuario)
            ->test(ProgramarMantenimiento::class)
            ->set('id_maquinaria', $maquinaria->id_maquinaria)
            ->set('id_tipo_mantenimiento', $tipo->id_tipo_mantenimiento)
            ->set('fechaProgramada', now()->addDays(2)->toDateString())
            ->call('programarOrden')
            ->assertHasNoErrors()
            ->assertRedirect(route('mantenimientos.index'));

        $this->assertDatabaseHas('mantenimientos', [
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
            'fecha_programada' => now()->addDays(2)->toDateString(),
            'fecha_inicio' => now()->addDays(2)->toDateString(),
            'estado' => 'programado',
        ]);
    }

    public function test_programar_orden_valida_campos_requeridos(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(ProgramarMantenimiento::class)
            ->set('id_maquinaria', null)
            ->set('id_tipo_mantenimiento', null)
            ->set('fechaProgramada', null)
            ->call('programarOrden')
            ->assertHasErrors([
                'id_maquinaria' => 'required',
                'id_tipo_mantenimiento' => 'required',
                'fechaProgramada' => 'required',
            ]);
    }

    public function test_programar_orden_valida_maquinaria_existente(): void
    {
        $tipo = TipoMantenimiento::factory()->preventivo()->create();

        Livewire::actingAs($this->usuario)
            ->test(ProgramarMantenimiento::class)
            ->set('id_maquinaria', 99999)
            ->set('id_tipo_mantenimiento', $tipo->id_tipo_mantenimiento)
            ->set('fechaProgramada', now()->addDays(2)->toDateString())
            ->call('programarOrden')
            ->assertHasErrors(['id_maquinaria' => 'exists']);
    }

    public function test_programar_orden_valida_tipo_mantenimiento_existente(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);

        Livewire::actingAs($this->usuario)
            ->test(ProgramarMantenimiento::class)
            ->set('id_maquinaria', $maquinaria->id_maquinaria)
            ->set('id_tipo_mantenimiento', 99999)
            ->set('fechaProgramada', now()->addDays(2)->toDateString())
            ->call('programarOrden')
            ->assertHasErrors(['id_tipo_mantenimiento' => 'exists']);
    }

    public function test_programar_orden_valida_fecha_dentro_de_7_dias(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();

        Livewire::actingAs($this->usuario)
            ->test(ProgramarMantenimiento::class)
            ->set('id_maquinaria', $maquinaria->id_maquinaria)
            ->set('id_tipo_mantenimiento', $tipo->id_tipo_mantenimiento)
            ->set('fechaProgramada', now()->addDays(10)->toDateString())
            ->call('programarOrden')
            ->assertHasErrors(['fechaProgramada']);
    }

    // =========================================================================
    // Detalle: confirmacion de orden generada por notificacion
    // =========================================================================

    public function test_con_notificacion_carga_detalle_del_mantenimiento(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo', 'modelo' => 'NOTIF-001']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();

        $this->be($this->usuario);

        $mantenimiento = Mantenimiento::factory()->programado()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $notificacion = NotificacionSistema::factory()->umbralAlcanzado()->noAccionada()->create([
            'user_id' => $this->usuario->id,
            'mantenimiento_id' => $mantenimiento->id_mantenimiento,
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(ProgramarMantenimiento::class, ['notificacionId' => $notificacion->id])
            ->assertSet('notificacionId', $notificacion->id);

        $mantenimientoCargado = $component->get('mantenimiento');
        $this->assertEquals($mantenimiento->id_mantenimiento, $mantenimientoCargado->id_mantenimiento);
    }

    public function test_confirmar_fecha_programa_mantenimiento_y_acciona_notificacion(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();

        $this->be($this->usuario);

        $mantenimiento = Mantenimiento::factory()->programado()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $notificacion = NotificacionSistema::factory()->umbralAlcanzado()->noAccionada()->create([
            'user_id' => $this->usuario->id,
            'mantenimiento_id' => $mantenimiento->id_mantenimiento,
        ]);

        $fecha = now()->addDays(2)->toDateString();

        Livewire::actingAs($this->usuario)
            ->test(ProgramarMantenimiento::class, ['notificacionId' => $notificacion->id])
            ->set('fechaProgramada', $fecha)
            ->call('guardarFecha')
            ->assertHasNoErrors()
            ->assertRedirect(route('mantenimientos.index'));

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'fecha_programada' => $fecha,
            'fecha_inicio' => $fecha,
            'estado' => 'programado',
        ]);

        $this->assertDatabaseHas('notificaciones_sistema', [
            'id' => $notificacion->id,
            'accionada' => true,
            'leida' => true,
        ]);
    }

    public function test_confirmar_fecha_fuera_del_rango_falla(): void
    {
        $maquinaria = Maquinaria::factory()->create(['estado' => 'activo']);
        $tipo = TipoMantenimiento::factory()->preventivo()->create();

        $this->be($this->usuario);

        $mantenimiento = Mantenimiento::factory()->programado()->create([
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipo->id_tipo_mantenimiento,
        ]);

        $notificacion = NotificacionSistema::factory()->umbralAlcanzado()->create([
            'user_id' => $this->usuario->id,
            'mantenimiento_id' => $mantenimiento->id_mantenimiento,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(ProgramarMantenimiento::class, ['notificacionId' => $notificacion->id])
            ->set('fechaProgramada', now()->addDays(30)->toDateString())
            ->call('guardarFecha')
            ->assertHasErrors(['fechaProgramada' => 'before_or_equal']);
    }
}
