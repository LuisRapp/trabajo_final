<?php

use App\Enums\TaskType;
use App\Http\Livewire\FichaLote;
use App\Models\Empleado;
use App\Models\Lote;
use App\Models\LoteTarea;
use App\Models\Maquinaria;
use Livewire\Livewire;

test('sin tareas propone planificarlas', function () {
    $usuario = usuarioConPermisos(['ver-lotes', 'editar-lotes']);
    $lote = Lote::factory()->create([
        'estado' => 'activo',
        'superficie' => 44,
        'main_task_type' => \App\Enums\TaskType::TALA_RASA->value,
    ]);

    Livewire::actingAs($usuario)
        ->test(FichaLote::class, ['loteId' => $lote->id_lote])
        ->assertSee('Paso 2: planificá las tareas')
        ->assertSee('Planificar tareas')
        ->assertSee('Todavía no hay tareas planificadas')
        // La tarea elegida al crear el lote tiene que verse en la ficha.
        ->assertSee('Tala Rasa')
        ->assertSee('La tarea principal es «Tala Rasa»');
});

test('con tareas y sin recursos propone asignarlos', function () {
    $usuario = usuarioConPermisos(['ver-lotes', 'editar-lotes']);
    $lote = Lote::factory()->create(['estado' => 'activo']);
    LoteTarea::factory()->create([
        'id_lote' => $lote->id_lote,
        'tipo_tarea' => TaskType::TALA_RASA->value,
        'estado' => 'planificada',
    ]);

    Livewire::actingAs($usuario)
        ->test(FichaLote::class, ['loteId' => $lote->id_lote])
        ->assertSee('Paso 3: asigná los recursos')
        ->assertSee('Ver sugerencias')
        ->assertSee('Todavía no hay recursos asignados');
});

test('con recursos muestra quienes trabajan y permite finalizar', function () {
    $usuario = usuarioConPermisos(['ver-lotes', 'editar-lotes']);
    $lote = Lote::factory()->create(['estado' => 'en_proceso']);
    $empleado = Empleado::factory()->create(['apellido' => 'Rapp', 'nombre' => 'Luis']);
    $maquinaria = Maquinaria::factory()->create(['modelo' => 'Fiat 800']);

    $lote->empleados()->attach($empleado->id_empleado);
    $lote->maquinarias()->attach($maquinaria->id_maquinaria);

    Livewire::actingAs($usuario)
        ->test(FichaLote::class, ['loteId' => $lote->id_lote])
        ->assertSee('El lote está en marcha')
        ->assertSee('Rapp, Luis')
        ->assertSee('Fiat 800')
        ->assertSee('Finalizar lote');
});

test('un lote cerrado no ofrece acciones de escritura', function () {
    $usuario = usuarioConPermisos(['ver-lotes', 'editar-lotes']);
    $lote = Lote::factory()->create(['estado' => 'cerrado']);

    Livewire::actingAs($usuario)
        ->test(FichaLote::class, ['loteId' => $lote->id_lote])
        ->assertSee('Lote cerrado')
        ->assertDontSee('Finalizar lote')
        ->assertDontSee('Planificar tareas');
});

test('la ficha de un lote inexistente da 404', function () {
    $usuario = usuarioConPermisos(['ver-lotes']);

    $this->actingAs($usuario)
        ->get(route('lotes.ficha', ['loteId' => 99999]))
        ->assertNotFound();
});

test('finalizar el lote vuelve al listado', function () {
    $usuario = usuarioConPermisos(['ver-lotes', 'editar-lotes']);
    $lote = Lote::factory()->create(['estado' => 'en_proceso']);

    Livewire::actingAs($usuario)
        ->test(FichaLote::class, ['loteId' => $lote->id_lote])
        ->call('finalizarLote')
        ->assertRedirect(route('lotes.index'));
});
