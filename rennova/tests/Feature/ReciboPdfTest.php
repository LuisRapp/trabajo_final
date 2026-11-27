<?php

use App\Models\Recibo;

test('descarga el comprobante pdf de un recibo', function () {
    $recibo = Recibo::factory()->create();

    $respuesta = $this->actingAs(usuarioConPermisos(['ver-recibos']))
        ->get(route('recibos.pdf', $recibo->id_recibo));

    $respuesta->assertOk();
    expect($respuesta->headers->get('Content-Type'))->toContain('application/pdf')
        ->and($respuesta->getContent())->toStartWith('%PDF');
});

test('sin permiso ver-recibos no descarga el comprobante', function () {
    $recibo = Recibo::factory()->create();

    $this->actingAs(usuarioConPermisos(['ver-insumos']))
        ->get(route('recibos.pdf', $recibo->id_recibo))
        ->assertForbidden();
});
