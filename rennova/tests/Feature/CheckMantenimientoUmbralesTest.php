<?php

use App\Models\Insumo;
use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\NotificacionSistema;
use App\Models\PropuestaCompraMantenimiento;
use App\Models\PropuestaCompraMantenimientoInsumo;
use App\Models\TipoMantenimiento;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    TipoMantenimiento::factory()->preventivo()->create();
});

test('check-umbrales genera orden y notificacion interna al superarse el umbral', function () {
    $usuario = Usuario::factory()->create();
    DB::table('configuracion_notificaciones_mantenimiento')->insert([
        'user_id' => $usuario->id,
        'tipo_notificacion' => 'umbral',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $maquinaria = Maquinaria::factory()->create([
        'estado' => 'operativa',
        'umbral_toneladas' => 1000,
        'toneladas_acumuladas' => 1500,
    ]);

    $this->artisan('mantenimiento:check-umbrales')->assertExitCode(0);

    $orden = Mantenimiento::query()
        ->where('id_maquinaria', $maquinaria->id_maquinaria)
        ->first();

    expect($orden)->not->toBeNull()
        ->and($orden->estado)->toBe('programado');

    expect(
        NotificacionSistema::query()
            ->where('user_id', $usuario->id)
            ->where('tipo', 'umbral_alcanzado')
            ->count()
    )->toBe(1);
});

test('check-umbrales no genera orden cuando ya existe una orden abierta', function () {
    $maquinaria = Maquinaria::factory()->create([
        'estado' => 'operativa',
        'umbral_toneladas' => 1000,
        'toneladas_acumuladas' => 1500,
    ]);

    Mantenimiento::factory()->programado()->create([
        'id_maquinaria' => $maquinaria->id_maquinaria,
    ]);

    $this->artisan('mantenimiento:check-umbrales')->assertExitCode(0);

    expect(
        Mantenimiento::query()
            ->where('id_maquinaria', $maquinaria->id_maquinaria)
            ->count()
    )->toBe(1);
});

test('check-umbrales no genera orden por debajo del umbral', function () {
    $maquinaria = Maquinaria::factory()->create([
        'estado' => 'operativa',
        'umbral_toneladas' => 1000,
        'toneladas_acumuladas' => 400,
    ]);

    $this->artisan('mantenimiento:check-umbrales')->assertExitCode(0);

    expect(
        Mantenimiento::query()
            ->where('id_maquinaria', $maquinaria->id_maquinaria)
            ->count()
    )->toBe(0);
});

test('los items de las propuestas de compra usan baja logica', function () {
    $maquinaria = Maquinaria::factory()->create(['estado' => 'operativa']);
    $mantenimiento = Mantenimiento::factory()->programado()->create([
        'id_maquinaria' => $maquinaria->id_maquinaria,
    ]);
    $insumo = Insumo::factory()->create();

    $proposal = PropuestaCompraMantenimiento::create([
        'id_mantenimiento' => $mantenimiento->id_mantenimiento,
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'status' => 'pending',
    ]);

    $item = PropuestaCompraMantenimientoInsumo::create([
        'id_propuesta_compra_mantenimiento' => $proposal->id_propuesta_compra_mantenimiento,
        'id_insumo' => $insumo->id_insumo,
        'cantidad_requerida' => 10,
        'stock_disponible' => 2,
        'faltante' => 8,
    ]);

    $item->delete();

    expect(PropuestaCompraMantenimientoInsumo::query()->count())->toBe(0)
        ->and(PropuestaCompraMantenimientoInsumo::withTrashed()->count())->toBe(1);
});
