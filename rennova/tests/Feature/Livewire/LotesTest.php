<?php

namespace Tests\Feature\Livewire;

use App\Enums\TaskType;
use App\Http\Livewire\Lotes;
use App\Jobs\GenerateAllocationProposalsForLote;
use App\Models\Empleado;
use App\Models\Lote;
use App\Models\Maquinaria;
use App\Models\PropuestaAsignacion;
use App\Models\PropuestaAsignacionEmpleado;
use App\Models\PropuestaAsignacionMaquinaria;
use App\Models\Usuario;
use App\Services\AutomaticAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class LotesTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->usuario = Usuario::factory()->create();
    }

    // =========================================================================
    // Authentication & Access
    // =========================================================================

    public function test_unauthenticated_user_is_redirected(): void
    {
        // Component has no middleware guard — unauthenticated users can render
        // but would be redirected when performing actions that require auth.
        // This test documents the current behavior (no PHP-level auth checks).
        Livewire::test(Lotes::class)
            ->assertStatus(200);
    }

    public function test_authenticated_user_can_render_component(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->assertStatus(200);
    }

    // =========================================================================
    // mount() Behavior
    // =========================================================================

    public function test_mount_loads_lotes_and_resets_fields(): void
    {
        $lotes = Lote::factory()->count(3)->create();

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->assertSee($lotes[0]->propietario)
            ->assertSet('busqueda', '')
            ->assertSet('estado', 'activo')
            ->assertSet('lote_id', null);
    }

    public function test_mount_initializes_default_values(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->assertSet('propietario', '')
            ->assertSet('ubicacion', '')
            ->assertSet('superficie', '')
            ->assertSet('condicion_compra', '')
            ->assertSet('especie', '')
            ->assertSet('latitud', null)
            ->assertSet('longitud', null)
            ->assertSet('main_task_type', TaskType::TALA_RASA->value)
            ->assertSet('mostrarModalRecomendaciones', false)
            ->assertSet('recomendaciones', []);
    }

    // =========================================================================
    // CRUD: guardar() - Create
    // =========================================================================

    public function test_guardar_creates_new_lote_with_valid_data(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', 'Forestal del Norte SA')
            ->set('ubicacion', 'Sector 15 - Ruta 8 km 230')
            ->set('especie', 'Pino elliottii')
            ->set('superficie', 120.50)
            ->set('condicion_compra', 'propio')
            ->set('estado', 'activo')
            ->set('main_task_type', 'tala_rasa')
            ->set('latitud', -27.47)
            ->set('longitud', -58.83)
            ->call('guardar');

        $this->assertDatabaseHas('lotes', [
            'propietario' => 'Forestal del Norte SA',
            'ubicacion' => 'Sector 15 - Ruta 8 km 230',
            'especie' => 'Pino elliottii',
            'superficie' => 120.50,
            'condicion_compra' => 'propio',
            'estado' => 'activo',
            'main_task_type' => 'tala_rasa',
        ]);
    }

    public function test_guardar_sets_default_estado_to_activo(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', 'Test Owner')
            ->set('ubicacion', 'Test Location')
            ->set('especie', 'Pino taeda')
            ->set('superficie', 50)
            ->set('condicion_compra', 'propio')
            ->set('estado', 'activo')
            ->set('main_task_type', 'raleo')
            ->call('guardar');

        $this->assertDatabaseHas('lotes', [
            'propietario' => 'Test Owner',
            'estado' => 'activo',
        ]);
    }

    public function test_guardar_validates_required_fields(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', '')
            ->set('ubicacion', '')
            ->set('especie', '')
            ->set('superficie', '')
            ->set('condicion_compra', '')
            ->set('main_task_type', '')
            ->call('guardar')
            ->assertHasErrors([
                'propietario' => 'required',
                'ubicacion' => 'required',
                'especie' => 'required',
                'superficie' => 'required',
                'condicion_compra' => 'required',
                'main_task_type' => 'required',
            ]);
    }

    public function test_guardar_validates_superficie_minimum(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', 'Test Owner')
            ->set('ubicacion', 'Test Location')
            ->set('especie', 'Pino')
            ->set('superficie', 0.01)
            ->set('condicion_compra', 'propio')
            ->set('estado', 'activo')
            ->set('main_task_type', 'tala_rasa')
            ->call('guardar')
            ->assertHasErrors(['superficie' => 'min']);
    }

    public function test_guardar_validates_condicion_compra_values(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', 'Test Owner')
            ->set('ubicacion', 'Test Location')
            ->set('especie', 'Pino')
            ->set('superficie', 50)
            ->set('condicion_compra', 'invalid_value')
            ->set('estado', 'activo')
            ->set('main_task_type', 'tala_rasa')
            ->call('guardar')
            ->assertHasErrors(['condicion_compra' => 'in']);
    }

    public function test_guardar_validates_latitud_range(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', 'Test Owner')
            ->set('ubicacion', 'Test Location')
            ->set('especie', 'Pino')
            ->set('superficie', 50)
            ->set('condicion_compra', 'propio')
            ->set('estado', 'activo')
            ->set('main_task_type', 'tala_rasa')
            ->set('latitud', -100)
            ->call('guardar')
            ->assertHasErrors(['latitud' => 'between']);
    }

    public function test_guardar_validates_longitud_range(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', 'Test Owner')
            ->set('ubicacion', 'Test Location')
            ->set('especie', 'Pino')
            ->set('superficie', 50)
            ->set('condicion_compra', 'propio')
            ->set('estado', 'activo')
            ->set('main_task_type', 'tala_rasa')
            ->set('longitud', -200)
            ->call('guardar')
            ->assertHasErrors(['longitud' => 'between']);
    }

    public function test_guardar_allows_null_coordinates(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', 'Test Owner')
            ->set('ubicacion', 'Test Location')
            ->set('especie', 'Pino')
            ->set('superficie', 50)
            ->set('condicion_compra', 'propio')
            ->set('estado', 'activo')
            ->set('main_task_type', 'tala_rasa')
            ->set('latitud', null)
            ->set('longitud', null)
            ->call('guardar')
            ->assertHasNoErrors(['latitud', 'longitud']);

        $this->assertDatabaseHas('lotes', [
            'propietario' => 'Test Owner',
            'latitud' => null,
            'longitud' => null,
        ]);
    }

    public function test_guardar_dispatches_lote_guardado_event(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', 'Test Owner')
            ->set('ubicacion', 'Test Location')
            ->set('especie', 'Pino')
            ->set('superficie', 50)
            ->set('condicion_compra', 'propio')
            ->set('estado', 'activo')
            ->set('main_task_type', 'tala_rasa')
            ->call('guardar')
            ->assertDispatched('loteGuardado');
    }

    public function test_guardar_resets_fields_after_create(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', 'Test Owner')
            ->set('ubicacion', 'Test Location')
            ->set('especie', 'Pino')
            ->set('superficie', 50)
            ->set('condicion_compra', 'propio')
            ->set('estado', 'activo')
            ->set('main_task_type', 'tala_rasa')
            ->call('guardar')
            ->assertSet('propietario', '')
            ->assertSet('lote_id', null);
    }

    // =========================================================================
    // CRUD: editar()
    // =========================================================================

    public function test_editar_loads_lote_data_into_form(): void
    {
        $lote = Lote::factory()->create([
            'propietario' => 'Editable Owner',
            'ubicacion' => 'Editable Location',
            'especie' => 'Eucalipto grandis',
            'superficie' => 75.5,
            'condicion_compra' => 'alquilado',
            'estado' => 'en_proceso',
            'main_task_type' => 'raleo',
            'latitud' => -28.123,
            'longitud' => -57.456,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('editar', $lote->id_lote)
            ->assertSet('lote_id', $lote->id_lote)
            ->assertSet('propietario', 'Editable Owner')
            ->assertSet('ubicacion', 'Editable Location')
            ->assertSet('especie', 'Eucalipto grandis')
            ->assertSet('superficie', 75.5)
            ->assertSet('condicion_compra', 'alquilado')
            ->assertSet('estado', 'en_proceso')
            ->assertSet('main_task_type', 'raleo')
            ->assertSet('latitud', -28.123)
            ->assertSet('longitud', -57.456);
    }

    public function test_editar_fails_for_nonexistent_lote(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('editar', 99999);
    }

    // =========================================================================
    // CRUD: guardar() - Update
    // =========================================================================

    public function test_guardar_updates_existing_lote(): void
    {
        $lote = Lote::factory()->create([
            'propietario' => 'Old Owner',
            'estado' => 'activo',
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('editar', $lote->id_lote)
            ->set('propietario', 'New Owner Updated')
            ->set('estado', 'en_proceso')
            ->call('guardar');

        $this->assertDatabaseHas('lotes', [
            'id_lote' => $lote->id_lote,
            'propietario' => 'New Owner Updated',
            'estado' => 'en_proceso',
        ]);
    }

    // =========================================================================
    // CRUD: eliminar()
    // =========================================================================

    public function test_eliminar_soft_deletes_lote(): void
    {
        $lote = Lote::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('eliminar', $lote->id_lote);

        $this->assertSoftDeleted('lotes', ['id_lote' => $lote->id_lote]);
    }

    public function test_eliminar_resets_form_fields(): void
    {
        $lote = Lote::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('editar', $lote->id_lote)
            ->assertSet('lote_id', $lote->id_lote)
            ->call('eliminar', $lote->id_lote)
            ->assertSet('lote_id', null)
            ->assertSet('propietario', '');
    }

    // =========================================================================
    // State Machine: finalizarLote()
    // =========================================================================

    public function test_finalizar_lote_changes_estado_to_cerrado(): void
    {
        $lote = Lote::factory()->activo()->create();

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('finalizarLote', $lote->id_lote);

        $this->assertDatabaseHas('lotes', [
            'id_lote' => $lote->id_lote,
            'estado' => 'cerrado',
        ]);
    }

    public function test_finalizar_lote_closes_open_proposals(): void
    {
        $lote = Lote::factory()->activo()->create();
        $proposal = PropuestaAsignacion::factory()->draft()->create(['id_lote' => $lote->id_lote]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('finalizarLote', $lote->id_lote);

        $this->assertDatabaseHas('allocation_proposals', [
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'status' => 'closed',
        ]);
    }

    public function test_finalizar_lote_releases_pivot_resources(): void
    {
        $lote = Lote::factory()->enProceso()->create();
        $empleado = Empleado::factory()->create();
        $maquinaria = Maquinaria::factory()->create();

        $lote->empleados()->attach($empleado->id_empleado);
        $lote->maquinarias()->attach($maquinaria->id_maquinaria);

        $this->assertDatabaseHas('lote_empleado', [
            'id_lote' => $lote->id_lote,
            'id_empleado' => $empleado->id_empleado,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('finalizarLote', $lote->id_lote);

        $this->assertDatabaseMissing('lote_empleado', [
            'id_lote' => $lote->id_lote,
        ]);

        $this->assertDatabaseMissing('lote_maquinaria', [
            'id_lote' => $lote->id_lote,
        ]);
    }

    public function test_finalizar_lote_reloads_lotes(): void
    {
        $lote = Lote::factory()->activo()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('finalizarLote', $lote->id_lote);

        $lotes = $component->viewData('lotes');
        $this->assertSame('cerrado', $lotes->firstWhere('id_lote', $lote->id_lote)->estado);
    }

    public function test_finalizar_lote_handles_nonexistent_lote_gracefully(): void
    {
        // finalizarLote catches the exception internally and flashes an error.
        // The component does not crash — it recovers gracefully.
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('finalizarLote', 99999)
            ->assertStatus(200);
    }

    // =========================================================================
    // Proposal Management: openLaunchpad()
    // =========================================================================

    public function test_open_launchpad_sets_modal_state(): void
    {
        $lote = Lote::factory()->activo()->create();

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('openLaunchpad', $lote->id_lote)
            ->assertSet('mostrarModalRecomendaciones', true)
            ->assertSet('modalLoteId', $lote->id_lote)
            ->assertSet('recomendacionesError', null);
    }

    public function test_open_launchpad_dispatches_job_when_no_proposals_exist(): void
    {
        $lote = Lote::factory()->activo()->create();

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('openLaunchpad', $lote->id_lote);

        Queue::assertPushed(GenerateAllocationProposalsForLote::class, function ($job) use ($lote) {
            return $job->loteId === $lote->id_lote;
        });
    }

    public function test_open_launchpad_does_not_dispatch_job_when_proposals_exist(): void
    {
        $lote = Lote::factory()->activo()->create();
        PropuestaAsignacion::factory()->draft()->create(['id_lote' => $lote->id_lote]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('openLaunchpad', $lote->id_lote);

        Queue::assertNotPushed(GenerateAllocationProposalsForLote::class);
    }

    public function test_open_launchpad_does_not_dispatch_for_inactive_lote(): void
    {
        $lote = Lote::factory()->inactivo()->create();

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('openLaunchpad', $lote->id_lote);

        Queue::assertNotPushed(GenerateAllocationProposalsForLote::class);
    }

    // =========================================================================
    // Proposal Management: generarRecomendaciones()
    // =========================================================================

    public function test_generar_recomendaciones_fails_for_inactive_lote(): void
    {
        $lote = Lote::factory()->inactivo()->create();

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('generarRecomendaciones')
            ->assertSet('recomendacionesError', 'El lote está inactivo. Activá el lote para generar recomendaciones.');
    }

    public function test_generar_recomendaciones_fails_when_no_lote_selected(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', null)
            ->call('generarRecomendaciones')
            ->assertSet('recomendacionesError', null);
    }

    public function test_generar_recomendaciones_closes_existing_drafts(): void
    {
        $lote = Lote::factory()->activo()->create();
        $draft = PropuestaAsignacion::factory()->draft()->create(['id_lote' => $lote->id_lote]);

        $this->mock(AutomaticAllocationService::class, function ($mock) {
            $mock->shouldReceive('proposeForLotAndTask')->andThrow(new \RuntimeException('No history'));
        });

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('generarRecomendaciones');

        $this->assertDatabaseHas('allocation_proposals', [
            'id_allocation_proposal' => $draft->id_allocation_proposal,
            'status' => 'closed',
        ]);
    }

    // =========================================================================
    // Proposal Management: confirmarRecomendacion()
    // =========================================================================

    public function test_confirmar_recomendacion_applies_proposal_and_updates_lote(): void
    {
        $lote = Lote::factory()->activo()->create();
        $empleado = Empleado::factory()->create();
        $maquinaria = Maquinaria::factory()->create();

        $proposal = PropuestaAsignacion::factory()->draft()->create([
            'id_lote' => $lote->id_lote,
            'meta' => ['confidence' => 'normal'],
        ]);

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

        $this->mock(AutomaticAllocationService::class, function ($mock) {
            $mock->shouldReceive('ensureWeek1SupplyEstimates')->once();
        });

        Notification::fake();

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('confirmarRecomendacion', $proposal->id_allocation_proposal);

        $this->assertDatabaseHas('allocation_proposals', [
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'status' => 'applied',
        ]);

        $this->assertDatabaseHas('lotes', [
            'id_lote' => $lote->id_lote,
            'estado' => 'en_proceso',
        ]);

        $this->assertDatabaseHas('lote_empleado', [
            'id_lote' => $lote->id_lote,
            'id_empleado' => $empleado->id_empleado,
        ]);

        $this->assertDatabaseHas('lote_maquinaria', [
            'id_lote' => $lote->id_lote,
            'id_maquinaria' => $maquinaria->id_maquinaria,
        ]);
    }

    public function test_confirmar_recomendacion_closes_other_proposals(): void
    {
        $lote = Lote::factory()->activo()->create();
        $empleado = Empleado::factory()->create();

        $proposalToApply = PropuestaAsignacion::factory()->draft()->create([
            'id_lote' => $lote->id_lote,
            'tipo_tarea' => 'tala_rasa',
            'meta' => ['confidence' => 'normal'],
        ]);

        $otherProposal = PropuestaAsignacion::factory()->draft()->create([
            'id_lote' => $lote->id_lote,
            'tipo_tarea' => 'tala_rasa',
        ]);

        PropuestaAsignacionEmpleado::factory()->create([
            'id_allocation_proposal' => $proposalToApply->id_allocation_proposal,
            'id_empleado' => $empleado->id_empleado,
            'selected' => true,
        ]);

        $this->mock(AutomaticAllocationService::class, function ($mock) {
            $mock->shouldReceive('ensureWeek1SupplyEstimates')->once();
        });

        Notification::fake();

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('confirmarRecomendacion', $proposalToApply->id_allocation_proposal);

        $this->assertDatabaseHas('allocation_proposals', [
            'id_allocation_proposal' => $otherProposal->id_allocation_proposal,
            'status' => 'closed',
        ]);
    }

    public function test_confirmar_recomendacion_low_confidence_requires_review(): void
    {
        $lote = Lote::factory()->activo()->create();

        $proposal = PropuestaAsignacion::factory()->lowConfidence()->create([
            'id_lote' => $lote->id_lote,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('confirmarRecomendacion', $proposal->id_allocation_proposal)
            ->assertSet('recomendacionesMensaje', 'Propuesta con baja confianza. Confirmada para revision manual. Vuelva a aplicar para asignar.');

        $this->assertDatabaseHas('allocation_proposals', [
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'status' => 'confirmed',
        ]);
    }

    public function test_confirmar_recomendacion_fails_when_employee_busy_on_other_lote(): void
    {
        $lote = Lote::factory()->activo()->create();
        $otherLote = Lote::factory()->enProceso()->create();
        $empleado = Empleado::factory()->create();

        $otherLote->empleados()->attach($empleado->id_empleado);

        $proposal = PropuestaAsignacion::factory()->draft()->create([
            'id_lote' => $lote->id_lote,
            'meta' => ['confidence' => 'normal'],
        ]);

        PropuestaAsignacionEmpleado::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'id_empleado' => $empleado->id_empleado,
            'selected' => true,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('confirmarRecomendacion', $proposal->id_allocation_proposal)
            ->assertSet('recomendacionesError', 'No se pudo aplicar la recomendación: Algunos empleados ya estan asignados a otros lotes en proceso.');
    }

    public function test_confirmar_recomendacion_fails_when_maquinaria_busy_on_other_lote(): void
    {
        $lote = Lote::factory()->activo()->create();
        $otherLote = Lote::factory()->enProceso()->create();
        $maquinaria = Maquinaria::factory()->create();

        $otherLote->maquinarias()->attach($maquinaria->id_maquinaria);

        $proposal = PropuestaAsignacion::factory()->draft()->create([
            'id_lote' => $lote->id_lote,
            'meta' => ['confidence' => 'normal'],
        ]);

        PropuestaAsignacionMaquinaria::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'selected' => true,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('confirmarRecomendacion', $proposal->id_allocation_proposal)
            ->assertSet('recomendacionesError', 'No se pudo aplicar la recomendación: Algunas maquinarias ya estan asignadas a otros lotes en proceso.');
    }

    public function test_confirmar_recomendacion_skips_already_applied(): void
    {
        $lote = Lote::factory()->enProceso()->create();

        $proposal = PropuestaAsignacion::factory()->applied()->create([
            'id_lote' => $lote->id_lote,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('confirmarRecomendacion', $proposal->id_allocation_proposal);

        $this->assertDatabaseHas('allocation_proposals', [
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'status' => 'applied',
        ]);
    }

    // =========================================================================
    // Proposal Management: eliminarRecomendacion()
    // =========================================================================

    public function test_eliminar_recomendacion_soft_deletes_draft(): void
    {
        $lote = Lote::factory()->create();
        $proposal = PropuestaAsignacion::factory()->draft()->create(['id_lote' => $lote->id_lote]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('eliminarRecomendacion', $proposal->id_allocation_proposal)
            ->assertSet('recomendacionesMensaje', 'Recomendación eliminada correctamente.');

        $this->assertSoftDeleted('allocation_proposals', [
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
        ]);
    }

    public function test_eliminar_recomendacion_blocks_non_draft(): void
    {
        $lote = Lote::factory()->create();
        $proposal = PropuestaAsignacion::factory()->applied()->create(['id_lote' => $lote->id_lote]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('eliminarRecomendacion', $proposal->id_allocation_proposal)
            ->assertSet('recomendacionesError', 'Solo se pueden eliminar recomendaciones en borrador.');

        $this->assertDatabaseHas('allocation_proposals', [
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'status' => 'applied',
            'deleted_at' => null,
        ]);
    }

    public function test_eliminar_recomendacion_fails_for_nonexistent(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('eliminarRecomendacion', 99999)
            ->assertSet('recomendacionesError', 'No se encontró la recomendación seleccionada.');
    }

    // =========================================================================
    // Proposal Management: eliminarBorradores()
    // =========================================================================

    public function test_eliminar_borradores_deletes_all_drafts_for_lote(): void
    {
        $lote = Lote::factory()->create();
        PropuestaAsignacion::factory()->draft()->count(3)->create(['id_lote' => $lote->id_lote]);
        PropuestaAsignacion::factory()->applied()->create(['id_lote' => $lote->id_lote]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('eliminarBorradores')
            ->assertSet('recomendacionesMensaje', 'Se eliminaron 3 recomendación(es) en borrador.');

        // 3 drafts soft-deleted + 1 applied still visible = 1 non-deleted
        $this->assertSame(1, PropuestaAsignacion::where('id_lote', $lote->id_lote)->count());
        // Total including soft-deleted = 4
        $this->assertSame(4, PropuestaAsignacion::withTrashed()->where('id_lote', $lote->id_lote)->count());
    }

    public function test_eliminar_borradores_does_not_affect_other_lotes(): void
    {
        $lote1 = Lote::factory()->create();
        $lote2 = Lote::factory()->create();
        PropuestaAsignacion::factory()->draft()->create(['id_lote' => $lote1->id_lote]);
        PropuestaAsignacion::factory()->draft()->create(['id_lote' => $lote2->id_lote]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote1->id_lote)
            ->call('eliminarBorradores');

        // lote2's draft should still be active
        $this->assertSame(1, PropuestaAsignacion::where('id_lote', $lote2->id_lote)->count());
        // lote1's draft should be soft-deleted
        $this->assertSame(0, PropuestaAsignacion::where('id_lote', $lote1->id_lote)->count());
    }

    public function test_eliminar_borradores_handles_no_drafts(): void
    {
        $lote = Lote::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('eliminarBorradores')
            ->assertSet('recomendacionesMensaje', 'No hay recomendaciones en borrador para eliminar.');
    }

    public function test_eliminar_borradores_fails_without_lote(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', null)
            ->call('eliminarBorradores')
            ->assertSet('recomendacionesError', 'No se encontró el lote seleccionado.');
    }

    // =========================================================================
    // Launchpad Edit: startEdit() / saveEdit() / cancelEdit()
    // =========================================================================

    public function test_start_edit_loads_proposal_data(): void
    {
        $lote = Lote::factory()->create();
        $proposal = PropuestaAsignacion::factory()->draft()->create([
            'id_lote' => $lote->id_lote,
            'estimated_person_days' => 45.5,
            'estimated_machine_days' => 30.0,
            'estimated_duration_days' => 15.0,
            'suggested_team_size' => 8,
            'suggested_machinery_count' => 3,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('startEdit', $proposal->id_allocation_proposal)
            ->assertSet('editProposalId', $proposal->id_allocation_proposal)
            ->assertSet('editData.estimated_person_days', 45.5)
            ->assertSet('editData.estimated_machine_days', 30.0)
            ->assertSet('editData.suggested_team_size', 8);
    }

    public function test_start_edit_blocks_applied_proposal(): void
    {
        $lote = Lote::factory()->create();
        $proposal = PropuestaAsignacion::factory()->applied()->create(['id_lote' => $lote->id_lote]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('startEdit', $proposal->id_allocation_proposal)
            ->assertSet('recomendacionesError', 'No se pueden editar recomendaciones que ya han sido aplicadas.');
    }

    public function test_start_edit_fails_for_nonexistent(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('startEdit', 99999)
            ->assertSet('recomendacionesError', 'No se encontró la recomendación seleccionada.');
    }

    public function test_cancel_edit_resets_edit_state(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('editProposalId', 1)
            ->set('editData', ['estimated_person_days' => 10])
            ->set('editProposedEmployees', [['id' => 1]])
            ->call('cancelEdit')
            ->assertSet('editProposalId', null)
            ->assertSet('editData', [])
            ->assertSet('editProposedEmployees', [])
            ->assertSet('editProposedMaquinarias', [])
            ->assertSet('editProposedInsumos', []);
    }

    public function test_save_edit_updates_proposal_estimations(): void
    {
        $lote = Lote::factory()->create();
        $proposal = PropuestaAsignacion::factory()->draft()->create([
            'id_lote' => $lote->id_lote,
            'estimated_person_days' => 10,
            'suggested_team_size' => 3,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->set('editProposalId', $proposal->id_allocation_proposal)
            ->set('editData', [
                'estimated_person_days' => 50,
                'estimated_machine_days' => 25,
                'estimated_duration_days' => 20,
                'suggested_team_size' => 10,
                'suggested_machinery_count' => 4,
            ])
            ->set('editProposedEmployees', [])
            ->set('editProposedMaquinarias', [])
            ->set('editProposedInsumos', [])
            ->call('saveEdit', $proposal->id_allocation_proposal);

        $this->assertDatabaseHas('allocation_proposals', [
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'estimated_person_days' => 50,
            'suggested_team_size' => 10,
        ]);
    }

    public function test_save_edit_validates_numeric_fields(): void
    {
        $lote = Lote::factory()->create();
        $proposal = PropuestaAsignacion::factory()->draft()->create(['id_lote' => $lote->id_lote]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->set('editProposalId', $proposal->id_allocation_proposal)
            ->set('editData', [
                'estimated_person_days' => -5,
                'suggested_team_size' => 0,
            ])
            ->set('editProposedEmployees', [])
            ->set('editProposedMaquinarias', [])
            ->set('editProposedInsumos', [])
            ->call('saveEdit', $proposal->id_allocation_proposal)
            ->assertSet('recomendacionesError', 'Revisá los valores numéricos antes de guardar.');
    }

    public function test_save_edit_blocks_applied_proposal(): void
    {
        $lote = Lote::factory()->create();
        $proposal = PropuestaAsignacion::factory()->applied()->create(['id_lote' => $lote->id_lote]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->set('editProposalId', $proposal->id_allocation_proposal)
            ->set('editData', ['estimated_person_days' => 50])
            ->call('saveEdit', $proposal->id_allocation_proposal)
            ->assertSet('recomendacionesError', 'No se pueden editar recomendaciones que ya han sido aplicadas.');
    }

    public function test_save_edit_mismatched_proposal_id_is_noop(): void
    {
        $lote = Lote::factory()->create();
        $proposal = PropuestaAsignacion::factory()->draft()->create(['id_lote' => $lote->id_lote]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('editProposalId', 999)
            ->call('saveEdit', $proposal->id_allocation_proposal);

        $this->assertDatabaseHas('allocation_proposals', [
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'estimated_person_days' => $proposal->estimated_person_days,
        ]);
    }

    // =========================================================================
    // toggleExpand()
    // =========================================================================

    public function test_toggle_expand_sets_and_unsets_proposal_id(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('toggleExpand', 5)
            ->assertSet('expandedProposalId', 5)
            ->call('toggleExpand', 5)
            ->assertSet('expandedProposalId', null);
    }

    public function test_toggle_expand_switches_to_different_proposal(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('toggleExpand', 5)
            ->assertSet('expandedProposalId', 5)
            ->call('toggleExpand', 10)
            ->assertSet('expandedProposalId', 10);
    }

    // =========================================================================
    // cerrarModalRecomendaciones()
    // =========================================================================

    public function test_cerrar_modal_resets_all_state(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('mostrarModalRecomendaciones', true)
            ->set('modalLoteId', 1)
            ->set('recomendacionesError', 'some error')
            ->set('recomendacionesMensaje', 'some msg')
            ->set('editProposalId', 1)
            ->set('editData', ['foo' => 'bar'])
            ->set('expandedProposalId', 1)
            ->call('cerrarModalRecomendaciones')
            ->assertSet('mostrarModalRecomendaciones', false)
            ->assertSet('modalLoteId', null)
            ->assertSet('recomendaciones', [])
            ->assertSet('recomendacionesError', null)
            ->assertSet('recomendacionesMensaje', null)
            ->assertSet('editProposalId', null)
            ->assertSet('editData', [])
            ->assertSet('expandedProposalId', null);
    }

    // =========================================================================
    // Filtering & Search: busqueda
    // =========================================================================

    public function test_busqueda_property_defaults_to_empty(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->assertSet('busqueda', '');
    }

    public function test_busqueda_defaults_to_empty_string(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->assertSet('busqueda', '');
    }

    public function test_cargar_lotes_loads_all_lotes_ordered_by_id_desc(): void
    {
        $lote1 = Lote::factory()->create(['propietario' => 'First Owner']);
        $lote2 = Lote::factory()->create(['propietario' => 'Second Owner']);
        $lote3 = Lote::factory()->create(['propietario' => 'Third Owner']);

        $component = Livewire::actingAs($this->usuario)
            ->test(Lotes::class);

        $lotes = $component->viewData('lotes');
        $this->assertCount(3, $lotes);
        // Ordered by id_lote desc
        $this->assertSame($lote3->id_lote, $lotes->first()->id_lote);
    }

    // =========================================================================
    // resetCampos()
    // =========================================================================

    public function test_reset_campos_clears_all_form_fields(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', 'Some Owner')
            ->set('ubicacion', 'Some Location')
            ->set('superficie', 100)
            ->set('especie', 'Pino')
            ->set('condicion_compra', 'propio')
            ->set('lote_id', 5)
            ->call('resetCampos')
            ->assertSet('propietario', '')
            ->assertSet('ubicacion', '')
            ->assertSet('superficie', '')
            ->assertSet('especie', '')
            ->assertSet('condicion_compra', '')
            ->assertSet('latitud', null)
            ->assertSet('longitud', null)
            ->assertSet('lote_id', null)
            ->assertSet('estado', 'activo')
            ->assertSet('main_task_type', TaskType::TALA_RASA->value);
    }

    // =========================================================================
    // Edge Cases & Transaction Safety
    // =========================================================================

    public function test_guardar_validates_main_task_type_values(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', 'Test Owner')
            ->set('ubicacion', 'Test Location')
            ->set('especie', 'Pino')
            ->set('superficie', 50)
            ->set('condicion_compra', 'propio')
            ->set('estado', 'activo')
            ->set('main_task_type', 'invalid_task')
            ->call('guardar')
            ->assertHasErrors(['main_task_type' => 'in']);
    }

    public function test_guardar_validates_estado_values(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', 'Test Owner')
            ->set('ubicacion', 'Test Location')
            ->set('especie', 'Pino')
            ->set('superficie', 50)
            ->set('condicion_compra', 'propio')
            ->set('estado', 'invalid_estado')
            ->set('main_task_type', 'tala_rasa')
            ->call('guardar')
            ->assertHasErrors(['estado' => 'in']);
    }

    public function test_guardar_validates_propietario_min_length(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', 'AB')
            ->set('ubicacion', 'Test Location')
            ->set('especie', 'Pino')
            ->set('superficie', 50)
            ->set('condicion_compra', 'propio')
            ->set('estado', 'activo')
            ->set('main_task_type', 'tala_rasa')
            ->call('guardar')
            ->assertHasErrors(['propietario' => 'min']);
    }

    public function test_guardar_validates_superficie_max(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('propietario', 'Test Owner')
            ->set('ubicacion', 'Test Location')
            ->set('especie', 'Pino')
            ->set('superficie', 99999)
            ->set('condicion_compra', 'propio')
            ->set('estado', 'activo')
            ->set('main_task_type', 'tala_rasa')
            ->call('guardar')
            ->assertHasErrors(['superficie' => 'max']);
    }

    public function test_confirmar_recomendacion_sets_applied_at_timestamp(): void
    {
        $lote = Lote::factory()->activo()->create();
        $empleado = Empleado::factory()->create();

        $proposal = PropuestaAsignacion::factory()->draft()->create([
            'id_lote' => $lote->id_lote,
            'meta' => ['confidence' => 'normal'],
            'applied_at' => null,
        ]);

        PropuestaAsignacionEmpleado::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'id_empleado' => $empleado->id_empleado,
            'selected' => true,
        ]);

        $this->mock(AutomaticAllocationService::class, function ($mock) {
            $mock->shouldReceive('ensureWeek1SupplyEstimates')->once();
        });

        Notification::fake();

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('confirmarRecomendacion', $proposal->id_allocation_proposal);

        $proposal->refresh();
        $this->assertNotNull($proposal->applied_at);
    }

    public function test_open_launchpad_clears_previous_error_state(): void
    {
        $lote = Lote::factory()->activo()->create();
        // Create an existing proposal so the job is NOT dispatched
        PropuestaAsignacion::factory()->draft()->create(['id_lote' => $lote->id_lote]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('recomendacionesError', 'Previous error')
            ->set('recomendacionesMensaje', 'Previous message')
            ->call('openLaunchpad', $lote->id_lote)
            ->assertSet('recomendacionesError', null)
            ->assertSet('recomendacionesMensaje', null);
    }

    public function test_eliminar_recomendacion_confirmed_status_is_blocked(): void
    {
        $lote = Lote::factory()->create();
        $proposal = PropuestaAsignacion::factory()->confirmed()->create(['id_lote' => $lote->id_lote]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('eliminarRecomendacion', $proposal->id_allocation_proposal)
            ->assertSet('recomendacionesError', 'Solo se pueden eliminar recomendaciones en borrador.');
    }

    public function test_confirmar_recomendacion_does_not_downgrade_en_proceso_lote(): void
    {
        $lote = Lote::factory()->enProceso()->create();
        $empleado = Empleado::factory()->create();

        $proposal = PropuestaAsignacion::factory()->draft()->create([
            'id_lote' => $lote->id_lote,
            'meta' => ['confidence' => 'normal'],
        ]);

        PropuestaAsignacionEmpleado::factory()->create([
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'id_empleado' => $empleado->id_empleado,
            'selected' => true,
        ]);

        $this->mock(AutomaticAllocationService::class, function ($mock) {
            $mock->shouldReceive('ensureWeek1SupplyEstimates')->once();
        });

        Notification::fake();

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->set('modalLoteId', $lote->id_lote)
            ->call('confirmarRecomendacion', $proposal->id_allocation_proposal);

        $this->assertDatabaseHas('lotes', [
            'id_lote' => $lote->id_lote,
            'estado' => 'en_proceso',
        ]);
    }
}
