<?php

use App\Http\Livewire\Adelantos;
use App\Http\Livewire\Cargas;
use App\Http\Livewire\Choferes;
use App\Http\Livewire\Empleados;
use App\Http\Livewire\HistoricoCostosMaquinarias;
use App\Http\Livewire\HistoricoRolesLaborales;
use App\Http\Livewire\Lotes;
use App\Http\Livewire\Mantenimientos;
use App\Http\Livewire\Maquinarias;
use App\Http\Livewire\PartesDiarios;
use App\Http\Livewire\Recibos;
use App\Http\Livewire\TipoMantenimiento;
use App\Http\Livewire\TiposMaquinaria;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

dataset('pantallas_con_pestanas', [
    'empleados' => [Empleados::class, ['ver-empleados', 'crear-empleados', 'editar-empleados'], 'Nuevo Empleado', 'Listado de Empleados'],
    'choferes' => [Choferes::class, ['ver-choferes', 'crear-choferes', 'editar-choferes'], 'Nuevo Chofer', 'Listado de Choferes'],
    'adelantos' => [Adelantos::class, ['ver-adelantos', 'crear-adelantos', 'editar-adelantos'], 'Nuevo Adelanto', 'Listado de Adelantos'],
    'recibos' => [Recibos::class, ['ver-recibos', 'crear-recibos', 'editar-recibos'], 'Nuevo Recibo', 'Listado de Recibos'],
    'historico-roles' => [HistoricoRolesLaborales::class, ['ver-historico-roles-laborales', 'crear-historico-roles-laborales', 'editar-historico-roles-laborales'], 'Nuevo Histórico', 'Listado de Históricos'],
    'lotes' => [Lotes::class, ['ver-lotes', 'crear-lotes', 'editar-lotes'], 'Nuevo Lote', 'Listado de Lotes'],
    'partes-diarios' => [PartesDiarios::class, ['ver-partes-diarios', 'crear-partes-diarios', 'editar-partes-diarios'], 'Nuevo Parte Diario', 'Listado de Partes Diarios'],
    'cargas' => [Cargas::class, ['ver-cargas', 'crear-cargas', 'editar-cargas'], 'Nueva Carga', 'Listado de Cargas'],
    'historico-costos' => [HistoricoCostosMaquinarias::class, ['ver-historico-costos-maquinarias', 'crear-historico-costos-maquinarias', 'editar-historico-costos-maquinarias'], 'Nuevo Historico', 'Listado de Historicos'],
    'mantenimientos' => [Mantenimientos::class, ['ver-mantenimientos', 'crear-mantenimientos', 'editar-mantenimientos'], 'Nuevo Mantenimiento', 'Listado de Mantenimientos'],
    'maquinarias' => [Maquinarias::class, ['ver-maquinarias', 'crear-maquinarias', 'editar-maquinarias'], 'Nueva Maquinaria', 'Listado de Maquinarias'],
    'tipo-mantenimiento' => [TipoMantenimiento::class, ['ver-tipos-mantenimiento', 'crear-tipos-mantenimiento', 'editar-tipos-mantenimiento'], 'Nuevo Tipo', 'Listado de Tipos'],
    'tipos-maquinaria' => [TiposMaquinaria::class, ['ver-tipos-maquinaria', 'crear-tipos-maquinaria', 'editar-tipos-maquinaria'], 'Nuevo Tipo', 'Listado de Tipos'],
]);

test('cada pantalla de alta muestra sus dos pestanas', function (string $componente, array $permisos, string $tabAlta, string $tabListado) {
    $usuario = usuarioConPermisos($permisos);

    Livewire::actingAs($usuario)
        ->test($componente)
        ->assertSee($tabAlta)
        ->assertSee($tabListado);
})->with('pantallas_con_pestanas');

test('sin permiso de alta la pestana del formulario no se muestra', function () {
    // Antes la pestana se dibujaba siempre y el @canany estaba adentro: un usuario
    // de solo lectura veia "Nuevo Empleado" y al clickearlo encontraba un hueco.
    $usuario = usuarioConPermisos(['ver-empleados']);

    Livewire::actingAs($usuario)
        ->test(Empleados::class)
        ->assertSee('Listado de Empleados')
        ->assertDontSee('Nuevo Empleado');
});

test('ninguna vista arma las pestanas a mano', function () {
    $infractores = collect(File::allFiles(resource_path('views')))
        ->reject(fn ($archivo): bool => str_ends_with($archivo->getPathname(), 'components/tab-nav.blade.php'))
        ->filter(fn ($archivo): bool => str_contains(File::get($archivo->getPathname()), 'role="tablist"'))
        ->map(fn ($archivo): string => $archivo->getRelativePathname())
        ->values();

    expect($infractores)->toBeEmpty(
        'Estas vistas todavia dibujan las pestanas a mano en vez de usar <x-tab-nav>: '.$infractores->implode(', ')
    );
});
