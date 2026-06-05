<?php

namespace Tests\Feature\Livewire;

use App\Enums\TaskType;
use App\Http\Livewire\PartesDiarios;
use App\Models\CategoriaMadera;
use App\Models\Chofer;
use App\Models\ClimaDiaLote;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\HistoricoRolLaboral;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\LoteInventario;
use App\Models\LoteTarea;
use App\Models\Maquinaria;
use App\Models\MovimientoStock;
use App\Models\ParteDiario;
use App\Models\RolLaboral;
use App\Models\TipoMaquinaria;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Services\ClimaOperativoService;
use App\Services\InventarioService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class PartesDiariosTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $usuario;

    protected Lote $lote;

    protected LoteTarea $loteTarea;

    protected Empleado $empleado;

    protected Maquinaria $maquinaria;

    protected CategoriaMadera $categoriaMadera;

    protected Chofer $chofer;

    protected Cliente $cliente;

    protected Insumo $insumo;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

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

        $this->loteTarea = LoteTarea::create([
            'id_lote' => $this->lote->id_lote,
            'tipo_tarea' => TaskType::TALA_RASA->value,
            'estado' => 'en_ejecucion',
            'fecha_inicio' => now()->toDateString(),
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

        $unidad = UnidadMedida::create(['nombre' => 'Litro', 'abreviatura' => 'L']);

        $this->insumo = Insumo::create([
            'nombre' => 'Aceite Hidraulico',
            'descripcion' => 'Aceite para maquinaria',
            'id_unidad_medida' => $unidad->id_unidad_medida,
        ]);

        $this->mockClimaOperativo();
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

    private function setRequiredFields($component, array $overrides = []): void
    {
        $defaults = [
            'id_lote' => $this->lote->id_lote,
            'id_lote_tarea' => $this->loteTarea->id_lote_tarea,
            'fecha' => Carbon::today()->toDateString(),
        ];

        foreach (array_merge($defaults, $overrides) as $key => $value) {
            $component->set($key, $value);
        }
    }

    private function crearStockDisponible(?int $insumoId = null, float $cantidad = 100, float $precio = 50.0): void
    {
        LoteInventario::create([
            'id_insumo' => $insumoId ?? $this->insumo->id_insumo,
            'cantidad_inicial' => $cantidad,
            'cantidad_disponible' => $cantidad,
            'precio_unitario' => $precio,
            'costo_total' => $cantidad * $precio,
            'fecha_compra' => now()->subDays(5)->toDateString(),
            'agotado' => false,
        ]);
    }

    private function crearParteDiario(array $overrides = []): ParteDiario
    {
        return ParteDiario::create(array_merge([
            'id_lote' => $this->lote->id_lote,
            'id_lote_tarea' => $this->loteTarea->id_lote_tarea,
            'fecha' => Carbon::today()->toDateString(),
            'tipo_tarea' => TaskType::TALA_RASA->value,
            'es_dia_caido' => false,
        ], $overrides));
    }

    private function cargaData(array $overrides = []): array
    {
        return array_merge([
            'id_categoria_madera' => $this->categoriaMadera->id_categoria_madera,
            'ticket' => 'TK-TEST-001',
            'peso_bruto' => 25000,
            'tara' => 8000,
            'peso_neto' => 17000,
            'id_chofer' => $this->chofer->id_chofer,
            'destino' => $this->cliente->id_cliente,
            'destino_nombre' => $this->cliente->razon_social,
            'empleados' => [$this->empleado->id_empleado],
            'maquinarias' => [$this->maquinaria->id_maquinaria],
        ], $overrides);
    }

    private function jornalData(array $overrides = []): array
    {
        return array_merge([
            'id_empleado' => $this->empleado->id_empleado,
            'nombre_completo' => "{$this->empleado->apellido}, {$this->empleado->nombre}",
            'rol' => 'Operario',
            'jornal_diario' => 1500,
            'observaciones' => null,
        ], $overrides);
    }

    private function movimientoData(array $overrides = []): array
    {
        return array_merge([
            'id_insumo' => $this->insumo->id_insumo,
            'nombre_insumo' => $this->insumo->nombre,
            'tipo' => 'salida',
            'cantidad' => 10,
            'motivo' => 'Produccion',
            'observaciones' => null,
            'unidad' => 'Litro',
        ], $overrides);
    }

    // ================================================================
    // AUTHENTICATION
    // ================================================================

    public function test_unauthenticated_user_cannot_access_component(): void
    {
        auth()->logout();

        $this->get(route('dashboard'))->assertRedirect('/login');
    }

    public function test_authenticated_user_can_render_component(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->assertStatus(200);
    }

    // ================================================================
    // VALIDATION (rules())
    // ================================================================

    public function test_cannot_save_without_lote(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('id_lote_tarea', $this->loteTarea->id_lote_tarea)
            ->set('fecha', Carbon::today()->toDateString())
            ->call('guardar')
            ->assertHasErrors(['id_lote']);
    }

    public function test_cannot_save_without_lote_tarea(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('id_lote', $this->lote->id_lote)
            ->set('fecha', Carbon::today()->toDateString())
            ->call('guardar')
            ->assertHasErrors(['id_lote_tarea']);
    }

    public function test_cannot_save_without_fecha(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('id_lote', $this->lote->id_lote)
            ->set('id_lote_tarea', $this->loteTarea->id_lote_tarea)
            ->call('guardar')
            ->assertHasErrors(['fecha']);
    }

    public function test_cannot_save_with_future_date(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c, [
                'fecha' => Carbon::tomorrow()->toDateString(),
            ]))
            ->call('guardar')
            ->assertHasErrors(['fecha']);
    }

    public function test_cannot_save_with_date_older_than_7_days(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c, [
                'fecha' => Carbon::today()->subDays(10)->toDateString(),
            ]))
            ->call('guardar')
            ->assertHasErrors(['fecha']);
    }

    public function test_cannot_save_with_invalid_lote_id(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c, [
                'id_lote' => 99999,
            ]))
            ->call('guardar')
            ->assertHasErrors(['id_lote']);
    }

    public function test_cannot_save_with_lote_tarea_from_different_lote(): void
    {
        $otroLote = Lote::create([
            'propietario' => 'Other',
            'condicion_compra' => 'propio',
            'estado' => 'activo',
            'especie' => 'Pino',
            'superficie' => 50,
            'latitud' => -29.0,
            'longitud' => -57.0,
            'ubicacion' => 'Test Location 2',
        ]);

        $tareaOtroLote = LoteTarea::create([
            'id_lote' => $otroLote->id_lote,
            'tipo_tarea' => TaskType::PODA->value,
            'estado' => 'planificada',
            'fecha_inicio' => now()->toDateString(),
        ]);

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c, [
                'id_lote' => $this->lote->id_lote,
                'id_lote_tarea' => $tareaOtroLote->id_lote_tarea,
            ]))
            ->call('guardar')
            ->assertHasErrors(['id_lote_tarea']);
    }

    // ================================================================
    // GUARDAR — Business Guards
    // ================================================================

    public function test_guardar_requires_at_least_one_carga_in_production_mode(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', false)
            ->call('guardar');

        $this->assertEquals(0, ParteDiario::count());
    }

    public function test_guardar_requires_at_least_one_jornal_in_dia_caido_mode(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', true)
            ->call('guardar');

        $this->assertEquals(0, ParteDiario::count());
    }

    public function test_guardar_blocks_future_date_as_security_guard(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c, [
                'fecha' => Carbon::tomorrow()->toDateString(),
            ]))
            ->call('guardar');

        $this->assertEquals(0, ParteDiario::count());
    }

    // ================================================================
    // GUARDAR — Production Mode (cargas)
    // ================================================================

    public function test_guardar_creates_parte_diario_in_production_mode(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', false)
            ->set('cargas', [$this->cargaData()])
            ->call('guardar');

        $this->assertDatabaseHas('parte_diarios', [
            'id_lote' => $this->lote->id_lote,
            'fecha' => Carbon::today()->toDateString(),
            'es_dia_caido' => false,
        ]);

        $this->assertDatabaseHas('cargas', [
            'id_lote' => $this->lote->id_lote,
            'ticket' => 'TK-TEST-001',
            'peso_neto' => 17000,
        ]);
    }

    public function test_guardar_creates_carga_with_empleados_and_maquinarias_pivot(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', false)
            ->set('cargas', [$this->cargaData(['ticket' => 'TK-PIVOT'])])
            ->call('guardar');

        $carga = \DB::table('cargas')->where('ticket', 'TK-PIVOT')->first();
        $this->assertNotNull($carga);

        $this->assertDatabaseHas('carga_empleado', [
            'id_carga' => $carga->id_carga,
            'id_empleado' => $this->empleado->id_empleado,
        ]);

        $this->assertDatabaseHas('carga_maquinaria', [
            'id_carga' => $carga->id_carga,
            'id_maquinaria' => $this->maquinaria->id_maquinaria,
        ]);
    }

    public function test_guardar_with_multiple_cargas_creates_all(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', false)
            ->set('cargas', [
                $this->cargaData(['ticket' => 'TK-MULTI-1']),
                $this->cargaData(['ticket' => 'TK-MULTI-2', 'peso_neto' => 16000]),
            ])
            ->call('guardar');

        $this->assertEquals(2, \DB::table('cargas')->where('ticket', 'like', 'TK-MULTI-%')->count());
    }

    // ================================================================
    // GUARDAR — Dia Caído Mode (jornales)
    // ================================================================

    public function test_guardar_creates_parte_diario_in_dia_caido_mode(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', true)
            ->set('jornales', [$this->jornalData()])
            ->call('guardar');

        $this->assertDatabaseHas('parte_diarios', [
            'id_lote' => $this->lote->id_lote,
            'fecha' => Carbon::today()->toDateString(),
            'es_dia_caido' => true,
        ]);

        $parte = ParteDiario::where('id_lote', $this->lote->id_lote)->first();
        $this->assertNotNull($parte);
        $this->assertTrue(
            $parte->empleados->contains('id_empleado', $this->empleado->id_empleado)
        );
    }

    public function test_guardar_dia_caido_does_not_create_cargas(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', true)
            ->set('jornales', [$this->jornalData()])
            ->call('guardar');

        $parte = ParteDiario::where('id_lote', $this->lote->id_lote)->first();
        $this->assertEquals(0, $parte->cargas()->count());
    }

    // ================================================================
    // GUARDAR — Updates existing Parte Diario
    // ================================================================

    public function test_guardar_updates_existing_parte_diario_for_same_lote_and_fecha(): void
    {
        $this->crearParteDiario();
        $this->assertEquals(1, ParteDiario::count());

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', true)
            ->set('jornales', [$this->jornalData()])
            ->call('guardar');

        $this->assertEquals(1, ParteDiario::count());
    }

    // ================================================================
    // EDITAR
    // ================================================================

    public function test_editar_loads_parte_diario_data(): void
    {
        $parte = $this->crearParteDiario(['observaciones' => 'Test obs']);

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->call('editar', $parte->id_parte_diario)
            ->assertSet('parte_id', $parte->id_parte_diario)
            ->assertSet('id_lote', $this->lote->id_lote)
            ->assertSet('id_lote_tarea', $this->loteTarea->id_lote_tarea)
            ->assertSet('es_dia_caido', false)
            ->assertSet('observaciones', 'Test obs')
            ->assertSet('tab_activo', 'nuevo');
    }

    public function test_editar_loads_cargas_for_production_mode(): void
    {
        $parte = $this->crearParteDiario();

        $cargaId = \DB::table('cargas')->insertGetId([
            'id_parte_diario' => $parte->id_parte_diario,
            'id_lote' => $this->lote->id_lote,
            'id_categoria_madera' => $this->categoriaMadera->id_categoria_madera,
            'id_chofer' => $this->chofer->id_chofer,
            'ticket' => 'TK-EDIT',
            'peso_bruto' => 20000,
            'tara' => 5000,
            'peso_neto' => 15000,
            'destino' => $this->cliente->razon_social,
            'fecha_carga' => Carbon::today()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('carga_empleado')->insert([
            'id_carga' => $cargaId,
            'id_empleado' => $this->empleado->id_empleado,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('carga_maquinaria')->insert([
            'id_carga' => $cargaId,
            'id_maquinaria' => $this->maquinaria->id_maquinaria,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->call('editar', $parte->id_parte_diario);

        $cargas = $component->get('cargas');
        $this->assertNotEmpty($cargas);
        $this->assertEquals('TK-EDIT', $cargas[0]['ticket']);
    }

    public function test_editar_loads_jornales_for_dia_caido_mode(): void
    {
        $parte = $this->crearParteDiario(['es_dia_caido' => true]);
        $parte->empleados()->attach($this->empleado->id_empleado);

        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->call('editar', $parte->id_parte_diario);

        $this->assertTrue((bool) $component->get('es_dia_caido'));
        $jornales = $component->get('jornales');
        $this->assertNotEmpty($jornales);
        $this->assertEquals($this->empleado->id_empleado, $jornales[0]['id_empleado']);
    }

    public function test_editar_nonexistent_parte_diario_throws(): void
    {
        $this->withoutExceptionHandling();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->call('editar', 99999);
    }

    // ================================================================
    // ELIMINAR
    // ================================================================

    public function test_eliminar_soft_deletes_parte_diario(): void
    {
        $parte = $this->crearParteDiario();

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->call('eliminar', $parte->id_parte_diario);

        $this->assertSoftDeleted('parte_diarios', [
            'id_parte_diario' => $parte->id_parte_diario,
        ]);
    }

    public function test_eliminar_nonexistent_throws(): void
    {
        $this->withoutExceptionHandling();
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->call('eliminar', 99999);
    }

    // ================================================================
    // CARGA MANAGEMENT
    // ================================================================

    public function test_agregar_carga_with_valid_data(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
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

        $cargas = $component->get('cargas');
        $this->assertCount(1, $cargas);
        $this->assertEquals('TK-TEST-001', $cargas[0]['ticket']);
        $this->assertEquals(17000, $cargas[0]['peso_neto']);
    }

    public function test_agregar_carga_resets_form_after_adding(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
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

    public function test_cannot_agregar_carga_with_negative_peso_neto(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
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

    public function test_cannot_agregar_carga_with_zero_peso_neto(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('carga_id_categoria_madera', $this->categoriaMadera->id_categoria_madera)
            ->set('carga_ticket', 'TK-ZERO')
            ->set('carga_peso_bruto', 5000)
            ->set('carga_tara', 5000)
            ->set('carga_peso_neto', 0)
            ->set('carga_id_chofer', $this->chofer->id_chofer)
            ->set('carga_destino', $this->cliente->id_cliente)
            ->set('carga_empleados', [$this->empleado->id_empleado])
            ->set('carga_maquinarias', [$this->maquinaria->id_maquinaria])
            ->call('agregarCarga')
            ->assertHasErrors(['carga_peso_neto']);
    }

    public function test_cannot_agregar_carga_without_ticket(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
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
            ->test(PartesDiarios::class)
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
            ->test(PartesDiarios::class)
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

    public function test_eliminar_carga_removes_from_array(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('cargas', [
                $this->cargaData(['ticket' => 'TK-DEL-1']),
                $this->cargaData(['ticket' => 'TK-DEL-2', 'peso_neto' => 16000]),
            ]);

        $component->call('eliminarCarga', 0);

        $cargas = $component->get('cargas');
        $this->assertCount(1, $cargas);
        $this->assertEquals('TK-DEL-2', $cargas[0]['ticket']);
    }

    public function test_eliminar_carga_recalculates_total_toneladas(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class);

        $component->call('agregarCarga');

        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('cargas', [
                $this->cargaData(['ticket' => 'TK-T1', 'peso_neto' => 15000]),
                $this->cargaData(['ticket' => 'TK-T2', 'peso_neto' => 16000]),
            ]);

        $component->call('eliminarCarga', 0);

        $cargas = $component->get('cargas');
        $this->assertCount(1, $cargas);
        $this->assertEquals(16000, $component->get('total_toneladas'));
    }

    // ================================================================
    // JORNAL MANAGEMENT
    // ================================================================

    public function test_agregar_jornal_with_valid_data(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('fecha', Carbon::today()->toDateString())
            ->set('jornal_id_empleado', $this->empleado->id_empleado);

        $component->call('agregarJornal');

        $jornales = $component->get('jornales');
        $this->assertCount(1, $jornales);
        $this->assertEquals($this->empleado->id_empleado, $jornales[0]['id_empleado']);
    }

    public function test_cannot_agregar_jornal_without_employee(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('jornal_id_empleado', null)
            ->call('agregarJornal')
            ->assertHasErrors(['jornal_id_empleado']);
    }

    public function test_cannot_agregar_jornal_with_invalid_employee(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('jornal_id_empleado', 99999)
            ->call('agregarJornal')
            ->assertHasErrors(['jornal_id_empleado']);
    }

    public function test_cannot_agregar_duplicate_jornal(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('fecha', Carbon::today()->toDateString())
            ->set('jornal_id_empleado', $this->empleado->id_empleado);

        $component->call('agregarJornal');
        $this->assertCount(1, $component->get('jornales'));

        $component->set('jornal_id_empleado', $this->empleado->id_empleado);
        $component->call('agregarJornal');

        $this->assertCount(1, $component->get('jornales'));
    }

    public function test_eliminar_jornal_removes_from_array(): void
    {
        $empleado2 = Empleado::create([
            'dni' => '77.777.777',
            'apellido' => 'Other',
            'nombre' => 'Person',
            'fecha_inicio_actividades' => now()->subYear()->toDateString(),
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('jornales', [
                $this->jornalData(),
                $this->jornalData(['id_empleado' => $empleado2->id_empleado, 'nombre_completo' => 'Other, Person', 'rol' => 'N/A', 'jornal_diario' => 0]),
            ]);

        $component->call('eliminarJornal', 0);

        $jornales = $component->get('jornales');
        $this->assertCount(1, $jornales);
        $this->assertEquals($empleado2->id_empleado, $jornales[0]['id_empleado']);
    }

    // ================================================================
    // MOVIMIENTO (STOCK) MANAGEMENT
    // ================================================================

    public function test_agregar_movimiento_with_valid_data_and_stock(): void
    {
        $this->crearStockDisponible();

        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->set('movimiento_cantidad', 10)
            ->set('movimiento_motivo', 'Producción');

        $component->call('agregarMovimiento');

        $movimientos = $component->get('movimientos');
        $this->assertCount(1, $movimientos);
        $this->assertEquals($this->insumo->id_insumo, $movimientos[0]['id_insumo']);
        $this->assertEquals(10, $movimientos[0]['cantidad']);
        $this->assertEquals('salida', $movimientos[0]['tipo']);
    }

    public function test_cannot_agregar_movimiento_exceeding_available_stock(): void
    {
        $this->crearStockDisponible(null, 5);

        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->set('movimiento_cantidad', 10)
            ->set('movimiento_motivo', 'Producción');

        $component->call('agregarMovimiento');

        $movimientos = $component->get('movimientos');
        $this->assertEmpty($movimientos);
    }

    public function test_cannot_agregar_movimiento_without_insumo(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('movimiento_cantidad', 10)
            ->set('movimiento_motivo', 'Producción')
            ->call('agregarMovimiento')
            ->assertHasErrors(['movimiento_id_insumo']);
    }

    public function test_cannot_agregar_movimiento_with_zero_quantity(): void
    {
        $this->crearStockDisponible();

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->set('movimiento_cantidad', 0)
            ->set('movimiento_motivo', 'Producción')
            ->call('agregarMovimiento')
            ->assertHasErrors(['movimiento_cantidad']);
    }

    public function test_cannot_agregar_movimiento_with_negative_quantity(): void
    {
        $this->crearStockDisponible();

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->set('movimiento_cantidad', -5)
            ->set('movimiento_motivo', 'Producción')
            ->call('agregarMovimiento')
            ->assertHasErrors(['movimiento_cantidad']);
    }

    public function test_cannot_agregar_movimiento_with_invalid_motivo(): void
    {
        $this->crearStockDisponible();

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->set('movimiento_cantidad', 10)
            ->set('movimiento_motivo', 'Invalido')
            ->call('agregarMovimiento')
            ->assertHasErrors(['movimiento_motivo']);
    }

    public function test_eliminar_movimiento_removes_from_array(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('movimientos', [
                $this->movimientoData(),
                $this->movimientoData(['cantidad' => 5, 'motivo' => 'Mantenimiento']),
            ]);

        $component->call('eliminarMovimiento', 0);

        $movimientos = $component->get('movimientos');
        $this->assertCount(1, $movimientos);
        $this->assertEquals(5, $movimientos[0]['cantidad']);
    }

    // ================================================================
    // PESO NETO AUTO-CALCULATION
    // ================================================================

    public function test_peso_neto_calculated_from_peso_bruto_minus_tara(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class);

        $component->set('carga_peso_bruto', 25000);
        $component->set('carga_tara', 8000);

        $component->assertSet('carga_peso_neto', 17000);
    }

    public function test_peso_neto_cleared_when_inputs_are_empty(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class);

        $component->set('carga_peso_bruto', 25000);
        $component->set('carga_tara', 8000);
        $component->assertSet('carga_peso_neto', 17000);

        $component->set('carga_peso_bruto', null);
        $component->assertSet('carga_peso_neto', null);
    }

    // ================================================================
    // TRANSACTION SAFETY
    // ================================================================

    public function test_guardar_rolls_back_on_service_exception(): void
    {
        $initialCount = ParteDiario::count();

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', false)
            ->set('cargas', [$this->cargaData(['maquinarias' => [99999]])])
            ->set('movimientos', [$this->movimientoData(['id_insumo' => 99999])])
            ->call('guardar');

        $this->assertEquals($initialCount, ParteDiario::count());
    }

    // ================================================================
    // COST CALCULATIONS
    // ================================================================

    public function test_guardar_triggers_cost_calculation(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', true)
            ->set('jornales', [$this->jornalData()])
            ->call('guardar');

        $parte = ParteDiario::where('id_lote', $this->lote->id_lote)
            ->whereDate('fecha', Carbon::today()->toDateString())
            ->first();

        $this->assertNotNull($parte);
        $this->assertNotNull($parte->costo_total_dia);
    }

    // ================================================================
    // FIFO INTEGRATION
    // ================================================================

    public function test_guardar_with_movimiento_registers_fifo_salida(): void
    {
        $this->crearStockDisponible(null, 100, 50.0);

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', true)
            ->set('jornales', [$this->jornalData()])
            ->set('movimientos', [$this->movimientoData()])
            ->call('guardar');

        $this->assertDatabaseHas('movimiento_stocks', [
            'id_insumo' => $this->insumo->id_insumo,
            'tipo' => 'salida',
        ]);

        $loteInventario = LoteInventario::where('id_insumo', $this->insumo->id_insumo)->first();
        $this->assertNotNull($loteInventario);
        $this->assertEquals(90, (float) $loteInventario->cantidad_disponible);
    }

    public function test_guardar_with_movimiento_decrements_stock_correctly(): void
    {
        $this->crearStockDisponible(null, 50, 100.0);

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', true)
            ->set('jornales', [$this->jornalData()])
            ->set('movimientos', [$this->movimientoData(['cantidad' => 30])])
            ->call('guardar');

        $stock = InventarioService::stockDisponible($this->insumo->id_insumo);
        $this->assertEquals(20, (float) $stock);
    }

    public function test_fifo_consumes_oldest_lots_first(): void
    {
        $loteViejo = LoteInventario::create([
            'id_insumo' => $this->insumo->id_insumo,
            'cantidad_inicial' => 50,
            'cantidad_disponible' => 50,
            'precio_unitario' => 10.0,
            'costo_total' => 500.0,
            'fecha_compra' => now()->subDays(30)->toDateString(),
            'agotado' => false,
        ]);

        $loteNuevo = LoteInventario::create([
            'id_insumo' => $this->insumo->id_insumo,
            'cantidad_inicial' => 50,
            'cantidad_disponible' => 50,
            'precio_unitario' => 20.0,
            'costo_total' => 1000.0,
            'fecha_compra' => now()->subDays(5)->toDateString(),
            'agotado' => false,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', true)
            ->set('jornales', [$this->jornalData()])
            ->set('movimientos', [$this->movimientoData(['cantidad' => 60])])
            ->call('guardar');

        $loteViejoActualizado = LoteInventario::find($loteViejo->id_lote_inventario);
        $loteNuevoActualizado = LoteInventario::find($loteNuevo->id_lote_inventario);

        $this->assertEquals(0, (float) $loteViejoActualizado->cantidad_disponible);
        $this->assertTrue($loteViejoActualizado->agotado);
        $this->assertEquals(40, (float) $loteNuevoActualizado->cantidad_disponible);
    }

    // ================================================================
    // RESET / CANCEL
    // ================================================================

    public function test_reset_campos_clears_all_fields(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('id_lote', $this->lote->id_lote)
            ->set('fecha', Carbon::today()->toDateString())
            ->set('observaciones', 'Some obs')
            ->set('es_dia_caido', true);

        $component->call('resetCampos');

        $component->assertSet('parte_id', null)
            ->assertSet('id_lote', null)
            ->assertSet('fecha', null)
            ->assertSet('observaciones', null)
            ->assertSet('es_dia_caido', false)
            ->assertSet('cargas', [])
            ->assertSet('jornales', [])
            ->assertSet('movimientos', [])
            ->assertSet('total_toneladas', 0);
    }

    public function test_cancelar_edicion_resets_and_returns_to_list(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('id_lote', $this->lote->id_lote)
            ->set('tab_activo', 'nuevo');

        $component->call('cancelarEdicion');

        $component->assertSet('tab_activo', 'listado')
            ->assertSet('id_lote', null);
    }

    // ================================================================
    // SEARCH / PAGINATION
    // ================================================================

    public function test_busqueda_fecha_resets_pagination(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('busqueda_fecha', Carbon::today()->toDateString())
            ->assertSet('busqueda_fecha', Carbon::today()->toDateString());
    }

    // ================================================================
    // TAREA RAPIDA
    // ================================================================

    public function test_crear_tarea_rapida_without_lote_does_not_create(): void
    {
        $initialCount = LoteTarea::count();

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('nueva_tarea_tipo_tarea', TaskType::PODA->value)
            ->call('crearTareaRapida');

        $this->assertEquals($initialCount, LoteTarea::count());
    }

    public function test_crear_tarea_rapida_without_tipo_does_not_create(): void
    {
        $initialCount = LoteTarea::count();

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('id_lote', $this->lote->id_lote)
            ->call('crearTareaRapida');

        $this->assertEquals($initialCount, LoteTarea::count());
    }

    public function test_crear_tarea_rapida_with_valid_data(): void
    {
        $initialCount = LoteTarea::where('id_lote', $this->lote->id_lote)->count();

        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('id_lote', $this->lote->id_lote)
            ->set('fecha', Carbon::today()->toDateString())
            ->set('nueva_tarea_tipo_tarea', TaskType::PODA->value);

        $component->call('crearTareaRapida');

        $this->assertEquals($initialCount + 1, LoteTarea::where('id_lote', $this->lote->id_lote)->count());
    }

    public function test_crear_tarea_rapida_with_negative_superficie_does_not_create(): void
    {
        $initialCount = LoteTarea::count();

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('id_lote', $this->lote->id_lote)
            ->set('nueva_tarea_tipo_tarea', TaskType::PODA->value)
            ->set('nueva_tarea_superficie_afectada_ha', -5)
            ->call('crearTareaRapida');

        $this->assertEquals($initialCount, LoteTarea::count());
    }

    // ================================================================
    // CLIMATE OVERRIDE
    // ================================================================

    public function test_guardar_requires_override_when_climate_is_inactive(): void
    {
        $this->mockClimaOperativo('INACTIVO');

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', false)
            ->set('clima_requiere_override', true)
            ->set('clima_override_confirmado', false)
            ->set('cargas', [$this->cargaData()])
            ->call('guardar');

        $this->assertEquals(0, ParteDiario::count());
    }

    public function test_guardar_with_inactive_climate_and_valid_override_succeeds(): void
    {
        $this->mockClimaOperativo('INACTIVO');

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', false)
            ->set('clima_requiere_override', true)
            ->set('clima_override_confirmado', true)
            ->set('clima_override_motivo', 'Urgent work needed')
            ->set('cargas', [$this->cargaData()])
            ->call('guardar');

        $this->assertDatabaseHas('parte_diarios', [
            'id_lote' => $this->lote->id_lote,
            'clima_override' => true,
        ]);
    }

    public function test_guardar_with_inactive_climate_override_without_motivo_fails(): void
    {
        $this->mockClimaOperativo('INACTIVO');

        Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->tap(fn ($c) => $this->setRequiredFields($c))
            ->set('es_dia_caido', false)
            ->set('clima_requiere_override', true)
            ->set('clima_override_confirmado', true)
            ->set('clima_override_motivo', '')
            ->set('cargas', [$this->cargaData()])
            ->call('guardar');

        $this->assertEquals(0, ParteDiario::count());
    }

    // ================================================================
    // DIA CAIDO TOGGLES CLEANUP
    // ================================================================

    public function test_toggling_es_dia_caido_to_false_clears_jornales(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(PartesDiarios::class)
            ->set('es_dia_caido', true)
            ->set('jornales', [$this->jornalData()])
            ->set('motivo_dia_caido', 'Lluvia');

        $component->set('es_dia_caido', false);

        $component->assertSet('jornales', [])
            ->assertSet('motivo_dia_caido', '');
    }
}
