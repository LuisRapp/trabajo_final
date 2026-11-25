<?php

use App\Http\Livewire\LotePlanificacionTareas;
use App\Models\Lote;
use Livewire\Livewire;

test('la pantalla de tareas muestra el lote arriba y no el texto tecnico', function () {
    // El usuario marco que la explicacion ("esto alimenta el historico y dispara
    // propuestas") y el tip de superficies estaban fuera del nivel del usuario.
    $usuario = usuarioConPermisos(['ver-lotes', 'editar-lotes']);

    $lote = Lote::factory()->create([
        'estado' => 'activo',
        'especie' => 'Pino sp',
        'ubicacion' => 'San Juan',
        'superficie' => 44,
    ]);

    Livewire::actingAs($usuario)
        ->test(LotePlanificacionTareas::class, ['loteId' => $lote->id_lote])
        ->assertSee('Tareas del Lote #'.$lote->id_lote)
        ->assertSee('Pino sp')
        ->assertSee('Actividades planificadas')
        ->assertDontSee('alimenta el histórico')
        ->assertDontSee('para dividir 5/5');
});

test('propone como primera tarea la tarea principal del lote', function () {
    $usuario = usuarioConPermisos(['ver-lotes', 'editar-lotes']);

    $lote = Lote::factory()->create([
        'estado' => 'activo',
        'main_task_type' => \App\Enums\TaskType::RALEO->value,
        'superficie' => 30,
    ]);

    Livewire::actingAs($usuario)
        ->test(LotePlanificacionTareas::class, ['loteId' => $lote->id_lote])
        ->assertSet('tareas.0.tipo_tarea', \App\Enums\TaskType::RALEO->value);
});

test('un lote cerrado no permite editar sus tareas', function () {
    $usuario = usuarioConPermisos(['ver-lotes', 'editar-lotes']);
    $lote = Lote::factory()->create(['estado' => 'cerrado']);

    $this->actingAs($usuario)
        ->get(route('lotes.tareas', ['loteId' => $lote->id_lote]))
        ->assertForbidden();
});
