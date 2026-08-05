<?php

use App\Models\Carga;
use App\Models\Cliente;
use App\Models\Lote;
use Illuminate\Support\Facades\DB;

// =========================================================================
// Schema verification (post-migration state)
// =========================================================================

test('cargas table has id_cliente column', function () {
    $columns = DB::select("PRAGMA table_info('cargas')");
    $columnNames = collect($columns)->pluck('name')->toArray();

    expect($columnNames)->toContain('id_cliente');
});

test('cargas table no longer has destino column', function () {
    $columns = DB::select("PRAGMA table_info('cargas')");
    $columnNames = collect($columns)->pluck('name')->toArray();

    expect($columnNames)->not->toContain('destino');
});

test('id_cliente is NOT NULL after migration', function () {
    $columns = DB::select("PRAGMA table_info('cargas')");
    $idClienteCol = collect($columns)->firstWhere('name', 'id_cliente');

    expect($idClienteCol)->not->toBeNull();
    expect($idClienteCol->notnull)->toBe(1);
});

// =========================================================================
// Carga model uses id_cliente relationship
// =========================================================================

test('carga factory creates with id_cliente FK', function () {
    $carga = Carga::factory()->create();

    expect($carga->id_cliente)->not->toBeNull();
    expect($carga->cliente)->not->toBeNull();
    expect($carga->cliente)->toBeInstanceOf(Cliente::class);
});

test('carga cliente relationship returns correct cliente', function () {
    $cliente = Cliente::create([
        'razon_social' => 'FK Test Client',
        'cuit' => '30-99999999-9',
    ]);

    $lote = Lote::factory()->create();

    $carga = Carga::factory()->create([
        'id_lote' => $lote->id_lote,
        'id_cliente' => $cliente->id_cliente,
    ]);

    expect($carga->cliente->id_cliente)->toBe($cliente->id_cliente);
    expect($carga->cliente->razon_social)->toBe('FK Test Client');
});

// =========================================================================
// Data integrity
// =========================================================================

test('all existing cargas have valid id_cliente after migration', function () {
    Carga::factory()->count(3)->create();

    $cargasWithNullClient = DB::table('cargas')
        ->whereNull('id_cliente')
        ->whereNull('deleted_at')
        ->count();

    expect($cargasWithNullClient)->toBe(0);
});

test('carga cannot be created with invalid id_cliente', function () {
    $lote = Lote::factory()->create();

    expect(function () use ($lote) {
        DB::table('cargas')->insert([
            'id_lote' => $lote->id_lote,
            'id_cliente' => 999999,
            'ticket' => 'TK-FK-TEST',
            'peso_bruto' => 20000,
            'tara' => 5000,
            'peso_neto' => 15000,
            'fecha_carga' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(\Illuminate\Database\QueryException::class);
});
