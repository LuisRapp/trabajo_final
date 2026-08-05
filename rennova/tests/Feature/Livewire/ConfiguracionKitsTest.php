<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\ConfiguracionKits;
use App\Models\Insumo;
use App\Models\KitMantenimientoPreventivo;
use App\Models\Maquinaria;
use App\Models\TipoMaquinaria;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConfiguracionKitsTest extends TestCase
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
            ->test(ConfiguracionKits::class)
            ->assertStatus(200);
    }

    public function test_mount_carga_kits_registrados(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $insumo = Insumo::factory()->create();

        KitMantenimientoPreventivo::create([
            'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_insumo' => $insumo->id_insumo,
            'cantidad_requerida' => 5,
            'es_obligatorio' => true,
        ]);

        $component = Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class);

        $component->assertSet('kits_registrados', function ($kits) use ($maquinaria) {
            return $kits->has($maquinaria->id_maquinaria);
        });
    }

    // =========================================================================
    // Guardar item
    // =========================================================================

    public function test_crear_item_de_kit_exitoso(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $insumo = Insumo::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->set('maquinaria_seleccionada', $maquinaria->id_maquinaria)
            ->call('abrirModalAgregar')
            ->set('insumo_id', $insumo->id_insumo)
            ->set('cantidad_requerida', 5)
            ->set('es_obligatorio', true)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kit_mantenimiento_preventivo', [
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_insumo' => $insumo->id_insumo,
            'cantidad_requerida' => 5,
            'es_obligatorio' => true,
            'deleted_at' => null,
        ]);
    }

    public function test_editar_item_existente(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $insumo = Insumo::factory()->create();
        $insumo2 = Insumo::factory()->create();

        $item = KitMantenimientoPreventivo::create([
            'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_insumo' => $insumo->id_insumo,
            'cantidad_requerida' => 5,
            'es_obligatorio' => true,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->set('maquinaria_seleccionada', $maquinaria->id_maquinaria)
            ->call('abrirModalEditar', $item->id_kit)
            ->set('insumo_id', $insumo2->id_insumo)
            ->set('cantidad_requerida', 10)
            ->set('es_obligatorio', false)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kit_mantenimiento_preventivo', [
            'id_kit' => $item->id_kit,
            'id_insumo' => $insumo2->id_insumo,
            'cantidad_requerida' => 10,
            'es_obligatorio' => false,
        ]);
    }

    public function test_duplicado_mismo_insumo_falla(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $insumo = Insumo::factory()->create();

        KitMantenimientoPreventivo::create([
            'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_insumo' => $insumo->id_insumo,
            'cantidad_requerida' => 5,
            'es_obligatorio' => true,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->set('maquinaria_seleccionada', $maquinaria->id_maquinaria)
            ->call('abrirModalAgregar')
            ->set('insumo_id', $insumo->id_insumo)
            ->set('cantidad_requerida', 3)
            ->set('es_obligatorio', false)
            ->call('guardar');

        $this->assertCount(1, KitMantenimientoPreventivo::where('id_maquinaria', $maquinaria->id_maquinaria)->where('id_insumo', $insumo->id_insumo)->whereNull('deleted_at')->get());
    }

    public function test_validacion_cantidad_minima(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $insumo = Insumo::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->set('maquinaria_seleccionada', $maquinaria->id_maquinaria)
            ->call('abrirModalAgregar')
            ->set('insumo_id', $insumo->id_insumo)
            ->set('cantidad_requerida', 0)
            ->set('es_obligatorio', true)
            ->call('guardar')
            ->assertHasErrors(['cantidad_requerida' => 'min']);
    }

    // =========================================================================
    // Eliminar item
    // =========================================================================

    public function test_eliminar_item_hace_soft_delete(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $insumo = Insumo::factory()->create();

        $item = KitMantenimientoPreventivo::create([
            'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_insumo' => $insumo->id_insumo,
            'cantidad_requerida' => 5,
            'es_obligatorio' => true,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->set('maquinaria_seleccionada', $maquinaria->id_maquinaria)
            ->call('eliminar', $item->id_kit);

        $this->assertSoftDeleted('kit_mantenimiento_preventivo', ['id_kit' => $item->id_kit]);
    }

    public function test_eliminar_item_inexistente_no_falla(): void
    {
        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->call('eliminar', 99999)
            ->assertStatus(200);
    }

    // =========================================================================
    // Restaurar item
    // =========================================================================

    public function test_restaurar_item_exitoso(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $insumo = Insumo::factory()->create();

        $item = KitMantenimientoPreventivo::create([
            'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_insumo' => $insumo->id_insumo,
            'cantidad_requerida' => 5,
            'es_obligatorio' => true,
        ]);

        $item->delete();

        $this->assertSoftDeleted('kit_mantenimiento_preventivo', ['id_kit' => $item->id_kit]);

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->set('maquinaria_seleccionada', $maquinaria->id_maquinaria)
            ->call('restaurar', $item->id_kit);

        $this->assertNull($item->fresh()->deleted_at);
    }

    public function test_restaurar_item_no_trashed_no_falla(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $insumo = Insumo::factory()->create();

        $item = KitMantenimientoPreventivo::create([
            'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_insumo' => $insumo->id_insumo,
            'cantidad_requerida' => 5,
            'es_obligatorio' => true,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->set('maquinaria_seleccionada', $maquinaria->id_maquinaria)
            ->call('restaurar', $item->id_kit)
            ->assertStatus(200);

        $this->assertNull($item->fresh()->deleted_at);
    }

    // =========================================================================
    // Eliminar kit completo
    // =========================================================================

    public function test_eliminar_kit_completo_elimina_todos_los_items(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $insumo1 = Insumo::factory()->create();
        $insumo2 = Insumo::factory()->create();

        $item1 = KitMantenimientoPreventivo::create([
            'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_insumo' => $insumo1->id_insumo,
            'cantidad_requerida' => 5,
            'es_obligatorio' => true,
        ]);

        $item2 = KitMantenimientoPreventivo::create([
            'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_insumo' => $insumo2->id_insumo,
            'cantidad_requerida' => 3,
            'es_obligatorio' => false,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->call('eliminarKit', $maquinaria->id_maquinaria);

        $this->assertSoftDeleted('kit_mantenimiento_preventivo', ['id_kit' => $item1->id_kit]);
        $this->assertSoftDeleted('kit_mantenimiento_preventivo', ['id_kit' => $item2->id_kit]);
    }

    public function test_eliminar_kit_vacio_no_falla(): void
    {
        $maquinaria = Maquinaria::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->call('eliminarKit', $maquinaria->id_maquinaria)
            ->assertStatus(200);
    }

    // =========================================================================
    // Cargar items
    // =========================================================================

    public function test_al_seleccionar_maquinaria_carga_items(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $insumo = Insumo::factory()->create();

        KitMantenimientoPreventivo::create([
            'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_insumo' => $insumo->id_insumo,
            'cantidad_requerida' => 5,
            'es_obligatorio' => true,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->set('maquinaria_seleccionada', $maquinaria->id_maquinaria)
            ->assertSet('items_kit', function ($items) {
                return count($items) === 1;
            });
    }

    public function test_maquinaria_sin_kit_muestra_lista_vacia(): void
    {
        $maquinaria = Maquinaria::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->set('maquinaria_seleccionada', $maquinaria->id_maquinaria)
            ->assertSet('items_kit', function ($items) {
                return count($items) === 0;
            });
    }

    // =========================================================================
    // Editar kit
    // =========================================================================

    public function test_editar_kit_carga_datos_en_formulario(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $insumo = Insumo::factory()->create();

        KitMantenimientoPreventivo::create([
            'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
            'id_maquinaria' => $maquinaria->id_maquinaria,
            'id_insumo' => $insumo->id_insumo,
            'cantidad_requerida' => 5,
            'es_obligatorio' => true,
        ]);

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->call('editarKit', $maquinaria->id_maquinaria)
            ->assertSet('maquinaria_seleccionada', $maquinaria->id_maquinaria)
            ->assertSet('editando_kit', true);
    }

    // =========================================================================
    // Validaciones
    // =========================================================================

    public function test_validacion_insumo_requerido(): void
    {
        $maquinaria = Maquinaria::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->set('maquinaria_seleccionada', $maquinaria->id_maquinaria)
            ->call('abrirModalAgregar')
            ->set('insumo_id', '')
            ->set('cantidad_requerida', 5)
            ->call('guardar')
            ->assertHasErrors(['insumo_id' => 'required']);
    }

    public function test_validacion_cantidad_minima_0_01(): void
    {
        $maquinaria = Maquinaria::factory()->create();
        $insumo = Insumo::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->set('maquinaria_seleccionada', $maquinaria->id_maquinaria)
            ->call('abrirModalAgregar')
            ->set('insumo_id', $insumo->id_insumo)
            ->set('cantidad_requerida', 0.001)
            ->call('guardar')
            ->assertHasErrors(['cantidad_requerida' => 'min']);
    }

    public function test_validacion_insumo_debe_existir(): void
    {
        $maquinaria = Maquinaria::factory()->create();

        Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class)
            ->set('maquinaria_seleccionada', $maquinaria->id_maquinaria)
            ->call('abrirModalAgregar')
            ->set('insumo_id', 99999)
            ->set('cantidad_requerida', 5)
            ->call('guardar')
            ->assertHasErrors(['insumo_id' => 'exists']);
    }

    // =========================================================================
    // Computed properties
    // =========================================================================

    public function test_computed_maquinarias_retorna_lista(): void
    {
        $maq1 = Maquinaria::factory()->create();
        $maq2 = Maquinaria::factory()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class);

        $viewData = $component->viewData('maquinarias');
        $this->assertTrue($viewData->contains('id_maquinaria', $maq1->id_maquinaria));
        $this->assertTrue($viewData->contains('id_maquinaria', $maq2->id_maquinaria));
    }

    public function test_computed_insumos_retorna_lista(): void
    {
        $insumo1 = Insumo::factory()->create();
        $insumo2 = Insumo::factory()->create();

        $component = Livewire::actingAs($this->usuario)
            ->test(ConfiguracionKits::class);

        $viewData = $component->viewData('insumos');
        $this->assertTrue($viewData->contains('id_insumo', $insumo1->id_insumo));
        $this->assertTrue($viewData->contains('id_insumo', $insumo2->id_insumo));
    }
}
