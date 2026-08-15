<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\JornalForm;
use App\Models\ClimaDiaLote;
use App\Models\Empleado;
use App\Models\HistoricoRolLaboral;
use App\Models\Lote;
use App\Models\RolLaboral;
use App\Models\Usuario;
use App\Services\ClimaOperativoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JornalFormTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $usuario;

    protected Empleado $empleado;

    protected Empleado $empleado2;

    protected Lote $lote;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = Usuario::factory()->create();

        $this->lote = Lote::create([
            'propietario' => 'Test Owner SA',
            'condicion_compra' => 'propio',
            'estado' => 'activo',
            'ubicacion' => 'Test Location',
            'especie' => 'Pino elliottii',
            'superficie' => 100.0,
            'latitud' => -30.0,
            'longitud' => -58.0,
        ]);

        $rolLaboral = RolLaboral::create([
            'nombre' => 'Operario',
            'costo_diario' => 5000.00,
        ]);

        HistoricoRolLaboral::create([
            'rol_laboral_id' => $rolLaboral->id_rol_laboral,
            'jornal_diario' => 1500.00,
            'precio_tonelada' => 100.00,
            'fecha_inicio' => now()->subYear()->toDateString(),
        ]);

        $this->empleado = Empleado::create([
            'id_rol_laboral' => $rolLaboral->id_rol_laboral,
            'dni' => '99.999.999',
            'apellido' => 'TestApellido',
            'nombre' => 'TestNombre',
            'fecha_inicio_actividades' => now()->subYear()->toDateString(),
        ]);

        $this->empleado2 = Empleado::create([
            'id_rol_laboral' => $rolLaboral->id_rol_laboral,
            'dni' => '88.888.888',
            'apellido' => 'OtroApellido',
            'nombre' => 'OtroNombre',
            'fecha_inicio_actividades' => now()->subYear()->toDateString(),
        ]);

        $this->lote->empleados()->attach($this->empleado->id_empleado);
        $this->lote->empleados()->attach($this->empleado2->id_empleado);
    }

    private function mockClimaOperativo(): void
    {
        $climaDia = new ClimaDiaLote([
            'id_lote' => $this->lote->id_lote,
            'fecha' => now()->toDateString(),
            'estado_operativo' => 'OPERATIVO',
            'estado_pronostico' => 'OPERATIVO',
            'razon' => 'Test reason',
            'fuente' => 'test',
            'razon_pronostico' => 'Test reason',
            'fuente_pronostico' => 'test',
        ]);

        $mock = \Mockery::mock(ClimaOperativoService::class);
        $mock->shouldReceive('obtenerEstadoDia')->andReturn($climaDia);
        $this->app->instance(ClimaOperativoService::class, $mock);
    }

    private function getEmpleadosFiltrados()
    {
        return Empleado::with('rolLaboral')
            ->whereNull('fecha_fin_actividades')
            ->orderBy('apellido')
            ->get();
    }

    // ================================================================
    // RENDERING
    // ================================================================

    public function test_component_renders_successfully(): void
    {
        $this->mockClimaOperativo();

        Livewire::actingAs($this->usuario)
            ->test(JornalForm::class, [
                'fecha' => Carbon::today()->toDateString(),
                'empleadosFiltrados' => $this->getEmpleadosFiltrados(),
                'es_dia_caido' => true,
            ])
            ->assertStatus(200)
            ->assertSee('Asignación de Jornales');
    }

    public function test_component_does_not_render_when_not_dia_caido(): void
    {
        $this->mockClimaOperativo();

        Livewire::actingAs($this->usuario)
            ->test(JornalForm::class, [
                'fecha' => Carbon::today()->toDateString(),
                'empleadosFiltrados' => $this->getEmpleadosFiltrados(),
                'es_dia_caido' => false,
            ])
            ->assertDontSee('Asignación de Jornales');
    }

    // ================================================================
    // AGREGAR JORNAL
    // ================================================================

    public function test_agregar_jornal_with_valid_data_dispatches_event(): void
    {
        $this->mockClimaOperativo();

        $component = Livewire::actingAs($this->usuario)
            ->test(JornalForm::class, [
                'fecha' => Carbon::today()->toDateString(),
                'empleadosFiltrados' => $this->getEmpleadosFiltrados(),
                'es_dia_caido' => true,
            ])
            ->set('jornal_id_empleado', $this->empleado->id_empleado)
            ->call('agregarJornal');

        $component->assertDispatched('jornalAgregado');

        $jornales = $component->get('jornales');
        $this->assertCount(1, $jornales);
        $this->assertEquals($this->empleado->id_empleado, $jornales[0]['id_empleado']);
        $this->assertEquals('TestApellido, TestNombre', $jornales[0]['nombre_completo']);
        $this->assertEquals(1500, $jornales[0]['jornal_diario']);
    }

    public function test_agregar_jornal_resets_form_after_adding(): void
    {
        $this->mockClimaOperativo();

        $component = Livewire::actingAs($this->usuario)
            ->test(JornalForm::class, [
                'fecha' => Carbon::today()->toDateString(),
                'empleadosFiltrados' => $this->getEmpleadosFiltrados(),
                'es_dia_caido' => true,
            ])
            ->set('jornal_id_empleado', $this->empleado->id_empleado)
            ->call('agregarJornal');

        $component->assertSet('jornal_id_empleado', null);
    }

    public function test_cannot_agregar_jornal_without_employee(): void
    {
        $this->mockClimaOperativo();

        Livewire::actingAs($this->usuario)
            ->test(JornalForm::class, [
                'fecha' => Carbon::today()->toDateString(),
                'empleadosFiltrados' => $this->getEmpleadosFiltrados(),
                'es_dia_caido' => true,
            ])
            ->set('jornal_id_empleado', null)
            ->call('agregarJornal')
            ->assertHasErrors(['jornal_id_empleado']);
    }

    public function test_cannot_agregar_jornal_with_invalid_employee(): void
    {
        $this->mockClimaOperativo();

        Livewire::actingAs($this->usuario)
            ->test(JornalForm::class, [
                'fecha' => Carbon::today()->toDateString(),
                'empleadosFiltrados' => $this->getEmpleadosFiltrados(),
                'es_dia_caido' => true,
            ])
            ->set('jornal_id_empleado', 99999)
            ->call('agregarJornal')
            ->assertHasErrors(['jornal_id_empleado']);
    }

    public function test_cannot_agregar_duplicate_jornal(): void
    {
        $this->mockClimaOperativo();

        $component = Livewire::actingAs($this->usuario)
            ->test(JornalForm::class, [
                'fecha' => Carbon::today()->toDateString(),
                'empleadosFiltrados' => $this->getEmpleadosFiltrados(),
                'es_dia_caido' => true,
            ])
            ->set('jornal_id_empleado', $this->empleado->id_empleado)
            ->call('agregarJornal');

        $this->assertCount(1, $component->get('jornales'));

        $component->set('jornal_id_empleado', $this->empleado->id_empleado);
        $component->call('agregarJornal');

        $this->assertCount(1, $component->get('jornales'));
    }

    public function test_agregar_jornal_with_historico_uses_vigente_value(): void
    {
        $this->mockClimaOperativo();

        $component = Livewire::actingAs($this->usuario)
            ->test(JornalForm::class, [
                'fecha' => Carbon::today()->toDateString(),
                'empleadosFiltrados' => $this->getEmpleadosFiltrados(),
                'es_dia_caido' => true,
            ])
            ->set('jornal_id_empleado', $this->empleado->id_empleado)
            ->call('agregarJornal');

        $jornales = $component->get('jornales');
        $this->assertEquals(1500, $jornales[0]['jornal_diario']);
    }

    // ================================================================
    // ELIMINAR JORNAL
    // ================================================================

    public function test_eliminar_jornal_removes_from_array_and_dispatches(): void
    {
        $this->mockClimaOperativo();

        $component = Livewire::actingAs($this->usuario)
            ->test(JornalForm::class, [
                'fecha' => Carbon::today()->toDateString(),
                'empleadosFiltrados' => $this->getEmpleadosFiltrados(),
                'es_dia_caido' => true,
            ])
            ->set('jornal_id_empleado', $this->empleado->id_empleado)
            ->call('agregarJornal')
            ->set('jornal_id_empleado', $this->empleado2->id_empleado)
            ->call('agregarJornal');

        $this->assertCount(2, $component->get('jornales'));

        $component->call('eliminarJornal', 0);

        $component->assertDispatched('jornalEliminado');

        $jornales = $component->get('jornales');
        $this->assertCount(1, $jornales);
        $this->assertEquals($this->empleado2->id_empleado, $jornales[0]['id_empleado']);
    }

    public function test_eliminar_jornal_reindexes_array(): void
    {
        $this->mockClimaOperativo();

        $component = Livewire::actingAs($this->usuario)
            ->test(JornalForm::class, [
                'fecha' => Carbon::today()->toDateString(),
                'empleadosFiltrados' => $this->getEmpleadosFiltrados(),
                'es_dia_caido' => true,
            ])
            ->set('jornal_id_empleado', $this->empleado->id_empleado)
            ->call('agregarJornal')
            ->set('jornal_id_empleado', $this->empleado2->id_empleado)
            ->call('agregarJornal');

        $component->call('eliminarJornal', 0);

        $jornales = $component->get('jornales');
        $this->assertArrayHasKey(0, $jornales);
        $this->assertArrayNotHasKey(1, $jornales);
    }

    // ================================================================
    // JORNAL POR EMPLEADO (computed from fecha)
    // ================================================================

    public function test_jornal_por_empleado_computed_from_fecha(): void
    {
        $this->mockClimaOperativo();

        $component = Livewire::actingAs($this->usuario)
            ->test(JornalForm::class, [
                'fecha' => Carbon::today()->toDateString(),
                'empleadosFiltrados' => $this->getEmpleadosFiltrados(),
                'es_dia_caido' => true,
            ]);

        $jornalPorEmpleado = $component->get('jornal_por_empleado');
        $this->assertArrayHasKey($this->empleado->id_empleado, $jornalPorEmpleado);
        $this->assertEquals(1500, $jornalPorEmpleado[$this->empleado->id_empleado]);
    }

    public function test_jornal_por_empleado_empty_when_no_fecha(): void
    {
        $this->mockClimaOperativo();

        $component = Livewire::actingAs($this->usuario)
            ->test(JornalForm::class, [
                'fecha' => null,
                'empleadosFiltrados' => $this->getEmpleadosFiltrados(),
                'es_dia_caido' => true,
            ]);

        $jornalPorEmpleado = $component->get('jornal_por_empleado');
        $this->assertEmpty($jornalPorEmpleado);
    }

    // ================================================================
    // INITIAL JORNALES (from parent)
    // ================================================================

    public function test_component_accepts_initial_jornales(): void
    {
        $this->mockClimaOperativo();

        $initialJornales = [
            [
                'id_empleado' => $this->empleado->id_empleado,
                'nombre_completo' => 'TestApellido, TestNombre',
                'rol' => 'Operario',
                'jornal_diario' => 1500,
                'observaciones' => null,
            ],
        ];

        $component = Livewire::actingAs($this->usuario)
            ->test(JornalForm::class, [
                'fecha' => Carbon::today()->toDateString(),
                'empleadosFiltrados' => $this->getEmpleadosFiltrados(),
                'es_dia_caido' => true,
                'jornales' => $initialJornales,
            ]);

        $jornales = $component->get('jornales');
        $this->assertCount(1, $jornales);
        $this->assertEquals($this->empleado->id_empleado, $jornales[0]['id_empleado']);
    }
}
