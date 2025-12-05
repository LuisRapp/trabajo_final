<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Lote;
use App\Models\Maquinaria;
use App\Models\TipoMaquinaria;
use App\Models\Empleado;
use App\Models\RolLaboral;
use App\Models\ParteDiario;
use App\Models\Carga;
use App\Models\Cliente;
use App\Models\Proveedor;
use App\Models\Insumo;
use App\Models\UnidadMedida;
use App\Models\HistoricoRolLaboral;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;

class ControllerHttpTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $usuario;
    protected Lote $lote;
    protected Maquinaria $maquinaria;
    protected TipoMaquinaria $tipoMaquinaria;
    protected RolLaboral $rolLaboral;
    protected Empleado $empleado;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::factory()->create();
        $this->actingAs($this->usuario);

        $this->tipoMaquinaria = TipoMaquinaria::create([
            'nombre' => 'Cosechadora',
            'descripcion' => 'Máquina cosechadora forestal'
        ]);

        $this->maquinaria = Maquinaria::create([
            'id_tipo_maquinaria' => $this->tipoMaquinaria->id_tipo_maquinaria,
            'modelo' => 'CAT 320',
            'estado' => 'operativo',
            'es_alquilada' => false,
            'fecha_inicio_actividades' => now(),
            'toneladas_acumuladas' => 100,
            'umbral_toneladas' => 500
        ]);

        $this->rolLaboral = RolLaboral::create([
            'nombre' => 'Operario',
            'descripcion' => 'Operario general',
            'valor_jornal' => 1000,
            'tarifa_fija_por_tonelada' => 50
        ]);

        HistoricoRolLaboral::create([
            'rol_laboral_id' => $this->rolLaboral->id_rol_laboral,
            'valor_jornal' => 1000,
            'tarifa_fija_por_tonelada' => 50,
            'fecha_inicio' => now()->subMonths(6),
            'fecha_fin' => null
        ]);

        $this->lote = Lote::create([
            'propietario' => 'Propietario Test',
            'condicion_compra' => 'comprado',
            'estado' => 'activo',
            'ubicacion' => 'Misiones',
            'especie' => 'Pino',
            'superficie' => 100,
            'latitud' => -27.3612,
            'longitud' => -55.5116
        ]);

        $this->empleado = Empleado::create([
            'id_rol_laboral' => $this->rolLaboral->id_rol_laboral,
            'dni' => '12345678',
            'apellido' => 'Pérez',
            'nombre' => 'Juan',
            'fecha_nacimiento' => '1990-01-15',
            'fecha_inicio_actividades' => now()->subYear(),
            'fecha_fin_actividades' => null
        ]);

        Log::info('═══ PRUEBAS DE CONTROLADORES HTTP ═══');
    }

    // ============================================================================
    // PRUEBAS DE VISTA - LOTES
    // ============================================================================

    public function test_ver_lista_lotes()
    {
        Log::info('TEST HTTP: Ver lista de lotes', ['test' => 'test_ver_lista_lotes']);

        $response = $this->get('/lotes');

        $response->assertStatus(200);
        $response->assertViewIs('lotes.index');

        Log::info('✓ ÉXITO: Lista de lotes accesible', ['status' => 200]);
    }

    public function test_ver_lista_maquinaria()
    {
        Log::info('TEST HTTP: Ver lista de maquinaria', ['test' => 'test_ver_lista_maquinaria']);

        $response = $this->get('/maquinarias');

        $response->assertStatus(200);

        Log::info('✓ ÉXITO: Lista de maquinaria accesible', ['status' => 200]);
    }

    public function test_ver_lista_empleados()
    {
        Log::info('TEST HTTP: Ver lista de empleados', ['test' => 'test_ver_lista_empleados']);

        $response = $this->get('/empleados');

        $response->assertStatus(200);

        Log::info('✓ ÉXITO: Lista de empleados accesible', ['status' => 200]);
    }

    public function test_ver_lista_insumos()
    {
        Log::info('TEST HTTP: Ver lista de insumos', ['test' => 'test_ver_lista_insumos']);

        $response = $this->get('/insumos');

        $response->assertStatus(200);

        Log::info('✓ ÉXITO: Lista de insumos accesible', ['status' => 200]);
    }

    // ============================================================================
    // PRUEBAS DE DASHBOARD Y VISTAS MÓDULOS
    // ============================================================================

    public function test_dashboard_accesible()
    {
        Log::info('TEST HTTP: Dashboard accesible', ['test' => 'test_dashboard_accesible']);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);

        Log::info('✓ ÉXITO: Dashboard accesible', ['status' => 200]);
    }

    public function test_modulo_maquinaria_accesible()
    {
        Log::info('TEST HTTP: Módulo de maquinaria accesible', ['test' => 'test_modulo_maquinaria_accesible']);

        $response = $this->get('/modulos/maquinaria');

        $response->assertStatus(200);

        Log::info('✓ ÉXITO: Módulo de maquinaria accesible', ['status' => 200]);
    }

    public function test_modulo_operaciones_accesible()
    {
        Log::info('TEST HTTP: Módulo de operaciones accesible', ['test' => 'test_modulo_operaciones_accesible']);

        $response = $this->get('/modulos/operaciones');

        $response->assertStatus(200);

        Log::info('✓ ÉXITO: Módulo de operaciones accesible', ['status' => 200]);
    }

    public function test_notificaciones_accesible()
    {
        Log::info('TEST HTTP: Notificaciones accesible', ['test' => 'test_notificaciones_accesible']);

        $response = $this->get('/notificaciones');

        $response->assertStatus(200);

        Log::info('✓ ÉXITO: Notificaciones accesible', ['status' => 200]);
    }

    public function test_mantenimientos_accesible()
    {
        Log::info('TEST HTTP: Mantenimientos accesible', ['test' => 'test_mantenimientos_accesible']);

        $response = $this->get('/mantenimientos');

        $response->assertStatus(200);

        Log::info('✓ ÉXITO: Mantenimientos accesible', ['status' => 200]);
    }

    // ============================================================================
    // PRUEBAS SIN AUTENTICACIÓN
    // ============================================================================

    public function test_lotes_sin_autenticacion()
    {
        Log::info('TEST HTTP: Lotes sin autenticación', ['test' => 'test_lotes_sin_autenticacion']);

        $this->signOut();

        $response = $this->get('/lotes');

        // Debería redirigir a login
        $response->assertRedirect('/login');

        Log::info('✓ ÉXITO: Protección correcta sin autenticación', ['redirige_a' => '/login']);
    }

    protected function signOut()
    {
        $this->post('/logout');
    }
}
