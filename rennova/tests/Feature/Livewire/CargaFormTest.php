<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\CargaForm;
use App\Models\CategoriaMadera;
use App\Models\Chofer;
use App\Models\Cliente;
use App\Models\ClimaDiaLote;
use App\Models\Empleado;
use App\Models\HistoricoRolLaboral;
use App\Models\Lote;
use App\Models\Maquinaria;
use App\Models\RolLaboral;
use App\Models\TipoMaquinaria;
use App\Models\Usuario;
use App\Services\ClimaOperativoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CargaFormTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $usuario;

    protected Lote $lote;

    protected Empleado $empleado;

    protected Maquinaria $maquinaria;

    protected CategoriaMadera $categoriaMadera;

    protected Chofer $chofer;

    protected Cliente $cliente;

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

        $this->lote->empleados()->attach($this->empleado->id_empleado);

        $this->categoriaMadera = CategoriaMadera::create([
            'nombre' => 'Madera Aserrable',
        ]);

        $this->cliente = Cliente::create([
            'razon_social' => 'Cliente Test SA',
            'cuit' => '30-99999999-9',
        ]);

        $this->chofer = Chofer::create([
            'id_cliente' => $this->cliente->id_cliente,
            'nombre' => 'Chofer',
            'apellido' => 'Test',
            'dni' => '88.888.888',
            'estado' => true,
        ]);

        $tipoMaquinaria = TipoMaquinaria::create([
            'nombre' => 'Harvester',
        ]);

        $this->maquinaria = Maquinaria::create([
            'id_tipo_maquinaria' => $tipoMaquinaria->id_tipo_maquinaria,
            'modelo' => 'JD-1234-H',
            'estado' => 'activo',
            'es_alquilada' => false,
            'fecha_inicio_actividades' => now()->subYear()->toDateString(),
            'toneladas_acumuladas' => 1000,
            'umbral_toneladas' => 10000,
        ]);

        $this->lote->maquinarias()->attach($this->maquinaria->id_maquinaria);
    }

    private function mockClimaOperativo(string $estado = 'OPERATIVO'): void
    {
        $climaDia = new ClimaDiaLote([
            'id_lote' => $this->lote->id_lote,
            'fecha' => now()->toDateString(),
            'estado_operativo' => $estado,
            'estado_pronostico' => $estado,
            'razon' => 'Test reason',
            'fuente' => 'test',
            'razon_pronostico' => 'Test reason',
            'fuente_pronostico' => 'test',
        ]);

        $mock = \Mockery::mock(ClimaOperativoService::class);
        $mock->shouldReceive('obtenerEstadoDia')
            ->andReturn($climaDia);
        $this->app->instance(ClimaOperativoService::class, $mock);
    }

    private function defaultProps(): array
    {
        return [
            'id_lote' => $this->lote->id_lote,
            'fecha' => now()->toDateString(),
            'empleados_asignados_ids' => [$this->empleado->id_empleado],
            'maquinarias_asignadas_ids' => [$this->maquinaria->id_maquinaria],
            'cargas' => [],
        ];
    }

    // ================================================================
    // COMPONENT RENDERING
    // ================================================================

    public function test_component_renders_successfully(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps())
            ->assertStatus(200);
    }

    // ================================================================
    // AGREGAR CARGA — Validation
    // ================================================================

    public function test_agregar_carga_with_valid_data_dispatches_event(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps())
            ->set('carga_id_categoria_madera', $this->categoriaMadera->id_categoria_madera)
            ->set('carga_ticket', 'TK-TEST-001')
            ->set('carga_peso_bruto', 25000)
            ->set('carga_tara', 8000)
            ->set('carga_peso_neto', 17000)
            ->set('carga_id_chofer', $this->chofer->id_chofer)
            ->set('carga_destino', $this->cliente->id_cliente)
            ->set('carga_empleados', [$this->empleado->id_empleado])
            ->set('carga_maquinarias', [$this->maquinaria->id_maquinaria]);

        $component->call('agregarCarga');

        $component->assertDispatched('cargaAgregada');
    }

    public function test_agregar_carga_resets_form_after_dispatch(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps())
            ->set('carga_id_categoria_madera', $this->categoriaMadera->id_categoria_madera)
            ->set('carga_ticket', 'TK-RESET')
            ->set('carga_peso_bruto', 20000)
            ->set('carga_tara', 5000)
            ->set('carga_peso_neto', 15000)
            ->set('carga_id_chofer', $this->chofer->id_chofer)
            ->set('carga_destino', $this->cliente->id_cliente)
            ->set('carga_empleados', [$this->empleado->id_empleado])
            ->set('carga_maquinarias', [$this->maquinaria->id_maquinaria]);

        $component->call('agregarCarga');

        $component->assertSet('carga_ticket', null)
            ->assertSet('carga_peso_bruto', null)
            ->assertSet('carga_tara', null)
            ->assertSet('carga_peso_neto', null)
            ->assertSet('carga_id_chofer', null)
            ->assertSet('carga_destino', null)
            ->assertSet('carga_empleados', [])
            ->assertSet('carga_maquinarias', []);
    }

    public function test_cannot_agregar_carga_without_ticket(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps())
            ->set('carga_id_categoria_madera', $this->categoriaMadera->id_categoria_madera)
            ->set('carga_peso_bruto', 20000)
            ->set('carga_tara', 5000)
            ->set('carga_peso_neto', 15000)
            ->set('carga_id_chofer', $this->chofer->id_chofer)
            ->set('carga_destino', $this->cliente->id_cliente)
            ->set('carga_empleados', [$this->empleado->id_empleado])
            ->set('carga_maquinarias', [$this->maquinaria->id_maquinaria])
            ->call('agregarCarga')
            ->assertHasErrors(['carga_ticket']);
    }

    public function test_cannot_agregar_carga_without_employees(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps())
            ->set('carga_id_categoria_madera', $this->categoriaMadera->id_categoria_madera)
            ->set('carga_ticket', 'TK-NOEMP')
            ->set('carga_peso_bruto', 20000)
            ->set('carga_tara', 5000)
            ->set('carga_peso_neto', 15000)
            ->set('carga_id_chofer', $this->chofer->id_chofer)
            ->set('carga_destino', $this->cliente->id_cliente)
            ->set('carga_empleados', [])
            ->set('carga_maquinarias', [$this->maquinaria->id_maquinaria])
            ->call('agregarCarga')
            ->assertHasErrors(['carga_empleados']);
    }

    public function test_cannot_agregar_carga_without_maquinarias(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps())
            ->set('carga_id_categoria_madera', $this->categoriaMadera->id_categoria_madera)
            ->set('carga_ticket', 'TK-NOMAQ')
            ->set('carga_peso_bruto', 20000)
            ->set('carga_tara', 5000)
            ->set('carga_peso_neto', 15000)
            ->set('carga_id_chofer', $this->chofer->id_chofer)
            ->set('carga_destino', $this->cliente->id_cliente)
            ->set('carga_empleados', [$this->empleado->id_empleado])
            ->set('carga_maquinarias', [])
            ->call('agregarCarga')
            ->assertHasErrors(['carga_maquinarias']);
    }

    public function test_cannot_agregar_carga_with_negative_peso_neto(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps())
            ->set('carga_id_categoria_madera', $this->categoriaMadera->id_categoria_madera)
            ->set('carga_ticket', 'TK-NEG')
            ->set('carga_peso_bruto', 5000)
            ->set('carga_tara', 8000)
            ->set('carga_peso_neto', -5)
            ->set('carga_id_chofer', $this->chofer->id_chofer)
            ->set('carga_destino', $this->cliente->id_cliente)
            ->set('carga_empleados', [$this->empleado->id_empleado])
            ->set('carga_maquinarias', [$this->maquinaria->id_maquinaria])
            ->call('agregarCarga')
            ->assertHasErrors(['carga_peso_neto']);
    }

    // ================================================================
    // ELIMINAR CARGA
    // ================================================================

    public function test_eliminar_carga_dispatches_event(): void
    {
        $cargas = [
            [
                'id_categoria_madera' => $this->categoriaMadera->id_categoria_madera,
                'ticket' => 'TK-DEL-1',
                'peso_bruto' => 25000,
                'tara' => 8000,
                'peso_neto' => 17000,
                'id_chofer' => $this->chofer->id_chofer,
                'destino' => $this->cliente->id_cliente,
                'destino_nombre' => $this->cliente->razon_social,
                'empleados' => [$this->empleado->id_empleado],
                'maquinarias' => [$this->maquinaria->id_maquinaria],
            ],
        ];

        $component = Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, array_merge($this->defaultProps(), ['cargas' => $cargas]))
            ->call('eliminarCarga', 0);

        $component->assertDispatched('cargaEliminada');
    }

    // ================================================================
    // PESO NETO AUTO-CALCULATION
    // ================================================================

    public function test_peso_neto_calculated_from_peso_bruto_minus_tara(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps());

        $component->set('carga_peso_bruto', 25000);
        $component->set('carga_tara', 8000);

        $component->assertSet('carga_peso_neto', 17000);
    }

    public function test_peso_neto_cleared_when_inputs_are_empty(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps());

        $component->set('carga_peso_bruto', 25000);
        $component->set('carga_tara', 8000);
        $component->assertSet('carga_peso_neto', 17000);

        $component->set('carga_peso_bruto', null);
        $component->assertSet('carga_peso_neto', null);
    }

    // ================================================================
    // SEARCHABLE MULTI-SELECTS
    // ================================================================

    public function test_choferes_filtrados_filters_by_busqueda(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps());

        // Without search — chofer should be visible
        $filtrados = $component->get('choferesFiltrados');
        $this->assertTrue($filtrados->contains('id_chofer', $this->chofer->id_chofer));

        // With matching search
        $component->set('busqueda_chofer', 'Test');
        $filtrados = $component->get('choferesFiltrados');
        $this->assertTrue($filtrados->contains('id_chofer', $this->chofer->id_chofer));

        // With non-matching search
        $component->set('busqueda_chofer', 'ZZZZNOEXISTE');
        $filtrados = $component->get('choferesFiltrados');
        $this->assertFalse($filtrados->contains('id_chofer', $this->chofer->id_chofer));
    }

    public function test_clientes_filtrados_filters_by_busqueda(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps());

        // Without search — cliente should be visible
        $filtrados = $component->get('clientesFiltrados');
        $this->assertTrue($filtrados->contains('id_cliente', $this->cliente->id_cliente));

        // With matching search
        $component->set('busqueda_cliente', 'Cliente Test');
        $filtrados = $component->get('clientesFiltrados');
        $this->assertTrue($filtrados->contains('id_cliente', $this->cliente->id_cliente));

        // With non-matching search
        $component->set('busqueda_cliente', 'ZZZZNOEXISTE');
        $filtrados = $component->get('clientesFiltrados');
        $this->assertFalse($filtrados->contains('id_cliente', $this->cliente->id_cliente));
    }

    public function test_empleados_carga_filtrados_filters_by_busqueda(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps());

        // Without search — employee should be visible (assigned to lote)
        $filtrados = $component->get('empleadosCargaFiltrados');
        $this->assertTrue($filtrados->contains('id_empleado', $this->empleado->id_empleado));

        // With matching search
        $component->set('busqueda_empleado', 'TestApe');
        $filtrados = $component->get('empleadosCargaFiltrados');
        $this->assertTrue($filtrados->contains('id_empleado', $this->empleado->id_empleado));

        // With non-matching search
        $component->set('busqueda_empleado', 'ZZZZNOEXISTE');
        $filtrados = $component->get('empleadosCargaFiltrados');
        $this->assertFalse($filtrados->contains('id_empleado', $this->empleado->id_empleado));
    }

    public function test_maquinarias_carga_filtrada_filters_by_busqueda(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps());

        // Without search — maquinaria should be visible (assigned to lote)
        $filtradas = $component->get('maquinariasCargaFiltrada');
        $this->assertTrue($filtradas->contains('id_maquinaria', $this->maquinaria->id_maquinaria));

        // With matching search
        $component->set('busqueda_maquinaria', 'JD-1234');
        $filtradas = $component->get('maquinariasCargaFiltrada');
        $this->assertTrue($filtradas->contains('id_maquinaria', $this->maquinaria->id_maquinaria));

        // With non-matching search
        $component->set('busqueda_maquinaria', 'ZZZZNOEXISTE');
        $filtradas = $component->get('maquinariasCargaFiltrada');
        $this->assertFalse($filtradas->contains('id_maquinaria', $this->maquinaria->id_maquinaria));
    }

    // ================================================================
    // TARA > BRUTO WARNING
    // ================================================================

    public function test_tara_mayor_que_bruto_muestra_warning(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps())
            ->set('carga_peso_bruto', 5000)
            ->set('carga_tara', 8000);

        $component->assertSee('La tara no puede ser mayor al bruto');
    }

    public function test_tara_menor_o_igual_que_bruto_no_muestra_warning(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps())
            ->set('carga_peso_bruto', 25000)
            ->set('carga_tara', 8000);

        $component->assertDontSee('La tara no puede ser mayor al bruto');
    }

    // ================================================================
    // LOTE CHANGED EVENT
    // ================================================================

    public function test_lote_changed_event_resets_search_fields(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps())
            ->set('busqueda_chofer', 'test')
            ->set('busqueda_cliente', 'test')
            ->set('busqueda_empleado', 'test')
            ->set('busqueda_maquinaria', 'test');

        $component->dispatch('loteChanged');

        $component->assertSet('busqueda_chofer', '')
            ->assertSet('busqueda_cliente', '')
            ->assertSet('busqueda_empleado', '')
            ->assertSet('busqueda_maquinaria', '');
    }

    // ================================================================
    // CARGAS LIST DISPLAY
    // ================================================================

    public function test_cargas_list_displays_when_cargas_exist(): void
    {
        $cargas = [
            [
                'id_categoria_madera' => $this->categoriaMadera->id_categoria_madera,
                'ticket' => 'TK-DISPLAY',
                'peso_bruto' => 25000,
                'tara' => 8000,
                'peso_neto' => 17000,
                'id_chofer' => $this->chofer->id_chofer,
                'destino' => $this->cliente->id_cliente,
                'destino_nombre' => $this->cliente->razon_social,
                'empleados' => [$this->empleado->id_empleado],
                'maquinarias' => [$this->maquinaria->id_maquinaria],
            ],
        ];

        Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, array_merge($this->defaultProps(), ['cargas' => $cargas]))
            ->assertSee('TK-DISPLAY')
            ->assertSee('17,000.00');
    }

    public function test_empty_cargas_message_shows_when_no_cargas(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(CargaForm::class, $this->defaultProps())
            ->assertSee('Sin cargas registradas');
    }
}
