<?php

use App\Models\Insumo;
use App\Models\Lote;
use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\NotificacionSistema;
use App\Models\PropuestaCompraMantenimiento;
use App\Models\PropuestaCompraMantenimientoInsumo;
use App\Models\TipoMantenimiento;
use App\Models\Usuario;
use App\Services\ClimaDecisionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

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
        'estado' => 'disponible',
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

test('check-umbrales registra el contexto de la orden cuando falla la notificacion interna', function () {
    $usuario = Usuario::factory()->create();
    DB::table('configuracion_notificaciones_mantenimiento')->insert([
        'user_id' => $usuario->id,
        'tipo_notificacion' => 'umbral',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $maquinaria = Maquinaria::factory()->create([
        'estado' => 'disponible',
        'umbral_toneladas' => 1000,
        'toneladas_acumuladas' => 1500,
    ]);

    Log::spy();
    Event::listen('eloquent.creating: '.NotificacionSistema::class, function () {
        throw new RuntimeException('fallo simulado de notificacion');
    });

    $this->artisan('mantenimiento:check-umbrales')->assertExitCode(0);

    $orden = Mantenimiento::query()
        ->where('id_maquinaria', $maquinaria->id_maquinaria)
        ->first();

    expect($orden)->not->toBeNull()
        ->and($orden->estado)->toBe('programado')
        ->and(
            NotificacionSistema::query()->where('tipo', 'umbral_alcanzado')->count()
        )->toBe(0);

    Log::shouldHaveReceived('error')
        ->withArgs(function (string $mensaje, array $contexto = []) use ($orden, $maquinaria) {
            return ($contexto['id_mantenimiento'] ?? null) === $orden->id_mantenimiento
                && ($contexto['id_maquinaria'] ?? null) === $maquinaria->id_maquinaria
                && str_contains((string) ($contexto['error'] ?? ''), 'fallo simulado');
        })
        ->once();
});

test('check-umbrales no genera orden cuando ya existe una orden abierta', function () {
    $maquinaria = Maquinaria::factory()->create([
        'estado' => 'disponible',
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
        'estado' => 'disponible',
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
    $maquinaria = Maquinaria::factory()->create(['estado' => 'disponible']);
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

test('check-umbrales programa la orden por clima cuando hay lluvia en la ventana', function () {
    $usuario = Usuario::factory()->create();
    DB::table('configuracion_notificaciones_mantenimiento')->insert([
        'user_id' => $usuario->id,
        'tipo_notificacion' => 'umbral',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $maquinaria = Maquinaria::factory()->create([
        'estado' => 'disponible',
        'umbral_toneladas' => 1000,
        'toneladas_acumuladas' => 1500,
    ]);

    $lote = Lote::factory()->activo()->create([
        'latitud' => -26.5,
        'longitud' => -54.5,
    ]);

    $lote->maquinarias()->attach($maquinaria->id_maquinaria);

    $this->mock(ClimaDecisionService::class, function ($mock) {
        $mock->shouldReceive('analizarYRecomendar')
            ->once()
            ->andReturn([
                'success' => true,
                'dias_detalle' => [
                    [
                        'fecha' => now()->addDay()->toDateString(),
                        'precipitacion_mm' => 15,
                        'razon' => 'lluvia',
                    ],
                ],
            ]);
    });

    $this->artisan('mantenimiento:check-umbrales')->assertExitCode(0);

    $orden = Mantenimiento::query()
        ->where('id_maquinaria', $maquinaria->id_maquinaria)
        ->first();

    expect($orden)->not->toBeNull()
        ->and($orden->estado)->toBe('programado')
        ->and($orden->fecha_programada)->not->toBeNull();
});

test('check-umbrales falla cuando no hay tipo preventivo en el catalogo', function () {
    TipoMantenimiento::query()->forceDelete();

    Maquinaria::factory()->create([
        'estado' => 'disponible',
        'umbral_toneladas' => 1000,
        'toneladas_acumuladas' => 1500,
    ]);

    $this->artisan('mantenimiento:check-umbrales')
        ->expectsOutputToContain('No hay un tipo de mantenimiento preventivo')
        ->assertExitCode(1);

    expect(Mantenimiento::query()->count())->toBe(0);
});

test('renombrar el tipo preventivo no detiene el proceso automatico', function () {
    TipoMantenimiento::query()->firstOrFail()->update(['nombre' => 'Service 500 hs']);

    $maquinaria = Maquinaria::factory()->create([
        'estado' => 'disponible',
        'umbral_toneladas' => 1000,
        'toneladas_acumuladas' => 1500,
    ]);

    $this->artisan('mantenimiento:check-umbrales')->assertExitCode(0);

    expect(
        Mantenimiento::query()->where('id_maquinaria', $maquinaria->id_maquinaria)->exists()
    )->toBeTrue();
});
