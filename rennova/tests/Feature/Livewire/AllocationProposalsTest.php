<?php

use App\Enums\TaskType;
use App\Http\Livewire\AllocationProposals;
use App\Models\Empleado;
use App\Models\Lote;
use App\Models\LoteTarea;
use App\Models\Maquinaria;
use App\Models\PropuestaAsignacion;
use App\Models\PropuestaAsignacionEmpleado;
use App\Models\PropuestaAsignacionMaquinaria;
use App\Models\Usuario;
use App\Services\AutomaticAllocationService;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();

    $this->usuario = Usuario::factory()->create();
});

// =========================================================================
// generarAhora()
// =========================================================================

test('generarAhora genera propuestas para un lote con tareas planificadas', function () {
    $this->mock(AutomaticAllocationService::class)
        ->shouldIgnoreMissing()
        ->shouldReceive('proposeForLoteTarea')
        ->once()
        ->andReturnUsing(function (LoteTarea $tarea) {
            return PropuestaAsignacion::factory()->for($tarea->lote()->first(), 'lote')->create([
                'id_lote_tarea' => $tarea->id_lote_tarea,
                'tipo_tarea' => $tarea->tipo_tarea,
                'status' => 'draft',
            ]);
        });

    $lote = Lote::factory()->create([
        'estado' => 'activo',
        'main_task_type' => TaskType::TALA_RASA->value,
    ]);
    $tarea = LoteTarea::factory()->create([
        'id_lote' => $lote->id_lote,
        'tipo_tarea' => TaskType::TALA_RASA->value,
        'estado' => 'planificada',
    ]);

    $componente = Livewire::actingAs($this->usuario)
        ->test(AllocationProposals::class, ['loteId' => $lote->id_lote])
        ->call('generarAhora');

    $propuesta = PropuestaAsignacion::query()
        ->where('id_lote', $lote->id_lote)
        ->where('status', 'draft')
        ->first();

    $componente
        ->assertSee('Propuestas generadas correctamente.')
        ->assertSet('selected_proposal_id', $propuesta->id_allocation_proposal);

    expect($propuesta)->not->toBeNull()
        ->and($propuesta->status)->toBe('draft')
        ->and($propuesta->id_lote_tarea)->toBe($tarea->id_lote_tarea);
});

test('generarAhora sin lote seleccionado muestra error', function () {
    Livewire::actingAs($this->usuario)
        ->test(AllocationProposals::class)
        ->call('generarAhora')
        ->assertSee('Seleccione un lote para generar propuestas.');
});

test('generarAhora con lote inactivo muestra error', function () {
    $loteInactivo = Lote::factory()->inactivo()->create();

    Livewire::actingAs($this->usuario)
        ->test(AllocationProposals::class, ['loteId' => $loteInactivo->id_lote])
        ->call('generarAhora')
        ->assertSee('El lote está inactivo. Activá el lote para generar propuestas de asignación.');
});

// =========================================================================
// guardarSeleccion()
// =========================================================================

test('guardarSeleccion persiste los flags selected', function () {
    $lote = Lote::factory()->create(['estado' => 'activo']);
    $propuesta = PropuestaAsignacion::factory()->for($lote, 'lote')->create(['status' => 'draft']);

    $emp1 = PropuestaAsignacionEmpleado::factory()->create([
        'id_allocation_proposal' => $propuesta->id_allocation_proposal,
        'selected' => true,
    ]);
    $emp2 = PropuestaAsignacionEmpleado::factory()->create([
        'id_allocation_proposal' => $propuesta->id_allocation_proposal,
        'selected' => true,
    ]);
    $maq = PropuestaAsignacionMaquinaria::factory()->create([
        'id_allocation_proposal' => $propuesta->id_allocation_proposal,
        'selected' => true,
    ]);

    Livewire::actingAs($this->usuario)
        ->test(AllocationProposals::class, ['loteId' => $lote->id_lote])
        ->set('employeeSelected.'.$emp2->id_allocation_proposal_employee, false)
        ->set('maquinariaSelected.'.$maq->id_allocation_proposal_maquinaria, false)
        ->call('guardarSeleccion')
        ->assertSee('Selección guardada correctamente.');

    expect($emp1->refresh()->selected)->toBeTrue()
        ->and($emp2->refresh()->selected)->toBeFalse()
        ->and($maq->refresh()->selected)->toBeFalse();
});

// =========================================================================
// confirmar() — confianza normal
// =========================================================================

test('confirmar aplica la propuesta de confianza normal y sincroniza el lote', function () {
    $lote = Lote::factory()->create(['estado' => 'activo']);
    $propuesta = PropuestaAsignacion::factory()->for($lote, 'lote')->create(['status' => 'draft']);
    $empleado = Empleado::factory()->create();
    $maquinaria = Maquinaria::factory()->create();

    PropuestaAsignacionEmpleado::factory()->create([
        'id_allocation_proposal' => $propuesta->id_allocation_proposal,
        'id_empleado' => $empleado->id_empleado,
        'selected' => true,
    ]);
    PropuestaAsignacionMaquinaria::factory()->create([
        'id_allocation_proposal' => $propuesta->id_allocation_proposal,
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'selected' => true,
    ]);

    $this->mock(AutomaticAllocationService::class)->shouldIgnoreMissing();

    Livewire::actingAs($this->usuario)
        ->test(AllocationProposals::class, ['loteId' => $lote->id_lote])
        ->call('confirmar')
        ->assertSee('Propuesta confirmada y aplicada al lote.');

    expect($propuesta->refresh()->status)->toBe('applied');
    expect($lote->refresh()->estado)->toBe('en_proceso');

    $this->assertDatabaseHas('lote_empleado', [
        'id_lote' => $lote->id_lote,
        'id_empleado' => $empleado->id_empleado,
    ]);
    $this->assertDatabaseHas('lote_maquinaria', [
        'id_lote' => $lote->id_lote,
        'id_maquinaria' => $maquinaria->id_maquinaria,
    ]);
});

// =========================================================================
// confirmar() — baja confianza
// =========================================================================

test('propuesta de baja confianza se confirma sin aplicar hasta volver a aplicar', function () {
    $lote = Lote::factory()->create(['estado' => 'activo']);
    $propuesta = PropuestaAsignacion::factory()->lowConfidence()->for($lote, 'lote')->create(['status' => 'draft']);

    $this->mock(AutomaticAllocationService::class)->shouldIgnoreMissing();

    $componente = Livewire::actingAs($this->usuario)
        ->test(AllocationProposals::class, ['loteId' => $lote->id_lote])
        ->call('confirmar');

    $componente->assertSee('Propuesta con baja confianza. Confirmada para revision manual. Vuelva a aplicar para asignar.');

    expect($propuesta->refresh()->status)->toBe('confirmed')
        ->and($propuesta->meta['review_required'])->toBeTrue()
        ->and($propuesta->status)->not->toBe('applied');
    expect($lote->refresh()->estado)->not->toBe('en_proceso');

    $componente->call('confirmar');

    expect($propuesta->refresh()->status)->toBe('applied');
});

// =========================================================================
// confirmar() — conflictos
// =========================================================================

test('confirmar con empleado ocupado en otro lote en proceso da error y no aplica', function () {
    $loteActual = Lote::factory()->create(['estado' => 'activo']);
    $loteOcupado = Lote::factory()->enProceso()->create();
    $empleado = Empleado::factory()->create();
    $loteOcupado->empleados()->attach($empleado->id_empleado);

    $propuesta = PropuestaAsignacion::factory()->for($loteActual, 'lote')->create(['status' => 'draft']);
    PropuestaAsignacionEmpleado::factory()->create([
        'id_allocation_proposal' => $propuesta->id_allocation_proposal,
        'id_empleado' => $empleado->id_empleado,
        'selected' => true,
    ]);

    $this->mock(AutomaticAllocationService::class)->shouldIgnoreMissing();

    Livewire::actingAs($this->usuario)
        ->test(AllocationProposals::class, ['loteId' => $loteActual->id_lote])
        ->call('confirmar')
        ->assertSee('No se pudo aplicar la propuesta. Intente nuevamente o contacte al administrador.');

    expect($propuesta->refresh()->status)->not->toBe('applied');
    expect($loteActual->refresh()->estado)->not->toBe('en_proceso');

    $this->assertDatabaseMissing('lote_empleado', [
        'id_lote' => $loteActual->id_lote,
        'id_empleado' => $empleado->id_empleado,
    ]);
});

test('confirmar con maquinaria ocupada en otro lote en proceso da error y no aplica', function () {
    $loteActual = Lote::factory()->create(['estado' => 'activo']);
    $loteOcupado = Lote::factory()->enProceso()->create();
    $maquinaria = Maquinaria::factory()->create();
    $loteOcupado->maquinarias()->attach($maquinaria->id_maquinaria);

    $propuesta = PropuestaAsignacion::factory()->for($loteActual, 'lote')->create(['status' => 'draft']);
    PropuestaAsignacionMaquinaria::factory()->create([
        'id_allocation_proposal' => $propuesta->id_allocation_proposal,
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'selected' => true,
    ]);

    $this->mock(AutomaticAllocationService::class)->shouldIgnoreMissing();

    Livewire::actingAs($this->usuario)
        ->test(AllocationProposals::class, ['loteId' => $loteActual->id_lote])
        ->call('confirmar')
        ->assertSee('No se pudo aplicar la propuesta. Intente nuevamente o contacte al administrador.');

    expect($propuesta->refresh()->status)->not->toBe('applied');
    expect($loteActual->refresh()->estado)->not->toBe('en_proceso');

    $this->assertDatabaseMissing('lote_maquinaria', [
        'id_lote' => $loteActual->id_lote,
        'id_maquinaria' => $maquinaria->id_maquinaria,
    ]);
});

// =========================================================================
// confirmar() — cierre de propuestas competidoras
// =========================================================================

test('confirmar cierra otras propuestas competidoras del mismo lote', function () {
    $lote = Lote::factory()->create([
        'estado' => 'activo',
        'main_task_type' => TaskType::TALA_RASA->value,
    ]);
    $propuesta1 = PropuestaAsignacion::factory()->for($lote, 'lote')->create([
        'status' => 'draft',
        'tipo_tarea' => TaskType::TALA_RASA->value,
        'id_lote_tarea' => null,
    ]);
    $propuesta2 = PropuestaAsignacion::factory()->for($lote, 'lote')->create([
        'status' => 'draft',
        'tipo_tarea' => TaskType::TALA_RASA->value,
        'id_lote_tarea' => null,
    ]);
    PropuestaAsignacionEmpleado::factory()->create([
        'id_allocation_proposal' => $propuesta2->id_allocation_proposal,
        'selected' => true,
    ]);

    $this->mock(AutomaticAllocationService::class)->shouldIgnoreMissing();

    Livewire::actingAs($this->usuario)
        ->test(AllocationProposals::class, ['loteId' => $lote->id_lote])
        ->call('confirmar')
        ->assertSee('Propuesta confirmada y aplicada al lote.');

    expect($propuesta1->refresh()->status)->toBe('closed')
        ->and($propuesta2->refresh()->status)->toBe('applied');
});

test('confirmar no cierra propuestas de otros lotes', function () {
    $lote1 = Lote::factory()->create(['estado' => 'activo']);
    $lote2 = Lote::factory()->create(['estado' => 'activo']);
    $propuesta1 = PropuestaAsignacion::factory()->for($lote1, 'lote')->create(['status' => 'draft']);
    $propuesta2 = PropuestaAsignacion::factory()->for($lote2, 'lote')->create(['status' => 'draft']);

    $this->mock(AutomaticAllocationService::class)->shouldIgnoreMissing();

    Livewire::actingAs($this->usuario)
        ->test(AllocationProposals::class, ['loteId' => $lote1->id_lote])
        ->call('confirmar');

    expect($propuesta1->refresh()->status)->toBe('applied')
        ->and($propuesta2->refresh()->status)->toBe('draft');
});

// =========================================================================
// confirmar() — idempotencia
// =========================================================================

test('confirmar es idempotente sobre una propuesta ya aplicada', function () {
    $lote = Lote::factory()->enProceso()->create();
    $propuesta = PropuestaAsignacion::factory()->applied()->for($lote, 'lote')->create();

    $this->mock(AutomaticAllocationService::class)->shouldIgnoreMissing();

    Livewire::actingAs($this->usuario)
        ->test(AllocationProposals::class, ['loteId' => $lote->id_lote])
        ->call('confirmar');

    expect($propuesta->refresh()->status)->toBe('applied');
    expect($lote->refresh()->estado)->toBe('en_proceso');
});
