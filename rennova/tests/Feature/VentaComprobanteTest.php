<?php

use App\Models\Carga;
use App\Models\Cliente;
use App\Models\Lote;
use App\Models\Venta;

test('el comprobante de una venta se descarga en pdf', function () {
    $usuario = usuarioConPermisos(['ver-ventas']);
    $cliente = Cliente::factory()->create(['razon_social' => 'Maderera del Norte']);
    $lote = Lote::factory()->create();
    $venta = Venta::factory()->create([
        'id_cliente' => $cliente->id_cliente,
        'fecha_emision' => now()->toDateString(),
        'monto' => 150000,
    ]);
    $carga = Carga::factory()->create(['id_lote' => $lote->id_lote, 'ticket' => 'TKT-COMP-1']);
    $venta->cargas()->attach($carga->id_carga, [
        'precio_unitario' => 15000,
        'peso_toneladas' => 10,
        'subtotal' => 150000,
    ]);

    $respuesta = $this->actingAs($usuario)->get(route('ventas.comprobante', ['venta' => $venta->id_recibo]));

    $respuesta->assertOk();
    expect($respuesta->headers->get('Content-Type'))->toContain('application/pdf');
});

test('sin permiso no se puede descargar el comprobante', function () {
    $usuario = usuarioConPermisos([]);
    $venta = Venta::factory()->create();

    $this->actingAs($usuario)
        ->get(route('ventas.comprobante', ['venta' => $venta->id_recibo]))
        ->assertForbidden();
});
