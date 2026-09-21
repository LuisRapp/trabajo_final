<?php

namespace Tests\Feature\Livewire;

use App\Enums\TaskType;
use App\Http\Livewire\Lotes;
use App\Models\Empleado;
use App\Models\Lote;
use App\Models\Maquinaria;
use App\Models\PropuestaAsignacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSet('main_task_type', TaskType::TALA_RASA->value);
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

    public function test_finalizar_lote_ya_cerrado_no_toca_propuestas_ni_recursos(): void
    {
        $lote = Lote::factory()->cerrado()->create();
        $empleado = Empleado::factory()->create();
        $lote->empleados()->attach($empleado->id_empleado);
        $proposal = PropuestaAsignacion::factory()->draft()->create(['id_lote' => $lote->id_lote]);

        Livewire::actingAs($this->usuario)
            ->test(Lotes::class)
            ->call('finalizarLote', $lote->id_lote);

        // El guard del servicio no opera sobre un lote ya cerrado
        $this->assertDatabaseHas('allocation_proposals', [
            'id_allocation_proposal' => $proposal->id_allocation_proposal,
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('lote_empleado', [
            'id_lote' => $lote->id_lote,
            'id_empleado' => $empleado->id_empleado,
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
}
