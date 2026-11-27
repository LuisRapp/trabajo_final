<?php

use App\Http\Livewire\SelectorLote;
use App\Models\Lote;
use App\Models\Usuario;
use Livewire\Livewire;

test('usuario autenticado puede renderizar el selector de lote', function () {
    Livewire::actingAs(Usuario::factory()->create())
        ->test(SelectorLote::class)
        ->assertStatus(200);
});

test('sin coordenadas no carga pronóstico', function () {
    Lote::factory()->create([
        'estado' => 'activo',
        'latitud' => null,
        'longitud' => null,
    ]);

    Livewire::actingAs(Usuario::factory()->create())
        ->test(SelectorLote::class)
        ->assertSet('pronosticoData', null);
});

test('un pronóstico con días acentuados produce un snapshot serializable', function () {
    // Regresión: con substr() en vez de mb_substr() el recorte de "miércoles"
    // partía el byte de la "é", json_encode() devolvía false y el snapshot del
    // componente quedaba vacío: el dashboard se renderizaba en blanco.
    app()->setLocale('es');

    Lote::factory()->create([
        'propietario' => 'Lote lluvia',
        'estado' => 'activo',
        'latitud' => -34.6,
        'longitud' => -58.4,
    ]);

    $componente = Livewire::actingAs(Usuario::factory()->create())->test(SelectorLote::class);
    $pronostico = $componente->get('pronosticoData');

    expect($pronostico)->not->toBeNull()
        ->and(json_encode($pronostico))->not->toBeFalse();

    $etiquetas = implode(' ', array_column($pronostico['pronostico'], 'label'));

    expect($etiquetas)->toContain('Mié');
});

test('volver a un lote con coordenadas despues de uno sin coordenadas no rompe el componente', function () {
    // Regresion: el servicio se inyectaba en mount(), asi que en el segundo
    // request la propiedad quedaba sin inicializar y el cambio de lote daba 500.
    $conCoordenadas = Lote::factory()->create([
        'propietario' => 'Lote Gutierrez',
        'estado' => 'activo',
        'latitud' => -34.6,
        'longitud' => -58.4,
    ]);

    $sinCoordenadas = Lote::factory()->create([
        'propietario' => 'Lote Yop',
        'estado' => 'activo',
        'latitud' => null,
        'longitud' => null,
    ]);

    $this->mock(\App\Services\ClimaDecisionService::class, function ($mock) {
        $mock->shouldReceive('analizarYRecomendar')->andReturn([
            'success' => true,
            'nivel_urgencia' => 'BAJA',
            'recomendacion' => 'Sin lluvia en la ventana.',
            'dias_detalle' => [],
            'datos_calculados' => ['volumen_riesgo' => 0, 'aumento_necesario_pct' => 0],
        ]);
    });

    $componente = Livewire::actingAs(Usuario::factory()->create())->test(SelectorLote::class);

    $componente->set('loteSeleccionado', $sinCoordenadas->id_lote)
        ->assertSet('pronosticoData', null)
        ->set('loteSeleccionado', $conCoordenadas->id_lote);

    expect($componente->get('pronosticoData'))->not->toBeNull();
});

test('los fines de semana se muestran como no laborales y no cuentan como dias perdidos', function () {
    Lote::factory()->create([
        'estado' => 'activo',
        'latitud' => -34.6,
        'longitud' => -58.4,
    ]);

    $this->mock(\App\Services\ClimaDecisionService::class, function ($mock) {
        $mock->shouldReceive('analizarYRecomendar')->andReturn([
            'success' => true,
            'nivel_urgencia' => 'BAJA',
            'recomendacion' => 'Se detecta un dia de lluvia.',
            'dias_detalle' => [
                ['fecha' => \Carbon\Carbon::parse('2026-11-25'), 'fecha_str' => '25/11/2026', 'dia_semana' => 'miercoles', 'estado' => 'OPERATIVO', 'razon' => null],
                ['fecha' => \Carbon\Carbon::parse('2026-11-28'), 'fecha_str' => '28/11/2026', 'dia_semana' => 'sabado', 'estado' => 'INACTIVO', 'razon' => 'Fin de semana (no laboral)'],
                ['fecha' => \Carbon\Carbon::parse('2026-11-29'), 'fecha_str' => '29/11/2026', 'dia_semana' => 'domingo', 'estado' => 'INACTIVO', 'razon' => 'Fin de semana (no laboral)'],
                ['fecha' => \Carbon\Carbon::parse('2026-12-02'), 'fecha_str' => '02/12/2026', 'dia_semana' => 'miercoles', 'estado' => 'INACTIVO', 'razon' => 'Lluvia diurna activa'],
            ],
            'datos_calculados' => ['volumen_riesgo' => 40, 'aumento_necesario_pct' => 12],
        ]);
    });

    $componente = Livewire::actingAs(Usuario::factory()->create())->test(SelectorLote::class);
    $pronostico = $componente->get('pronosticoData');
    $porEtiqueta = collect($pronostico['pronostico'])->keyBy('label');

    expect($pronostico['analisisImpacto']['diasPerdidos'])->toBe(1)
        ->and($porEtiqueta['Sáb (28/11/2026)']['estado'])->toBe('NO LABORAL')
        ->and($porEtiqueta['Sáb (28/11/2026)']['inactivo'])->toBeFalse()
        ->and($porEtiqueta['Sáb (28/11/2026)']['no_laboral'])->toBeTrue()
        ->and($porEtiqueta['Mié (02/12/2026)']['estado'])->toBe('NO OPERATIVO')
        ->and($porEtiqueta['Mié (02/12/2026)']['inactivo'])->toBeTrue();
});

test('calcula el dia en espanol y arma una accion concreta aunque el servicio devuelva ingles', function () {
    Lote::factory()->create([
        'propietario' => 'Lote test1',
        'estado' => 'activo',
        'latitud' => -34.6,
        'longitud' => -58.4,
    ]);

    $hoy = \Carbon\Carbon::today();

    $this->mock(\App\Services\ClimaDecisionService::class, function ($mock) use ($hoy) {
        $mock->shouldReceive('analizarYRecomendar')->andReturn([
            'success' => true,
            'nivel_urgencia' => 'ALTA',
            'recomendacion' => 'texto viejo',
            'dias_detalle' => [
                ['fecha' => $hoy->copy(), 'fecha_str' => $hoy->format('d/m/Y'), 'dia_semana' => 'Friday', 'estado' => 'INACTIVO', 'razon' => 'Lluvia diurna activa'],
                ['fecha' => $hoy->copy()->addDay(), 'fecha_str' => $hoy->copy()->addDay()->format('d/m/Y'), 'dia_semana' => 'Saturday', 'estado' => 'INACTIVO', 'razon' => 'Fin de semana (no laboral)'],
                ['fecha' => $hoy->copy()->addDays(2), 'fecha_str' => $hoy->copy()->addDays(2)->format('d/m/Y'), 'dia_semana' => 'Sunday', 'estado' => 'INACTIVO', 'razon' => 'Fin de semana (no laboral)'],
                ['fecha' => $hoy->copy()->addDays(3), 'fecha_str' => $hoy->copy()->addDays(3)->format('d/m/Y'), 'dia_semana' => 'Monday', 'estado' => 'OPERATIVO', 'razon' => null],
                ['fecha' => $hoy->copy()->addDays(4), 'fecha_str' => $hoy->copy()->addDays(4)->format('d/m/Y'), 'dia_semana' => 'Tuesday', 'estado' => 'OPERATIVO', 'razon' => null],
            ],
            'datos_calculados' => [
                'volumen_riesgo' => 300,
                'aumento_necesario_pct' => 18,
                'nueva_meta_diaria' => 118,
            ],
        ]);
    });

    $componente = Livewire::actingAs(Usuario::factory()->create())->test(SelectorLote::class);
    $datos = $componente->get('pronosticoData');

    $componente->assertSee('Sumá 18%');

    expect($datos['alerta'])->toBe('COMPENSAR')
        ->and($datos['pronostico'][0]['label'])->toStartWith('Vie')
        ->and($datos['analisisImpacto']['diasPerdidos'])->toBe(1)
        ->and($datos['analisisImpacto']['primerOperativo'])->toStartWith('Lun')
        ->and($datos['recomendacionDetallada'])->toContain('Sumá 18%');
});
