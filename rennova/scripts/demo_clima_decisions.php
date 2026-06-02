<?php

use App\Models\Lote;
use App\Services\ClimaDecisionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Forzar SQLite local para el sandbox y crear esquema mínimo
config([
    'database.default' => 'sqlite',
    'database.connections.sqlite.database' => database_path('demo_clima.sqlite'),
]);
DB::purge('sqlite');
DB::reconnect('sqlite');

if (!file_exists(database_path('demo_clima.sqlite'))) {
    touch(database_path('demo_clima.sqlite'));
}

// Esquema mínimo necesario para el servicio (lotes, maquinarias, pivot, parte_diarios, cargas)
Schema::disableForeignKeyConstraints();
Schema::dropIfExists('lote_maquinaria');
Schema::dropIfExists('empleados');
Schema::dropIfExists('cargas');
Schema::dropIfExists('parte_diarios');
Schema::dropIfExists('maquinarias');
Schema::dropIfExists('lotes');

Schema::create('lotes', function (Blueprint $table) {
    $table->id('id_lote');
    $table->string('propietario')->nullable();
    $table->decimal('latitud', 10, 6)->nullable();
    $table->decimal('longitud', 10, 6)->nullable();
    $table->timestamps();
});

Schema::create('maquinarias', function (Blueprint $table) {
    $table->id('id_maquinaria');
    $table->unsignedBigInteger('id_tipo_maquinaria')->nullable();
    $table->string('nombre')->nullable();
    $table->decimal('odometro', 10, 2)->nullable();
    $table->decimal('horas_proximo_mant', 10, 2)->nullable();
    $table->boolean('es_alquilada')->default(false);
    $table->timestamps();
});

Schema::create('empleados', function (Blueprint $table) {
    $table->id('id_empleado');
    $table->string('nombre')->nullable();
    $table->date('fecha_fin_actividades')->nullable();
    $table->timestamps();
});

Schema::create('lote_maquinaria', function (Blueprint $table) {
    $table->unsignedBigInteger('id_lote');
    $table->unsignedBigInteger('id_maquinaria');
    $table->timestamps();
});

Schema::create('parte_diarios', function (Blueprint $table) {
    $table->id('id_parte_diario');
    $table->date('fecha')->nullable();
    $table->timestamps();
});

Schema::create('cargas', function (Blueprint $table) {
    $table->id('id_carga');
    $table->unsignedBigInteger('id_parte_diario')->nullable();
    $table->decimal('peso_neto', 10, 2)->nullable();
    $table->timestamps();
});
Schema::enableForeignKeyConstraints();

function getLoteDemo(): Lote
{
    $lote = Lote::first();
    if (!$lote) {
        $lote = Lote::create([
            'propietario' => 'Demo',
            'latitud' => -34.6,
            'longitud' => -58.4,
        ]);
    }
    if (!$lote->latitud || !$lote->longitud) {
        $lote->latitud = $lote->latitud ?: -34.6;
        $lote->longitud = $lote->longitud ?: -58.4;
        $lote->save();
    }
    return $lote;
}

function buildHourly(array $fechas, array $precipitaciones): array
{
    $times = [];
    $values = [];

    foreach ($fechas as $i => $fechaStr) {
        $base = Carbon::parse($fechaStr)->startOfDay();
        $mm = $precipitaciones[$i] ?? 0;
        $diurnaTotal = min(6, $mm); // asegura lluvia diurna > 5mm si mm >= 10
        $perHour = $diurnaTotal > 0 ? ($diurnaTotal / 12) : 0;

        for ($h = 0; $h < 24; $h++) {
            $times[] = $base->copy()->addHours($h)->format('Y-m-d\TH:00');
            if ($h >= 6 && $h < 18) {
                $values[] = $perHour;
            } else {
                $values[] = 0.0;
            }
        }
    }

    return ['time' => $times, 'precipitation' => $values];
}

function escenario(string $nombre, array $precipitaciones, array $nubosidades)
{
    $svc = app(ClimaDecisionService::class);
    $ref = new ReflectionClass($svc);
    $map = tap($ref->getMethod('mapearDiasInactivos'))->setAccessible(true);

    $fechas = collect(range(0, 6))->map(fn ($i) => now()->addDays($i)->toDateString())->all();
    $hourly = buildHourly($fechas, $precipitaciones);
    $vientos = array_fill(0, 7, 12.0);
    $et0s = array_fill(0, 7, 3.2);

    $pronostico = [
        'daily' => [
            'time' => $fechas,
            'precipitation_sum' => $precipitaciones,
            'cloudcover_mean' => $nubosidades,
            'wind_speed_10m_max' => $vientos,
            'et0_fao_evapotranspiration' => $et0s,
        ],
        'hourly' => [
            'time' => $hourly['time'],
            'precipitation' => $hourly['precipitation'],
        ],
    ];

    $analisis = $map->invoke($svc, $pronostico);
    $lote = getLoteDemo();

    if ($analisis['dias_operativos_previos'] > 0) {
        $ant = tap($ref->getMethod('estrategiaAnticipacion'))->setAccessible(true);
        $res = $ant->invoke($svc, $lote, $analisis);
    } else {
        $react = tap($ref->getMethod('estrategiaReaccion'))->setAccessible(true);
        $res = $react->invoke($svc, $lote, $analisis);
    }

    echo "==== {$nombre} ====" . PHP_EOL;
    echo "Estrategia: {$res['estrategia']}" . PHP_EOL;
    if (isset($res['accion_recomendada'])) {
        echo "Accion: {$res['accion_recomendada']}" . PHP_EOL;
    }
    if (isset($res['datos_calculados']['aumento_necesario_pct'])) {
        echo "Aumento (visual): {$res['datos_calculados']['aumento_necesario_pct']}%" . PHP_EOL;
    }
    echo "Recomendacion:\n" . $res['recomendacion'] . PHP_EOL . PHP_EOL;
}

// Escenario 1: Anticipacion moderada (lluvia en 2 dias)
escenario(
    'Anticipacion moderada',
    [0, 0, 12, 0, 0, 0, 0],
    [20, 30, 90, 40, 30, 20, 10]
);

// Escenario 2: Reaccion inmediata (llueve hoy)
escenario(
    'Reaccion inmediata',
    [15, 0, 0, 0, 0, 0, 0],
    [80, 80, 40, 20, 20, 20, 20]
);

// Escenario 3: Anticipacion con tope visual (deficit alto)
escenario(
    'Anticipacion tope 25%',
    [0, 0, 20, 18, 0, 0, 0],
    [20, 20, 90, 90, 40, 30, 20]
);
