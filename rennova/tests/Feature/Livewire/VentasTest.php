<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Ventas;
use App\Models\Carga;
use App\Models\CategoriaClientePrecio;
use App\Models\CategoriaMadera;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VentasTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = Usuario::factory()->create();
    }

    // =========================================================================
    // Render / Mount
    // =========================================================================

    public function test_component_renders_successfully(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->assertStatus(200);
    }

    public function test_mount_setea_fechas_ultimos_7_dias(): void
    {
        $component = Livewire::actingAs($this->usuario)
            ->test(Ventas::class);

        $this->assertEquals(
            now()->subDays(7)->format('Y-m-d'),
            $component->get('fecha_desde')
        );
        $this->assertEquals(
            now()->format('Y-m-d'),
            $component->get('fecha_hasta')
        );
    }

    // =========================================================================
    // Búsqueda de cargas
    // =========================================================================

    public function test_buscar_cargas_sin_cliente_falla(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->set('id_cliente', null)
            ->call('buscarCargasPendientes')
            ->assertHasErrors('id_cliente');
    }

    public function test_buscar_cargas_sin_fechas_falla(): void
    {
        $cliente = Cliente::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->set('id_cliente', $cliente->id_cliente)
            ->set('fecha_desde', null)
            ->set('fecha_hasta', null)
            ->call('buscarCargasPendientes')
            ->assertHasErrors(['fecha_desde', 'fecha_hasta']);
    }

    public function test_buscar_cargas_con_cliente_inexistente_falla(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->set('id_cliente', 99999)
            ->set('fecha_desde', now()->subDays(7)->format('Y-m-d'))
            ->set('fecha_hasta', now()->format('Y-m-d'))
            ->call('buscarCargasPendientes')
            ->assertHasErrors('id_cliente');
    }

    public function test_buscar_cargas_pendientes_exitoso(): void
    {
        $cliente = Cliente::factory()->create();
        $categoria = CategoriaMadera::factory()->create();
        $lote = \App\Models\Lote::factory()->create();

        $carga = Carga::factory()->create([
            'id_lote' => $lote->id_lote,
            'id_categoria_madera' => $categoria->id_categoria_madera,
            'id_cliente' => $cliente->id_cliente,
            'estado' => 'pendiente',
            'fecha_carga' => now()->format('Y-m-d'),
        ]);

        CategoriaClientePrecio::factory()->vigente()->create([
            'cliente_id' => $cliente->id_cliente,
            'categoria_id' => $categoria->id_categoria_madera,
            'precio' => 100,
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->set('id_cliente', $cliente->id_cliente)
            ->set('fecha_desde', now()->subDays(7)->format('Y-m-d'))
            ->set('fecha_hasta', now()->format('Y-m-d'))
            ->call('buscarCargasPendientes');

        $this->assertNotEmpty($component->get('detalle_cargas'));
        $this->assertGreaterThan(0, $component->get('total_venta'));
    }

    public function test_buscar_cargas_sin_resultados(): void
    {
        $cliente = Cliente::factory()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->set('id_cliente', $cliente->id_cliente)
            ->set('fecha_desde', now()->subDays(7)->format('Y-m-d'))
            ->set('fecha_hasta', now()->format('Y-m-d'))
            ->call('buscarCargasPendientes');

        $this->assertEmpty($component->get('detalle_cargas'));
        $this->assertEquals(0, $component->get('total_venta'));
    }

    public function test_precio_correcto_segun_vigencia(): void
    {
        $cliente = Cliente::factory()->create();
        $categoria = CategoriaMadera::factory()->create();
        $lote = \App\Models\Lote::factory()->create();

        Carga::factory()->create([
            'id_lote' => $lote->id_lote,
            'id_categoria_madera' => $categoria->id_categoria_madera,
            'id_cliente' => $cliente->id_cliente,
            'estado' => 'pendiente',
            'fecha_carga' => now()->format('Y-m-d'),
            'peso_neto' => 10000,
        ]);

        CategoriaClientePrecio::factory()->create([
            'cliente_id' => $cliente->id_cliente,
            'categoria_id' => $categoria->id_categoria_madera,
            'precio' => 250,
            'fecha_desde' => now()->subMonth(),
            'fecha_hasta' => null,
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->set('id_cliente', $cliente->id_cliente)
            ->set('fecha_desde', now()->subDays(7)->format('Y-m-d'))
            ->set('fecha_hasta', now()->format('Y-m-d'))
            ->call('buscarCargasPendientes');

        $cargas = $component->get('detalle_cargas');
        $this->assertEquals(250, $cargas[0]['precio_unitario']);
    }

    // =========================================================================
    // Guardar venta
    // =========================================================================

    public function test_guardar_venta_exitosa(): void
    {
        $cliente = Cliente::factory()->create();
        $categoria = CategoriaMadera::factory()->create();
        $lote = \App\Models\Lote::factory()->create();

        $carga = Carga::factory()->create([
            'id_lote' => $lote->id_lote,
            'id_categoria_madera' => $categoria->id_categoria_madera,
            'id_cliente' => $cliente->id_cliente,
            'estado' => 'pendiente',
            'peso_neto' => 10000,
        ]);

        $detalleCargas = [[
            'id_carga' => $carga->id_carga,
            'precio_unitario' => 100,
            'peso_toneladas' => 10,
            'subtotal' => 1000,
        ]];

        Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->set('id_cliente', $cliente->id_cliente)
            ->set('detalle_cargas', $detalleCargas)
            ->set('total_venta', 1000)
            ->set('observaciones', 'Venta de prueba')
            ->call('guardarVenta');

        $this->assertDatabaseHas('ventas', [
            'id_cliente' => $cliente->id_cliente,
            'monto' => 1000,
        ]);
    }

    public function test_guardar_venta_sin_cargas_falla(): void
    {
        $cliente = Cliente::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->set('id_cliente', $cliente->id_cliente)
            ->set('detalle_cargas', [])
            ->call('guardarVenta');

        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_guardar_venta_sin_cliente_falla(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->set('id_cliente', null)
            ->set('detalle_cargas', [['id_carga' => 1]])
            ->call('guardarVenta')
            ->assertHasErrors('id_cliente');
    }

    // =========================================================================
    // Dar de baja
    // =========================================================================

    public function test_dar_de_baja_venta_soft_delete(): void
    {
        $cliente = Cliente::factory()->create();
        $venta = Venta::factory()->create(['id_cliente' => $cliente->id_cliente]);

        Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->call('darDeBaja', $venta->id_recibo);

        $this->assertSoftDeleted('ventas', ['id_recibo' => $venta->id_recibo]);
    }

    public function test_dar_de_baja_revierte_cargas_a_pendiente(): void
    {
        $cliente = Cliente::factory()->create();
        $categoria = CategoriaMadera::factory()->create();
        $lote = \App\Models\Lote::factory()->create();

        $carga = Carga::factory()->create([
            'id_lote' => $lote->id_lote,
            'id_categoria_madera' => $categoria->id_categoria_madera,
            'id_cliente' => $cliente->id_cliente,
            'estado' => 'facturada',
        ]);

        $venta = Venta::factory()->create(['id_cliente' => $cliente->id_cliente]);
        $venta->cargas()->attach($carga->id_carga, [
            'precio_unitario' => 100,
            'peso_toneladas' => 10,
            'subtotal' => 1000,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->call('darDeBaja', $venta->id_recibo);

        $carga->refresh();
        $this->assertEquals('pendiente', $carga->estado);
    }

    // =========================================================================
    // Detalle / Edición
    // =========================================================================

    public function test_ver_detalle_carga_datos(): void
    {
        $cliente = Cliente::factory()->create();
        $categoria = CategoriaMadera::factory()->create();
        $lote = \App\Models\Lote::factory()->create();

        $carga = Carga::factory()->create([
            'id_lote' => $lote->id_lote,
            'id_categoria_madera' => $categoria->id_categoria_madera,
            'id_cliente' => $cliente->id_cliente,
            'estado' => 'facturada',
        ]);

        $venta = Venta::factory()->create(['id_cliente' => $cliente->id_cliente]);
        $venta->cargas()->attach($carga->id_carga, [
            'precio_unitario' => 100,
            'peso_toneladas' => 10,
            'subtotal' => 1000,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->call('verDetalle', $venta->id_recibo)
            ->assertSet('mostrar_modal', true)
            ->assertSet('venta_seleccionada.id_recibo', $venta->id_recibo);
    }

    public function test_activar_edicion_carga_valores(): void
    {
        $cliente = Cliente::factory()->create();
        $venta = Venta::factory()->create([
            'id_cliente' => $cliente->id_cliente,
            'observaciones' => 'Original',
            'monto' => 1000,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->call('verDetalle', $venta->id_recibo)
            ->call('activarEdicion')
            ->assertSet('modo_edicion', true);
    }

    public function test_guardar_edicion_actualiza_observaciones_y_monto(): void
    {
        $cliente = Cliente::factory()->create();
        $venta = Venta::factory()->create([
            'id_cliente' => $cliente->id_cliente,
            'observaciones' => 'Original',
            'monto' => 1000,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->call('verDetalle', $venta->id_recibo)
            ->call('activarEdicion')
            ->set('obs_edicion', 'Editado')
            ->set('monto_edicion', 2000)
            ->call('guardarEdicion');

        $venta->refresh();
        $this->assertEquals('Editado', $venta->observaciones);
        $this->assertEquals(2000, $venta->monto);
    }

    public function test_cancelar_edicion_restaura_valores(): void
    {
        $cliente = Cliente::factory()->create();
        $venta = Venta::factory()->create([
            'id_cliente' => $cliente->id_cliente,
            'observaciones' => 'Original',
            'monto' => 1000,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->call('verDetalle', $venta->id_recibo)
            ->call('activarEdicion')
            ->set('obs_edicion', 'Editado')
            ->set('monto_edicion', 2000)
            ->call('cancelarEdicion')
            ->assertSet('modo_edicion', false)
            ->assertSet('obs_edicion', 'Original')
            ->assertSet('monto_edicion', 1000);
    }

    public function test_busqueda_en_historial_filtra(): void
    {
        $cliente1 = Cliente::factory()->create(['razon_social' => 'Maderera del Sur']);
        $cliente2 = Cliente::factory()->create(['razon_social' => 'Aserraderos Norte']);

        Venta::factory()->create(['id_cliente' => $cliente1->id_cliente]);
        Venta::factory()->create(['id_cliente' => $cliente2->id_cliente]);

        $component = Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->set('busqueda', 'Maderera del Sur');

        $ventas = $component->viewData('ventas');
        $this->assertTrue($ventas->contains('id_cliente', $cliente1->id_cliente));
    }

    // =========================================================================
    // Edge cases
    // =========================================================================

    public function test_precio_cero_si_no_hay_precio_categoria(): void
    {
        $cliente = Cliente::factory()->create();
        $categoria = CategoriaMadera::factory()->create();
        $lote = \App\Models\Lote::factory()->create();

        Carga::factory()->create([
            'id_lote' => $lote->id_lote,
            'id_categoria_madera' => $categoria->id_categoria_madera,
            'id_cliente' => $cliente->id_cliente,
            'estado' => 'pendiente',
            'fecha_carga' => now()->format('Y-m-d'),
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->set('id_cliente', $cliente->id_cliente)
            ->set('fecha_desde', now()->subDays(7)->format('Y-m-d'))
            ->set('fecha_hasta', now()->format('Y-m-d'))
            ->call('buscarCargasPendientes');

        $cargas = $component->get('detalle_cargas');
        $this->assertNotEmpty($cargas);
        $this->assertEquals(0, $cargas[0]['precio_unitario']);
    }

    public function test_transaccion_rollback_si_falla(): void
    {
        $cliente = Cliente::factory()->create();

        $detalleCargas = [[
            'id_carga' => 99999,
            'precio_unitario' => 100,
            'peso_toneladas' => 10,
            'subtotal' => 1000,
        ]];

        Livewire::actingAs($this->usuario)
            ->test(Ventas::class)
            ->set('id_cliente', $cliente->id_cliente)
            ->set('detalle_cargas', $detalleCargas)
            ->set('total_venta', 1000)
            ->call('guardarVenta');

        $this->assertDatabaseCount('ventas', 0);
    }
}
