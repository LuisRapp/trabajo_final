<?php

use App\Http\Livewire\Adelantos;
use App\Http\Livewire\Choferes;
use App\Http\Livewire\Empleados;
use App\Http\Livewire\HistoricoRolesLaborales;
use App\Http\Livewire\Lotes;
use App\Http\Livewire\Recibos;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

dataset('pantallas_con_pestanas', [
    'empleados' => [Empleados::class, ['ver-empleados', 'crear-empleados', 'editar-empleados'], 'Nuevo Empleado', 'Listado de Empleados'],
    'choferes' => [Choferes::class, ['ver-choferes', 'crear-choferes', 'editar-choferes'], 'Nuevo Chofer', 'Listado de Choferes'],
    'adelantos' => [Adelantos::class, ['ver-adelantos', 'crear-adelantos', 'editar-adelantos'], 'Nuevo Adelanto', 'Listado de Adelantos'],
    'recibos' => [Recibos::class, ['ver-recibos', 'crear-recibos', 'editar-recibos'], 'Nuevo Recibo', 'Listado de Recibos'],
    'historico-roles' => [HistoricoRolesLaborales::class, ['ver-historico-roles-laborales', 'crear-historico-roles-laborales', 'editar-historico-roles-laborales'], 'Nuevo Histórico', 'Listado de Históricos'],
    'lotes' => [Lotes::class, ['ver-lotes', 'crear-lotes', 'editar-lotes'], 'Nuevo Lote', 'Listado de Lotes'],
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
