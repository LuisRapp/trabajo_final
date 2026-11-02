<?php

use App\Models\Lote;
use App\Models\Usuario;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = Usuario::factory()->create();
    $this->admin->assignRole('Administrador');
});

it('responde 200 en la pantalla', function (string $nombreRuta) {
    $this->actingAs($this->admin)
        ->get(route($nombreRuta))
        ->assertOk();
})->with([
    'dashboard',
    'modulos.maquinaria',
    'modulos.inventario-forestal',
    'modulos.personal',
    'modulos.operaciones',
    'modulos.administracion',
    'profile.edit',
    'password.edit',
    'appearance.edit',
    'lotes.index',
    'clientes.index',
    'proveedores.index',
    'categorias-madera.index',
    'unidades-medida.index',
    'tipos-maquinaria.index',
    'roles-laborales.index',
    'insumos.index',
    'maquinarias.index',
    'empleados.index',
    'usuarios.index',
    'mantenimientos.index',
    'configuracion-notificaciones.index',
    'configuracion-mantenimiento.index',
    'notificaciones.index',
    'programar-mantenimiento',
    'kits-mantenimiento.index',
    'partes-diarios.index',
    'ventas.index',
    'adelantos.index',
    'recibos.index',
    'historico-costos-maquinarias.index',
    'historico-roles-laborales.index',
    'lista-precios.index',
    'auditorias.index',
    'estado-procesos.index',
    'reportes.estadisticas-forestales',
    'liquidacion-pagos.index',
    'asignaciones-lote.index',
    'propuestas-asignacion.index',
    'modulos.operaciones.gestionstock',
    'cargas.index',
    'cargas.create',
    'choferes.index',
    'choferes.create',
    'roles-permisos.index',
]);

it('responde 200 en pantallas con parámetro de lote', function (string $nombreRuta) {
    $lote = Lote::factory()->create();

    $this->actingAs($this->admin)
        ->get(route($nombreRuta, $lote->id_lote))
        ->assertOk();
})->with([
    'lotes.recomendaciones',
    'lotes.tareas',
]);
