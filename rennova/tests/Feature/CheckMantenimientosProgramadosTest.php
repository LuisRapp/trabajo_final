<?php

use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\NotificacionSistema;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

test('check-programados marca vencido el mantenimiento no confirmado y notifica a los usuarios configurados', function () {
    $usuario = Usuario::factory()->create();
    DB::table('configuracion_notificaciones_mantenimiento')->insert([
        'user_id' => $usuario->id,
        'tipo_notificacion' => 'recordatorio',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $maquinaria = Maquinaria::factory()->create(['estado' => 'disponible']);
    $mantenimiento = Mantenimiento::factory()->programado()->create([
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'fecha_programada' => now()->subDay()->toDateString(),
    ]);

    $this->artisan('mantenimiento:check-programados')->assertExitCode(0);

    expect($mantenimiento->fresh()->estado)->toBe('vencido');

    expect(
        NotificacionSistema::query()
            ->where('user_id', $usuario->id)
            ->where('tipo', 'mantenimiento_vencido')
            ->count()
    )->toBe(1);
});

test('check-programados marca vencido aunque falle la notificacion y registra el contexto', function () {
    $usuario = Usuario::factory()->create();
    DB::table('configuracion_notificaciones_mantenimiento')->insert([
        'user_id' => $usuario->id,
        'tipo_notificacion' => 'recordatorio',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $maquinaria = Maquinaria::factory()->create(['estado' => 'disponible']);
    $mantenimiento = Mantenimiento::factory()->programado()->create([
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'fecha_programada' => now()->subDay()->toDateString(),
    ]);

    Log::spy();
    Event::listen('eloquent.creating: '.NotificacionSistema::class, function () {
        throw new RuntimeException('fallo simulado de notificacion');
    });

    $this->artisan('mantenimiento:check-programados')->assertExitCode(0);

    expect($mantenimiento->fresh()->estado)->toBe('vencido')
        ->and(
            NotificacionSistema::query()->where('tipo', 'mantenimiento_vencido')->count()
        )->toBe(0);

    Log::shouldHaveReceived('error')
        ->withArgs(function (string $mensaje, array $contexto = []) use ($mantenimiento, $maquinaria) {
            return ($contexto['id_mantenimiento'] ?? null) === $mantenimiento->id_mantenimiento
                && ($contexto['id_maquinaria'] ?? null) === $maquinaria->id_maquinaria
                && str_contains((string) ($contexto['error'] ?? ''), 'fallo simulado');
        })
        ->once();
});

test('check-programados no marca vencidos los mantenimientos confirmados en curso', function () {
    $maquinaria = Maquinaria::factory()->create(['estado' => 'disponible']);
    $mantenimiento = Mantenimiento::factory()->enCurso()->create([
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'fecha_programada' => now()->subDay()->toDateString(),
    ]);

    $this->artisan('mantenimiento:check-programados')->assertExitCode(0);

    expect($mantenimiento->fresh()->estado)->toBe('en curso');
});
