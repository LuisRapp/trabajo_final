<?php

use App\Http\Livewire\EstadoProcesos;
use App\Models\Usuario;
use Livewire\Livewire;

test('usuario autenticado puede renderizar el estado de procesos', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(EstadoProcesos::class)
        ->assertStatus(200)
        ->assertSet('procesos', fn ($v) => is_array($v))
        ->assertSet('climaPorLote', fn ($v) => is_array($v))
        ->assertSet('cola', fn ($v) => is_array($v));
});
