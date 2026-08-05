<?php

namespace Tests\Feature\Livewire;

use App\Enums\TaskType;
use App\Http\Livewire\LaunchpadModal;
use App\Jobs\SendPurchaseOrderEmail;
use App\Models\Empleado;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\Maquinaria;
use App\Models\PropuestaAsignacion;
use App\Models\PropuestaAsignacionEmpleado;
use App\Models\PropuestaAsignacionInsumo;
use App\Models\PropuestaAsignacionMaquinaria;
use App\Models\Usuario;
use App\Services\AutomaticAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Tests\TestCase;

class LaunchpadModalTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = Usuario::factory()->create();
    }

    private function mockAllocationService(): \Mockery\MockInterface
    {
        $mock = \Mockery::mock(AutomaticAllocationService::class);
        $this->app->instance(AutomaticAllocationService::class, $mock);

        return $mock;
    }

    // =========================================================================
    // Render / Mount
    // =========================================================================

    public function test_component_renders_successfully(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->assertStatus(200);
    }

    public function test_mount_inicializa_colecciones_vacias(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->assertSet('employees', collect())
            ->assertSet('machinery', collect())
            ->assertSet('supplies', collect())
            ->assertSet('supplies_cost', 0.0)
            ->assertSet('week_1_fuel', 0.0)
            ->assertSet('showModal', false)
            ->assertSet('loteId', null);
    }

    // =========================================================================
    // Apertura de modal
    // =========================================================================

    public function test_open_carga_propuesta_existente(): void
    {
        $lote = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);
        $proposal = PropuestaAsignacion::factory()->for($lote, 'lote')->create(['status' => 'draft']);
        PropuestaAsignacionEmpleado::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'selected' => true,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $lote->id_lote)
            ->assertSet('showModal', true)
            ->assertSet('loteId', $lote->id_lote)
            ->assertSet('proposal.id_allocation_proposal', $proposal->id_allocation_proposal);
    }

    public function test_open_genera_propuesta_si_no_existe(): void
    {
        $lote = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);

        $component = Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $lote->id_lote);

        $component->assertSet('showModal', true);
        $component->assertSet('loteId', $lote->id_lote);
    }

    public function test_open_con_lote_inexistente_no_rompe(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', 99999)
            ->assertStatus(200)
            ->assertSet('showModal', true)
            ->assertSet('proposal', null);
    }

    // =========================================================================
    // Baja confianza
    // =========================================================================

    public function test_propuesta_baja_confianza_confirma_pero_no_aplica(): void
    {
        $lote = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);
        $proposal = PropuestaAsignacion::factory()->lowConfidence()->for($lote, 'lote')->create([
            'status' => 'draft',
        ]);

        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $lote->id_lote)
            ->call('confirmAndLaunch');

        $proposal->refresh();
        $this->assertEquals('confirmed', $proposal->status);
    }

    public function test_propuesta_baja_confianza_muestra_mensaje_revision(): void
    {
        $lote = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);
        $proposal = PropuestaAsignacion::factory()->lowConfidence()->for($lote, 'lote')->create([
            'status' => 'draft',
        ]);

        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $lote->id_lote)
            ->call('confirmAndLaunch');

        $proposal->refresh();
        $this->assertEquals('confirmed', $proposal->status);
        $this->assertNotEquals('applied', $proposal->status);
        $this->assertNotNull($proposal->confirmed_at);

        $meta = $proposal->meta;
        $this->assertTrue($meta['review_required'] ?? false);
    }

    // =========================================================================
    // Conflictos
    // =========================================================================

    public function test_conflicto_empleados_ocupados_en_otro_lote_falla(): void
    {
        $loteActual = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);
        $loteOcupado = Lote::factory()->enProceso()->create();

        $empleado = Empleado::factory()->create();
        $loteOcupado->empleados()->attach($empleado->id_empleado);

        $proposal = PropuestaAsignacion::factory()->for($loteActual, 'lote')->create(['status' => 'draft']);
        PropuestaAsignacionEmpleado::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'id_empleado' => $empleado->id_empleado,
            'selected' => true,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $loteActual->id_lote)
            ->call('confirmAndLaunch');

        $loteActual->refresh();
        $this->assertNotEquals('en_proceso', $loteActual->estado);

        $proposal->refresh();
        $this->assertNotEquals('applied', $proposal->status);
    }

    public function test_conflicto_maquinarias_ocupadas_en_otro_lote_falla(): void
    {
        $loteActual = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);
        $loteOcupado = Lote::factory()->enProceso()->create();

        $maquinaria = Maquinaria::factory()->create();
        $loteOcupado->maquinarias()->attach($maquinaria->id_maquinaria);

        $proposal = PropuestaAsignacion::factory()->for($loteActual, 'lote')->create(['status' => 'draft']);
        PropuestaAsignacionMaquinaria::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'selected' => true,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $loteActual->id_lote)
            ->call('confirmAndLaunch');

        $loteActual->refresh();
        $this->assertNotEquals('en_proceso', $loteActual->estado);

        $proposal->refresh();
        $this->assertNotEquals('applied', $proposal->status);
    }

    public function test_empleado_en_lote_no_en_proceso_no_genera_conflicto(): void
    {
        $loteActual = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);
        $loteActivo = Lote::factory()->activo()->create();

        $empleado = Empleado::factory()->create();
        $loteActivo->empleados()->attach($empleado->id_empleado);

        $proposal = PropuestaAsignacion::factory()->for($loteActual, 'lote')->create(['status' => 'draft']);
        PropuestaAsignacionEmpleado::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'id_empleado' => $empleado->id_empleado,
            'selected' => true,
        ]);

        Bus::fake();

        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $loteActual->id_lote)
            ->call('confirmAndLaunch');

        $loteActual->refresh();
        $this->assertEquals('en_proceso', $loteActual->estado);

        $proposal->refresh();
        $this->assertEquals('applied', $proposal->status);
    }

    // =========================================================================
    // Aplicación exitosa
    // =========================================================================

    public function test_confirm_and_launch_aplica_propuesta_exitosamente(): void
    {
        $lote = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);
        $proposal = PropuestaAsignacion::factory()->for($lote, 'lote')->create(['status' => 'draft']);
        PropuestaAsignacionEmpleado::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'selected' => true,
        ]);

        Bus::fake();

        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $lote->id_lote)
            ->call('confirmAndLaunch');

        $proposal->refresh();
        $this->assertEquals('applied', $proposal->status);
        $this->assertNotNull($proposal->applied_at);
    }

    public function test_confirm_and_launch_cambia_estado_lote_a_en_proceso(): void
    {
        $lote = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);
        $proposal = PropuestaAsignacion::factory()->for($lote, 'lote')->create(['status' => 'draft']);
        PropuestaAsignacionEmpleado::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'selected' => true,
        ]);

        Bus::fake();

        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $lote->id_lote)
            ->call('confirmAndLaunch');

        $lote->refresh();
        $this->assertEquals('en_proceso', $lote->estado);
    }

    public function test_confirm_and_launch_sincroniza_empleados_y_maquinarias(): void
    {
        $lote = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);
        $empleado = Empleado::factory()->create();
        $maquinaria = Maquinaria::factory()->create();

        $proposal = PropuestaAsignacion::factory()->for($lote, 'lote')->create(['status' => 'draft']);
        PropuestaAsignacionEmpleado::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'id_empleado' => $empleado->id_empleado,
            'selected' => true,
        ]);
        PropuestaAsignacionMaquinaria::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'selected' => true,
        ]);

        Bus::fake();

        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $lote->id_lote)
            ->call('confirmAndLaunch');

        $lote->refresh();
        $this->assertTrue($lote->empleados->contains('id_empleado', $empleado->id_empleado));
        $this->assertTrue($lote->maquinarias->contains('id_maquinaria', $maquinaria->id_maquinaria));
    }

    public function test_confirm_and_launch_despacha_job_email(): void
    {
        $lote = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);
        $proposal = PropuestaAsignacion::factory()->for($lote, 'lote')->create(['status' => 'draft']);
        PropuestaAsignacionEmpleado::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'selected' => true,
        ]);

        Bus::fake();

        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $lote->id_lote)
            ->call('confirmAndLaunch');

        Bus::assertDispatched(SendPurchaseOrderEmail::class);
    }

    // =========================================================================
    // Cierre de propuestas
    // =========================================================================

    public function test_cierra_otras_propuestas_del_mismo_lote(): void
    {
        $lote = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);
        $proposal1 = PropuestaAsignacion::factory()->for($lote, 'lote')->create(['status' => 'draft', 'tipo_tarea' => TaskType::TALA_RASA->value]);
        $proposal2 = PropuestaAsignacion::factory()->for($lote, 'lote')->create(['status' => 'draft', 'tipo_tarea' => TaskType::TALA_RASA->value]);

        PropuestaAsignacionEmpleado::factory()->create([
            'id_allocation_proposal' => $proposal2->id_allocation_proposal,
            'selected' => true,
        ]);

        Bus::fake();

        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $lote->id_lote)
            ->call('confirmAndLaunch');

        $proposal1->refresh();
        $this->assertEquals('closed', $proposal1->status);
    }

    public function test_no_cierra_propuestas_de_otros_lotes(): void
    {
        $lote1 = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);
        $lote2 = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);

        $proposalLote1 = PropuestaAsignacion::factory()->for($lote1, 'lote')->create(['status' => 'draft']);
        $proposalLote2 = PropuestaAsignacion::factory()->for($lote2, 'lote')->create(['status' => 'draft']);

        PropuestaAsignacionEmpleado::factory()->create([
            'id_allocation_proposal' => $proposalLote1->id_allocation_proposal,
            'selected' => true,
        ]);

        Bus::fake();

        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $lote1->id_lote)
            ->call('confirmAndLaunch');

        $proposalLote2->refresh();
        $this->assertEquals('draft', $proposalLote2->status);
    }

    // =========================================================================
    // Edge cases
    // =========================================================================

    public function test_transaccion_rollback_si_falla(): void
    {
        $lote = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);
        $loteOcupado = Lote::factory()->enProceso()->create();

        $empleado = Empleado::factory()->create();
        $loteOcupado->empleados()->attach($empleado->id_empleado);

        $proposal = PropuestaAsignacion::factory()->for($lote, 'lote')->create(['status' => 'draft']);
        PropuestaAsignacionEmpleado::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'id_empleado' => $empleado->id_empleado,
            'selected' => true,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $lote->id_lote)
            ->call('confirmAndLaunch');

        $lote->refresh();
        $this->assertEquals('activo', $lote->estado);
        $proposal->refresh();
        $this->assertNotEquals('applied', $proposal->status);
    }

    public function test_calculo_supplies_cost_y_week_1_fuel(): void
    {
        $lote = Lote::factory()->create(['estado' => 'activo', 'main_task_type' => TaskType::TALA_RASA->value]);
        $proposal = PropuestaAsignacion::factory()->for($lote, 'lote')->create(['status' => 'draft']);

        $insumoDiesel = Insumo::factory()->create(['nombre' => 'Diesel grado 3']);
        $insumoNormal = Insumo::factory()->create(['nombre' => 'Aceite hidráulico']);

        PropuestaAsignacionInsumo::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'id_insumo' => $insumoDiesel->id_insumo,
            'cantidad_semana_1' => 50,
            'costo_estimado_semana_1' => 1500,
        ]);
        PropuestaAsignacionInsumo::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'id_insumo' => $insumoNormal->id_insumo,
            'cantidad_semana_1' => 10,
            'costo_estimado_semana_1' => 800,
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(LaunchpadModal::class)
            ->call('open', $lote->id_lote);

        $this->assertEquals(2300.0, $component->get('supplies_cost'));
        $this->assertEquals(50.0, $component->get('week_1_fuel'));
    }
}
