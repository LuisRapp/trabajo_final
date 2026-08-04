<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\AsignacionesLote;
use App\Models\Empleado;
use App\Models\Lote;
use App\Models\Maquinaria;
use App\Models\Usuario;
use App\Services\AsignacionLoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AsignacionesLoteTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = Usuario::factory()->create();
    }

    // =========================================================================
    // Render / Mount
    // =========================================================================

    public function test_component_renders_successfully(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->assertStatus(200);
    }

    public function test_mount_carga_lotes_empleados_maquinarias(): void
    {
        $lote = Lote::factory()->create();
        $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);
        $maquinaria = Maquinaria::factory()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class);

        $component->assertSet('lotes', function ($lotes) use ($lote) {
            return $lotes->contains('id_lote', $lote->id_lote);
        });

        $component->assertSet('empleados', function ($empleados) use ($empleado) {
            return $empleados->contains('id_empleado', $empleado->id_empleado);
        });

        $component->assertSet('maquinarias', function ($maquinarias) use ($maquinaria) {
            return $maquinarias->contains('id_maquinaria', $maquinaria->id_maquinaria);
        });
    }

    public function test_mount_excluye_empleados_inactivos(): void
    {
        $empleadoActivo = Empleado::factory()->create(['fecha_fin_actividades' => null]);
        $empleadoInactivo = Empleado::factory()->create(['fecha_fin_actividades' => now()->subMonth()]);

        $component = Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class);

        $component->assertSet('empleados', function ($empleados) use ($empleadoActivo, $empleadoInactivo) {
            return $empleados->contains('id_empleado', $empleadoActivo->id_empleado)
                && ! $empleados->contains('id_empleado', $empleadoInactivo->id_empleado);
        });
    }

    // =========================================================================
    // Guardar / Asignar
    // =========================================================================

    public function test_asignar_empleados_y_maquinarias_a_lote(): void
    {
        $lote = Lote::factory()->activo()->create();
        $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);
        $maquinaria = Maquinaria::factory()->create();

        $mock = \Mockery::mock(AsignacionLoteService::class);
        $mock->shouldReceive('asignarRecursos')
            ->once()
            ->withArgs(function ($loteId, $empIds, $maqIds, $request) use ($lote, $empleado, $maquinaria) {
                return $loteId === $lote->id_lote
                    && in_array($empleado->id_empleado, $empIds)
                    && in_array($maquinaria->id_maquinaria, $maqIds);
            })
            ->andReturn([]);

        $this->app->instance(AsignacionLoteService::class, $mock);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->set('id_lote', $lote->id_lote)
            ->set('empleados_seleccionados', [$empleado->id_empleado])
            ->set('maquinarias_seleccionadas', [$maquinaria->id_maquinaria])
            ->call('guardar')
            ->assertHasNoErrors();
    }

    public function test_sync_reemplaza_asignaciones_existentes(): void
    {
        $lote = Lote::factory()->activo()->create();
        $empleadoViejo = Empleado::factory()->create(['fecha_fin_actividades' => null]);
        $empleadoNuevo = Empleado::factory()->create(['fecha_fin_actividades' => null]);

        $lote->empleados()->attach($empleadoViejo->id_empleado);

        $mock = \Mockery::mock(AsignacionLoteService::class);
        $mock->shouldReceive('asignarRecursos')
            ->once()
            ->andReturn([]);

        $this->app->instance(AsignacionLoteService::class, $mock);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->set('id_lote', $lote->id_lote)
            ->set('empleados_seleccionados', [$empleadoNuevo->id_empleado])
            ->set('maquinarias_seleccionadas', [])
            ->call('guardar')
            ->assertHasNoErrors();
    }

    public function test_conflicto_empleado_en_otro_lote_activo_falla(): void
    {
        $loteActivo = Lote::factory()->activo()->create();
        $loteNuevo = Lote::factory()->activo()->create();
        $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);

        $loteActivo->empleados()->attach($empleado->id_empleado);

        $mock = \Mockery::mock(AsignacionLoteService::class);
        $mock->shouldNotReceive('asignarRecursos');
        $this->app->instance(AsignacionLoteService::class, $mock);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->set('id_lote', $loteNuevo->id_lote)
            ->set('empleados_seleccionados', [$empleado->id_empleado])
            ->set('maquinarias_seleccionadas', [])
            ->call('guardar')
            ->assertSet('guardando', false);
    }

    public function test_conflicto_maquinaria_en_otro_lote_activo_falla(): void
    {
        $loteActivo = Lote::factory()->activo()->create();
        $loteNuevo = Lote::factory()->activo()->create();
        $maquinaria = Maquinaria::factory()->create();

        $loteActivo->maquinarias()->attach($maquinaria->id_maquinaria);

        $mock = \Mockery::mock(AsignacionLoteService::class);
        $mock->shouldNotReceive('asignarRecursos');
        $this->app->instance(AsignacionLoteService::class, $mock);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->set('id_lote', $loteNuevo->id_lote)
            ->set('empleados_seleccionados', [])
            ->set('maquinarias_seleccionadas', [$maquinaria->id_maquinaria])
            ->call('guardar')
            ->assertSet('guardando', false);
    }

    public function test_conflicto_ambos_recursos_falla(): void
    {
        $loteActivo = Lote::factory()->activo()->create();
        $loteNuevo = Lote::factory()->activo()->create();
        $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);
        $maquinaria = Maquinaria::factory()->create();

        $loteActivo->empleados()->attach($empleado->id_empleado);
        $loteActivo->maquinarias()->attach($maquinaria->id_maquinaria);

        $mock = \Mockery::mock(AsignacionLoteService::class);
        $mock->shouldNotReceive('asignarRecursos');
        $this->app->instance(AsignacionLoteService::class, $mock);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->set('id_lote', $loteNuevo->id_lote)
            ->set('empleados_seleccionados', [$empleado->id_empleado])
            ->set('maquinarias_seleccionadas', [$maquinaria->id_maquinaria])
            ->call('guardar')
            ->assertSet('guardando', false);
    }

    // =========================================================================
    // Eliminar
    // =========================================================================

    public function test_eliminar_asignaciones_del_lote(): void
    {
        $lote = Lote::factory()->activo()->create();
        $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);
        $lote->empleados()->attach($empleado->id_empleado);

        $mock = \Mockery::mock(AsignacionLoteService::class);
        $mock->shouldReceive('eliminarAsignaciones')
            ->once()
            ->with($lote->id_lote, \Mockery::type('array'));

        $this->app->instance(AsignacionLoteService::class, $mock);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->call('eliminarAsignacion', $lote->id_lote);
    }

    public function test_eliminar_lote_sin_asignaciones_no_falla(): void
    {
        $lote = Lote::factory()->activo()->create();

        $mock = \Mockery::mock(AsignacionLoteService::class);
        $mock->shouldReceive('eliminarAsignaciones')
            ->once()
            ->with($lote->id_lote, \Mockery::type('array'));

        $this->app->instance(AsignacionLoteService::class, $mock);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->call('eliminarAsignacion', $lote->id_lote)
            ->assertStatus(200);
    }

    // =========================================================================
    // Liberar
    // =========================================================================

    public function test_liberar_recursos_cambia_estado_a_terminado(): void
    {
        $lote = Lote::factory()->activo()->create();
        $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);
        $lote->empleados()->attach($empleado->id_empleado);

        $mock = \Mockery::mock(AsignacionLoteService::class);
        $mock->shouldReceive('liberarRecursos')
            ->once()
            ->with($lote->id_lote, \Mockery::type('array'));

        $this->app->instance(AsignacionLoteService::class, $mock);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->call('liberar', $lote->id_lote);
    }

    public function test_liberar_lote_ya_terminado_no_falla(): void
    {
        $lote = Lote::factory()->cerrado()->create();

        $mock = \Mockery::mock(AsignacionLoteService::class);
        $mock->shouldReceive('liberarRecursos')
            ->once()
            ->with($lote->id_lote, \Mockery::type('array'));

        $this->app->instance(AsignacionLoteService::class, $mock);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->call('liberar', $lote->id_lote)
            ->assertStatus(200);
    }

    public function test_liberar_recarga_historial(): void
    {
        $lote = Lote::factory()->activo()->create();

        $mock = \Mockery::mock(AsignacionLoteService::class);
        $mock->shouldReceive('liberarRecursos')->once();

        $this->app->instance(AsignacionLoteService::class, $mock);

        $component = Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->call('liberar', $lote->id_lote);

        $component->assertStatus(200);
    }

    // =========================================================================
    // Cargar asignaciones
    // =========================================================================

    public function test_cargar_asignaciones_de_lote_existente(): void
    {
        $lote = Lote::factory()->activo()->create();
        $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);
        $maquinaria = Maquinaria::factory()->create();

        $lote->empleados()->attach($empleado->id_empleado);
        $lote->maquinarias()->attach($maquinaria->id_maquinaria);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->set('id_lote', $lote->id_lote)
            ->call('cargarAsignaciones')
            ->assertSet('empleados_seleccionados', [$empleado->id_empleado])
            ->assertSet('maquinarias_seleccionadas', [$maquinaria->id_maquinaria]);
    }

    public function test_cargar_lote_inexistente_no_rompe(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->set('id_lote', 99999)
            ->call('cargarAsignaciones')
            ->assertStatus(200)
            ->assertSet('empleados_seleccionados', [])
            ->assertSet('maquinarias_seleccionadas', []);
    }

    // =========================================================================
    // Editar
    // =========================================================================

    public function test_editar_asignacion_carga_datos(): void
    {
        $lote = Lote::factory()->activo()->create();
        $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);
        $lote->empleados()->attach($empleado->id_empleado);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->call('editarAsignacion', $lote->id_lote)
            ->assertSet('id_lote', $lote->id_lote)
            ->assertSet('empleados_seleccionados', [$empleado->id_empleado])
            ->assertSet('modo', 'editar')
            ->assertSet('mostrar_historial', false);
    }

    public function test_editar_cambia_modo_a_editar(): void
    {
        $lote = Lote::factory()->activo()->create();

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->assertSet('modo', 'nuevo')
            ->call('editarAsignacion', $lote->id_lote)
            ->assertSet('modo', 'editar');
    }

    // =========================================================================
    // Filtros
    // =========================================================================

    public function test_filtro_empleados_por_nombre(): void
    {
        $empleado1 = Empleado::factory()->create([
            'apellido' => 'Gonzalez',
            'nombre' => 'Juan',
            'fecha_fin_actividades' => null,
        ]);
        $empleado2 = Empleado::factory()->create([
            'apellido' => 'Perez',
            'nombre' => 'Maria',
            'fecha_fin_actividades' => null,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->set('busqueda_empleado', 'Gonzalez')
            ->assertSet('empleadosFiltrados', function ($filtrados) use ($empleado1, $empleado2) {
                return $filtrados->contains('id_empleado', $empleado1->id_empleado)
                    && ! $filtrados->contains('id_empleado', $empleado2->id_empleado);
            });
    }

    public function test_filtro_maquinarias_por_modelo(): void
    {
        $maq1 = Maquinaria::factory()->create(['modelo' => 'JD-1234-A']);
        $maq2 = Maquinaria::factory()->create(['modelo' => 'CAT-5678-B']);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->set('busqueda_maquinaria', 'JD')
            ->assertSet('maquinariasFiltrada', function ($filtradas) use ($maq1, $maq2) {
                return $filtradas->contains('id_maquinaria', $maq1->id_maquinaria)
                    && ! $filtradas->contains('id_maquinaria', $maq2->id_maquinaria);
            });
    }

    // =========================================================================
    // Edge cases
    // =========================================================================

    public function test_recurso_en_lote_no_activo_no_genera_conflicto(): void
    {
        $loteCerrado = Lote::factory()->cerrado()->create();
        $loteNuevo = Lote::factory()->activo()->create();
        $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);

        $loteCerrado->empleados()->attach($empleado->id_empleado);

        $mock = \Mockery::mock(AsignacionLoteService::class);
        $mock->shouldReceive('asignarRecursos')->once()->andReturn([]);

        $this->app->instance(AsignacionLoteService::class, $mock);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->set('id_lote', $loteNuevo->id_lote)
            ->set('empleados_seleccionados', [$empleado->id_empleado])
            ->set('maquinarias_seleccionadas', [])
            ->call('guardar')
            ->assertHasNoErrors();
    }

    public function test_recurso_en_mismo_lote_no_genera_conflicto(): void
    {
        $lote = Lote::factory()->activo()->create();
        $empleado = Empleado::factory()->create(['fecha_fin_actividades' => null]);

        $lote->empleados()->attach($empleado->id_empleado);

        $mock = \Mockery::mock(AsignacionLoteService::class);
        $mock->shouldReceive('asignarRecursos')->once()->andReturn([]);

        $this->app->instance(AsignacionLoteService::class, $mock);

        Livewire::actingAs($this->usuario)
            ->test(AsignacionesLote::class)
            ->set('id_lote', $lote->id_lote)
            ->set('empleados_seleccionados', [$empleado->id_empleado])
            ->set('maquinarias_seleccionadas', [])
            ->call('guardar')
            ->assertHasNoErrors();
    }
}
