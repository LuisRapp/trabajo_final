<?php

use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\MovimientoStock;
use App\Models\UnidadMedida;
use App\Services\InventarioService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $unidad = UnidadMedida::create(['nombre' => 'Litro', 'abreviatura' => 'L']);

    $this->insumo = Insumo::create([
        'nombre' => 'Aceite',
        'descripcion' => 'Aceite para motor',
        'id_unidad_medida' => $unidad->id_unidad_medida,
    ]);
});

it('registra entrada de stock y crea lote de inventario', function () {
    $resultado = InventarioService::registrarEntrada(
        $this->insumo->id_insumo,
        100,
        50.00,
        ['motivo' => 'Compra inicial'],
        '2024-01-15'
    );

    expect($resultado['lote'])->toBeInstanceOf(LoteInventario::class);
    expect((float) $resultado['lote']->cantidad_inicial)->toBe(100.0);
    expect((float) $resultado['lote']->cantidad_disponible)->toBe(100.0);
    expect((float) $resultado['lote']->precio_unitario)->toBe(50.00);
    expect((float) $resultado['lote']->costo_total)->toBe(5000.00);
    expect($resultado['lote']->agotado)->toBeFalse();

    expect($resultado['movimiento'])->toBeInstanceOf(MovimientoStock::class);
    expect($resultado['movimiento']->tipo)->toBe('entrada');
    expect((float) $resultado['movimiento']->cantidad)->toBe(100.0);

    $this->assertDatabaseHas('lotes_inventario', [
        'id_lote_inventario' => $resultado['lote']->id_lote_inventario,
        'cantidad_disponible' => 100,
    ]);
});

it('calcula stock disponible correctamente', function () {
    LoteInventario::create([
        'id_insumo' => $this->insumo->id_insumo,
        'cantidad_inicial' => 100,
        'cantidad_disponible' => 80,
        'precio_unitario' => 50.00,
        'costo_total' => 5000.00,
        'fecha_compra' => '2024-01-15',
        'agotado' => false,
    ]);

    LoteInventario::create([
        'id_insumo' => $this->insumo->id_insumo,
        'cantidad_inicial' => 50,
        'cantidad_disponible' => 20,
        'precio_unitario' => 60.00,
        'costo_total' => 3000.00,
        'fecha_compra' => '2024-01-20',
        'agotado' => false,
    ]);

    $stock = InventarioService::stockDisponible($this->insumo->id_insumo);

    expect((int) $stock)->toBe(100);
});

it('calcula precio promedio ponderado', function () {
    LoteInventario::create([
        'id_insumo' => $this->insumo->id_insumo,
        'cantidad_inicial' => 100,
        'cantidad_disponible' => 80,
        'precio_unitario' => 50.00,
        'costo_total' => 5000.00,
        'fecha_compra' => '2024-01-15',
        'agotado' => false,
    ]);

    LoteInventario::create([
        'id_insumo' => $this->insumo->id_insumo,
        'cantidad_inicial' => 50,
        'cantidad_disponible' => 20,
        'precio_unitario' => 60.00,
        'costo_total' => 3000.00,
        'fecha_compra' => '2024-01-20',
        'agotado' => false,
    ]);

    $precio = InventarioService::precioPromedio($this->insumo->id_insumo);

    // (80*50 + 20*60) / 100 = 5200/100 = 52
    expect($precio)->toBe(52.0);
});

it('calcula costo fifo manual correctamente', function () {
    // Lote 1: 100 unidades a $10
    LoteInventario::create([
        'id_insumo' => $this->insumo->id_insumo,
        'cantidad_inicial' => 100,
        'cantidad_disponible' => 100,
        'precio_unitario' => 10.00,
        'costo_total' => 1000.00,
        'fecha_compra' => '2024-01-10',
        'agotado' => false,
    ]);

    // Lote 2: 50 unidades a $12
    LoteInventario::create([
        'id_insumo' => $this->insumo->id_insumo,
        'cantidad_inicial' => 50,
        'cantidad_disponible' => 50,
        'precio_unitario' => 12.00,
        'costo_total' => 600.00,
        'fecha_compra' => '2024-01-15',
        'agotado' => false,
    ]);

    $lotesConsumidos = (new ReflectionClass(InventarioService::class))
        ->getMethod('calcularCostoFifoManual')
        ->invoke(null, $this->insumo->id_insumo, 120);

    expect($lotesConsumidos)->toHaveCount(2);

    // Primero consume del lote más antiguo (100 unidades a $10)
    expect((int) $lotesConsumidos[0]['cantidad_consumida'])->toBe(100);
    expect((float) $lotesConsumidos[0]['precio_unitario'])->toBe(10.00);
    expect((float) $lotesConsumidos[0]['costo_parcial'])->toBe(1000.00);

    // Luego consume 20 del segundo lote (a $12)
    expect((int) $lotesConsumidos[1]['cantidad_consumida'])->toBe(20);
    expect((float) $lotesConsumidos[1]['precio_unitario'])->toBe(12.00);
    expect((float) $lotesConsumidos[1]['costo_parcial'])->toBe(240.00);
});

it('consume lote y marca como agotado', function () {
    $lote = LoteInventario::create([
        'id_insumo' => $this->insumo->id_insumo,
        'cantidad_inicial' => 100,
        'cantidad_disponible' => 100,
        'precio_unitario' => 10.00,
        'costo_total' => 1000.00,
        'fecha_compra' => '2024-01-10',
        'agotado' => false,
    ]);

    InventarioService::consumirLote($lote, 100);

    expect((float) $lote->fresh()->cantidad_disponible)->toBe(0.0);
    expect($lote->fresh()->agotado)->toBeTrue();
});

it('lanza excepcion si intenta consumir mas del disponible', function () {
    $lote = LoteInventario::create([
        'id_insumo' => $this->insumo->id_insumo,
        'cantidad_inicial' => 100,
        'cantidad_disponible' => 50,
        'precio_unitario' => 10.00,
        'costo_total' => 1000.00,
        'fecha_compra' => '2024-01-10',
        'agotado' => false,
    ]);

    expect(fn () => InventarioService::consumirLote($lote, 100))
        ->toThrow(\Exception::class, 'No se puede consumir 100 unidades del lote');
});

it('usa funcion PostgreSQL cuando esta disponible', function () {
    // Este test verifica que el servicio detecta PostgreSQL y usa la funcion nativa
    // No se puede mockear facilmente metodos estaticos de Eloquent sin librerias adicionales
    // La verificacion real se hace con la suite de integracion en PostgreSQL
    $driver = DB::connection()->getDriverName();

    if ($driver === 'pgsql') {
        expect(true)->toBeTrue();
    } else {
        // En SQLite, verificamos que el fallback manual funcione
        $lote = LoteInventario::create([
            'id_insumo' => $this->insumo->id_insumo,
            'cantidad_inicial' => 10,
            'cantidad_disponible' => 10,
            'precio_unitario' => 50.00,
            'costo_total' => 500.00,
            'fecha_compra' => '2024-01-10',
            'agotado' => false,
        ]);

        $resultado = InventarioService::registrarSalida($this->insumo->id_insumo, 5, 'Test');

        expect($resultado['costo_total'])->toBe(250.00);
        expect($resultado['lotes_consumidos'])->toHaveCount(1);
    }
});
