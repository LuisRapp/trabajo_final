<?php

use App\Http\Livewire\Cargas;
use App\Models\Chofer;
use App\Models\Cliente;
use App\Models\Lote;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->usuario = usuarioConPermisos(['ver-cargas', 'crear-cargas', 'editar-cargas', 'eliminar-cargas']);
});

test('el formulario de cargas ofrece los lotes con el combobox', function () {
    $lote = Lote::factory()->create(['propietario' => 'Lote En Proceso', 'estado' => 'en_proceso']);

    Livewire::actingAs($this->usuario)
        ->test(Cargas::class)
        ->set('tab_activo', 'nuevo')
        ->assertSee('Lote En Proceso')
        ->assertSeeHtml("\$wire.set('id_lote'");
});

test('elegir el cliente deja solo sus choferes', function () {
    $clienteA = Cliente::factory()->create(['razon_social' => 'Cliente A']);
    $clienteB = Cliente::factory()->create(['razon_social' => 'Cliente B']);

    Chofer::create(['id_cliente' => $clienteA->id_cliente, 'nombre' => 'Propio', 'apellido' => 'ChoferA', 'dni' => '11111111']);
    Chofer::create(['id_cliente' => $clienteB->id_cliente, 'nombre' => 'Ajeno', 'apellido' => 'ChoferB', 'dni' => '22222222']);

    Livewire::actingAs($this->usuario)
        ->test(Cargas::class)
        ->set('tab_activo', 'nuevo')
        ->set('id_cliente', $clienteA->id_cliente)
        ->assertSee('ChoferA')
        ->assertDontSee('ChoferB');
});

test('el peso neto se calcula con bruto menos tara', function () {
    Livewire::actingAs($this->usuario)
        ->test(Cargas::class)
        ->set('peso_bruto', 1000)
        ->set('tara', 250)
        ->assertSet('peso_neto', 750.0);
});
