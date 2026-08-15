<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\MovimientoForm;
use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Services\InventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MovimientoFormTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $usuario;

    protected Insumo $insumo;

    protected Insumo $insumo2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = Usuario::factory()->create();

        $unidad = UnidadMedida::create(['nombre' => 'Litro', 'abreviatura' => 'L']);

        $this->insumo = Insumo::create([
            'nombre' => 'Aceite Hidraulico',
            'descripcion' => 'Aceite para maquinaria',
            'id_unidad_medida' => $unidad->id_unidad_medida,
        ]);

        $this->insumo2 = Insumo::create([
            'nombre' => 'Grasa',
            'descripcion' => 'Grasa para rodamientos',
            'id_unidad_medida' => $unidad->id_unidad_medida,
        ]);
    }

    private function crearStockDisponible(?int $insumoId = null, float $cantidad = 100, float $precio = 50.0): void
    {
        LoteInventario::create([
            'id_insumo' => $insumoId ?? $this->insumo->id_insumo,
            'cantidad_inicial' => $cantidad,
            'cantidad_disponible' => $cantidad,
            'precio_unitario' => $precio,
            'costo_total' => $cantidad * $precio,
            'fecha_compra' => now()->subDays(5)->toDateString(),
            'agotado' => false,
        ]);
    }

    private function getInsumos()
    {
        return InventarioService::queryInsumosConStockYPrecio()
            ->with('unidadMedida')
            ->orderBy('nombre')
            ->get();
    }

    // ================================================================
    // RENDERING
    // ================================================================

    public function test_component_renders_successfully(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
            ])
            ->assertStatus(200)
            ->assertSee('Movimientos de Insumos');
    }

    public function test_component_shows_empty_state_when_no_movimientos(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
            ])
            ->assertSee('Sin movimientos registrados');
    }

    // ================================================================
    // AGREGAR MOVIMIENTO
    // ================================================================

    public function test_agregar_movimiento_with_valid_data_and_stock(): void
    {
        $this->crearStockDisponible();

        $component = Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
            ])
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->set('movimiento_cantidad', 10)
            ->set('movimiento_motivo', 'Producción')
            ->call('agregarMovimiento');

        $component->assertDispatched('movimientoAgregado');

        $movimientos = $component->get('movimientos');
        $this->assertCount(1, $movimientos);
        $this->assertEquals($this->insumo->id_insumo, $movimientos[0]['id_insumo']);
        $this->assertEquals('Aceite Hidraulico', $movimientos[0]['nombre_insumo']);
        $this->assertEquals(10, $movimientos[0]['cantidad']);
        $this->assertEquals('salida', $movimientos[0]['tipo']);
        $this->assertEquals('Producción', $movimientos[0]['motivo']);
    }

    public function test_agregar_movimiento_resets_form_after_adding(): void
    {
        $this->crearStockDisponible();

        $component = Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
            ])
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->set('movimiento_cantidad', 10)
            ->set('movimiento_motivo', 'Producción')
            ->call('agregarMovimiento');

        $component->assertSet('movimiento_id_insumo', null)
            ->assertSet('movimiento_cantidad', null)
            ->assertSet('movimiento_motivo', 'Producción')
            ->assertSet('stock_disponible_insumo', null);
    }

    public function test_cannot_agregar_movimiento_without_insumo(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
            ])
            ->set('movimiento_cantidad', 10)
            ->set('movimiento_motivo', 'Producción')
            ->call('agregarMovimiento')
            ->assertHasErrors(['movimiento_id_insumo']);
    }

    public function test_cannot_agregar_movimiento_with_zero_quantity(): void
    {
        $this->crearStockDisponible();

        Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
            ])
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->set('movimiento_cantidad', 0)
            ->set('movimiento_motivo', 'Producción')
            ->call('agregarMovimiento')
            ->assertHasErrors(['movimiento_cantidad']);
    }

    public function test_cannot_agregar_movimiento_with_negative_quantity(): void
    {
        $this->crearStockDisponible();

        Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
            ])
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->set('movimiento_cantidad', -5)
            ->set('movimiento_motivo', 'Producción')
            ->call('agregarMovimiento')
            ->assertHasErrors(['movimiento_cantidad']);
    }

    public function test_cannot_agregar_movimiento_with_invalid_motivo(): void
    {
        $this->crearStockDisponible();

        Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
            ])
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->set('movimiento_cantidad', 10)
            ->set('movimiento_motivo', 'Invalido')
            ->call('agregarMovimiento')
            ->assertHasErrors(['movimiento_motivo']);
    }

    public function test_cannot_agregar_movimiento_exceeding_available_stock(): void
    {
        $this->crearStockDisponible(null, 5);

        $component = Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
            ])
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->set('movimiento_cantidad', 10)
            ->set('movimiento_motivo', 'Producción')
            ->call('agregarMovimiento');

        $movimientos = $component->get('movimientos');
        $this->assertEmpty($movimientos);
    }

    public function test_agregar_movimiento_includes_unidad_from_insumo(): void
    {
        $this->crearStockDisponible();

        $component = Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
            ])
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->set('movimiento_cantidad', 10)
            ->set('movimiento_motivo', 'Producción')
            ->call('agregarMovimiento');

        $movimientos = $component->get('movimientos');
        $this->assertEquals('Litro', $movimientos[0]['unidad']);
    }

    // ================================================================
    // ELIMINAR MOVIMIENTO
    // ================================================================

    public function test_eliminar_movimiento_removes_from_array_and_dispatches(): void
    {
        $this->crearStockDisponible();
        $this->crearStockDisponible($this->insumo2->id_insumo, 50, 30.0);

        $component = Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
            ])
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->set('movimiento_cantidad', 10)
            ->set('movimiento_motivo', 'Producción')
            ->call('agregarMovimiento')
            ->set('movimiento_id_insumo', $this->insumo2->id_insumo)
            ->set('movimiento_cantidad', 5)
            ->set('movimiento_motivo', 'Mantenimiento')
            ->call('agregarMovimiento');

        $this->assertCount(2, $component->get('movimientos'));

        $component->call('eliminarMovimiento', 0);

        $component->assertDispatched('movimientoEliminado');

        $movimientos = $component->get('movimientos');
        $this->assertCount(1, $movimientos);
        $this->assertEquals($this->insumo2->id_insumo, $movimientos[0]['id_insumo']);
    }

    public function test_eliminar_movimiento_reindexes_array(): void
    {
        $this->crearStockDisponible();
        $this->crearStockDisponible($this->insumo2->id_insumo, 50, 30.0);

        $component = Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
            ])
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->set('movimiento_cantidad', 10)
            ->set('movimiento_motivo', 'Producción')
            ->call('agregarMovimiento')
            ->set('movimiento_id_insumo', $this->insumo2->id_insumo)
            ->set('movimiento_cantidad', 5)
            ->set('movimiento_motivo', 'Mantenimiento')
            ->call('agregarMovimiento');

        $component->call('eliminarMovimiento', 0);

        $movimientos = $component->get('movimientos');
        $this->assertArrayHasKey(0, $movimientos);
        $this->assertArrayNotHasKey(1, $movimientos);
    }

    // ================================================================
    // STOCK DISPONIBLE (updatedMovimientoIdInsumo)
    // ================================================================

    public function test_updated_movimiento_id_insumo_loads_stock(): void
    {
        $this->crearStockDisponible(null, 75);

        $component = Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
            ])
            ->set('movimiento_id_insumo', $this->insumo->id_insumo);

        $component->assertSet('stock_disponible_insumo', 75);
    }

    public function test_updated_movimiento_id_insumo_clears_stock_when_empty(): void
    {
        $this->crearStockDisponible(null, 75);

        $component = Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
            ])
            ->set('movimiento_id_insumo', $this->insumo->id_insumo)
            ->assertSet('stock_disponible_insumo', 75)
            ->set('movimiento_id_insumo', null)
            ->assertSet('stock_disponible_insumo', null);
    }

    // ================================================================
    // INITIAL MOVIMIENTOS (from parent)
    // ================================================================

    public function test_component_accepts_initial_movimientos(): void
    {
        $initialMovimientos = [
            [
                'id_insumo' => $this->insumo->id_insumo,
                'nombre_insumo' => 'Aceite Hidraulico',
                'tipo' => 'salida',
                'cantidad' => 10,
                'motivo' => 'Producción',
                'observaciones' => null,
                'unidad' => 'Litro',
            ],
        ];

        $component = Livewire::actingAs($this->usuario)
            ->test(MovimientoForm::class, [
                'insumos' => $this->getInsumos(),
                'movimientos' => $initialMovimientos,
            ]);

        $movimientos = $component->get('movimientos');
        $this->assertCount(1, $movimientos);
        $this->assertEquals($this->insumo->id_insumo, $movimientos[0]['id_insumo']);
    }
}
