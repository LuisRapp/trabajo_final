<?php

$rutasModulo = [
    ['lotes.index', 'ver-lotes'],
    ['clientes.index', 'ver-clientes'],
    ['proveedores.index', 'ver-proveedores'],
    ['categorias-madera.index', 'ver-categorias-madera'],
    ['unidades-medida.index', 'ver-unidades-medida'],
    ['tipos-maquinaria.index', 'ver-tipos-maquinaria'],
    ['roles-laborales.index', 'ver-roles-laborales'],
    ['insumos.index', 'ver-insumos'],
    ['maquinarias.index', 'ver-maquinarias'],
    ['empleados.index', 'ver-empleados'],
    ['mantenimientos.index', 'ver-mantenimientos'],
    ['programar-mantenimiento', 'ver-mantenimientos'],
    ['kits-mantenimiento.index', 'ver-kits-mantenimiento'],
    ['partes-diarios.index', 'ver-partes-diarios'],
    ['ventas.index', 'ver-ventas'],
    ['recibos.index', 'ver-recibos'],
    ['historico-costos-maquinarias.index', 'ver-historico-costos-maquinarias'],
    ['historico-roles-laborales.index', 'ver-roles-laborales'],
    ['lista-precios.index', 'ver-lista-precios'],
    ['auditorias.index', 'ver-auditoria'],
    ['reportes.estadisticas-forestales', 'ver-reportes'],
    ['liquidacion-pagos.index', 'ver-liquidacion-pagos'],
    ['asignaciones-lote.index', 'ver-asignaciones-lote'],
    ['propuestas-asignacion.index', 'ver-propuestas-asignacion'],
    ['cargas.index', 'ver-cargas'],
    ['choferes.index', 'ver-choferes'],
];

it('bloquea la pagina de modulo sin el permiso ver-*', function (string $ruta, string $permiso) {
    $usuario = usuarioConPermisos();

    $this->actingAs($usuario)
        ->get(route($ruta))
        ->assertForbidden();
})->with($rutasModulo);

it('permite la pagina de modulo con el permiso ver-*', function (string $ruta, string $permiso) {
    $usuario = usuarioConPermisos([$permiso]);

    $this->actingAs($usuario)
        ->get(route($ruta))
        ->assertOk();
})->with($rutasModulo);
