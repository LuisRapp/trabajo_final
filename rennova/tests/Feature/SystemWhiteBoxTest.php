<?php

namespace Tests\Feature;

use App\Models\Carga;
use App\Models\Empleado;
use App\Models\HistoricoRolLaboral;
use App\Models\Insumo;
use App\Models\KitMantenimientoPreventivo;
use App\Models\Lote;
use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\MovimientoStock;
use App\Models\NotificacionSistema;
use App\Models\ParteDiario;
use App\Models\RolLaboral;
use App\Models\TipoMantenimiento;
use App\Models\TipoMaquinaria;
use App\Models\Usuario;
use App\Services\ClimaDecisionService;
use App\Services\EmpleadoPagoService;
use App\Services\ForestalStatsService;
use App\Services\MantenimientoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SystemWhiteBoxTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected Usuario $usuario;

    protected Lote $lote;

    protected TipoMaquinaria $tipoMaquinaria;

    protected Maquinaria $maquinaria;

    protected RolLaboral $rolLaboral;

    protected Empleado $empleado;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuario de prueba
        $this->usuario = Usuario::factory()->create();
        $this->actingAs($this->usuario);

        // Crear configuración de tipo de maquinaria
        $this->tipoMaquinaria = TipoMaquinaria::create([
            'nombre' => 'Cosechadora',
            'descripcion' => 'Máquina cosechadora forestal',
        ]);

        // Crear maquinaria
        $this->maquinaria = Maquinaria::create([
            'id_tipo_maquinaria' => $this->tipoMaquinaria->id_tipo_maquinaria,
            'modelo' => 'CAT 320',
            'estado' => 'operativo',
            'es_alquilada' => false,
            'fecha_inicio_actividades' => now(),
            'toneladas_acumuladas' => 100,
            'umbral_toneladas' => 500,
        ]);

        // Crear rol laboral
        $this->rolLaboral = RolLaboral::create([
            'nombre' => 'Operario',
            'descripcion' => 'Operario general',
            'valor_jornal' => 1000,
            'tarifa_fija_por_tonelada' => 50,
        ]);

        // Registrar histórico de rol
        HistoricoRolLaboral::create([
            'rol_laboral_id' => $this->rolLaboral->id_rol_laboral,
            'valor_jornal' => 1000,
            'tarifa_fija_por_tonelada' => 50,
            'fecha_inicio' => now()->subMonths(6),
            'fecha_fin' => null,
        ]);

        // Crear lote
        $this->lote = Lote::create([
            'propietario' => 'Propietario Test',
            'condicion_compra' => 'comprado',
            'estado' => 'activo',
            'ubicacion' => 'Misiones',
            'especie' => 'Pino',
            'superficie' => 100,
            'latitud' => -27.3612,
            'longitud' => -55.5116,
        ]);

        // Crear empleado
        $this->empleado = Empleado::create([
            'id_rol_laboral' => $this->rolLaboral->id_rol_laboral,
            'dni' => '12345678',
            'apellido' => 'Pérez',
            'nombre' => 'Juan',
            'fecha_nacimiento' => '1990-01-15',
            'fecha_inicio_actividades' => now()->subYear(),
            'fecha_fin_actividades' => null,
        ]);

        Log::info('═══ INICIALIZACIÓN DE DATOS DE PRUEBA ═══', [
            'usuario_id' => $this->usuario->id,
            'lote_id' => $this->lote->id_lote,
            'maquinaria_id' => $this->maquinaria->id_maquinaria,
            'empleado_id' => $this->empleado->id_empleado,
        ]);
    }

    // ============================================================================
    // PRUEBAS CRUD - LOTES
    // ============================================================================

    public function test_crear_lote()
    {
        Log::info('TEST: Crear Lote', ['test' => 'test_crear_lote']);

        $datos = [
            'propietario' => 'Nuevo Propietario',
            'condicion_compra' => 'arrendado',
            'estado' => 'activo',
            'ubicacion' => 'Corrientes',
            'especie' => 'Eucalipto',
            'superficie' => 50,
            'latitud' => -29.1234,
            'longitud' => -56.7890,
        ];

        $lote = Lote::create($datos);

        $this->assertDatabaseHas('lotes', ['propietario' => 'Nuevo Propietario']);
        $this->assertEquals('arrendado', $lote->condicion_compra);
        $this->assertEquals(50, $lote->superficie);

        Log::info('✓ ÉXITO: Lote creado correctamente', [
            'lote_id' => $lote->id_lote,
            'datos' => $datos,
        ]);
    }

    public function test_actualizar_lote()
    {
        Log::info('TEST: Actualizar Lote', ['test' => 'test_actualizar_lote']);

        $this->lote->update([
            'estado' => 'inactivo',
            'superficie' => 150,
        ]);

        $this->assertDatabaseHas('lotes', [
            'id_lote' => $this->lote->id_lote,
            'estado' => 'inactivo',
            'superficie' => 150,
        ]);

        Log::info('✓ ÉXITO: Lote actualizado correctamente', [
            'lote_id' => $this->lote->id_lote,
            'nuevos_datos' => ['estado' => 'inactivo', 'superficie' => 150],
        ]);
    }

    public function test_eliminar_lote()
    {
        Log::info('TEST: Eliminar Lote', ['test' => 'test_eliminar_lote']);

        $loteId = $this->lote->id_lote;
        $this->lote->delete();

        $this->assertSoftDeleted('lotes', ['id_lote' => $loteId]);

        Log::info('✓ ÉXITO: Lote eliminado correctamente', ['lote_id' => $loteId]);
    }

    public function test_listar_lotes()
    {
        Log::info('TEST: Listar Lotes', ['test' => 'test_listar_lotes']);

        Lote::create([
            'propietario' => 'Propietario 2',
            'condicion_compra' => 'comprado',
            'estado' => 'activo',
            'ubicacion' => 'Misiones',
            'especie' => 'Pino',
            'superficie' => 75,
        ]);

        $lotes = Lote::all();

        $this->assertGreaterThanOrEqual(2, $lotes->count());

        Log::info('✓ ÉXITO: Lotes listados', [
            'total_lotes' => $lotes->count(),
        ]);
    }

    // ============================================================================
    // PRUEBAS CRUD - MAQUINARIA
    // ============================================================================

    public function test_crear_maquinaria()
    {
        Log::info('TEST: Crear Maquinaria', ['test' => 'test_crear_maquinaria']);

        $datos = [
            'id_tipo_maquinaria' => $this->tipoMaquinaria->id_tipo_maquinaria,
            'modelo' => 'CAT 325',
            'estado' => 'operativo',
            'es_alquilada' => true,
            'fecha_inicio_actividades' => now(),
            'toneladas_acumuladas' => 50,
            'umbral_toneladas' => 400,
        ];

        $maquinaria = Maquinaria::create($datos);

        $this->assertDatabaseHas('maquinarias', ['modelo' => 'CAT 325']);
        $this->assertTrue($maquinaria->es_alquilada);

        Log::info('✓ ÉXITO: Maquinaria creada', [
            'maquinaria_id' => $maquinaria->id_maquinaria,
            'modelo' => $datos['modelo'],
        ]);
    }

    public function test_asignar_maquinaria_a_lote()
    {
        Log::info('TEST: Asignar Maquinaria a Lote', ['test' => 'test_asignar_maquinaria_a_lote']);

        $this->lote->maquinarias()->attach($this->maquinaria->id_maquinaria);

        $this->assertDatabaseHas('lote_maquinaria', [
            'id_lote' => $this->lote->id_lote,
            'id_maquinaria' => $this->maquinaria->id_maquinaria,
        ]);

        Log::info('✓ ÉXITO: Maquinaria asignada a lote', [
            'lote_id' => $this->lote->id_lote,
            'maquinaria_id' => $this->maquinaria->id_maquinaria,
        ]);
    }

    public function test_desasignar_maquinaria_de_lote()
    {
        Log::info('TEST: Desasignar Maquinaria de Lote', ['test' => 'test_desasignar_maquinaria_de_lote']);

        $this->lote->maquinarias()->attach($this->maquinaria->id_maquinaria);
        $this->lote->maquinarias()->detach($this->maquinaria->id_maquinaria);

        $this->assertDatabaseMissing('lote_maquinaria', [
            'id_lote' => $this->lote->id_lote,
            'id_maquinaria' => $this->maquinaria->id_maquinaria,
        ]);

        Log::info('✓ ÉXITO: Maquinaria desasignada de lote', [
            'lote_id' => $this->lote->id_lote,
            'maquinaria_id' => $this->maquinaria->id_maquinaria,
        ]);
    }

    // ============================================================================
    // PRUEBAS CRUD - EMPLEADOS
    // ============================================================================

    public function test_crear_empleado()
    {
        Log::info('TEST: Crear Empleado', ['test' => 'test_crear_empleado']);

        $datos = [
            'id_rol_laboral' => $this->rolLaboral->id_rol_laboral,
            'dni' => '87654321',
            'apellido' => 'García',
            'nombre' => 'Carlos',
            'fecha_nacimiento' => '1985-06-20',
            'fecha_inicio_actividades' => now()->subMonths(3),
            'fecha_fin_actividades' => null,
        ];

        $empleado = Empleado::create($datos);

        $this->assertDatabaseHas('empleados', ['dni' => '87654321']);
        $this->assertEquals('García', $empleado->apellido);

        Log::info('✓ ÉXITO: Empleado creado', [
            'empleado_id' => $empleado->id_empleado,
            'dni' => $datos['dni'],
        ]);
    }

    public function test_asignar_empleado_a_lote()
    {
        Log::info('TEST: Asignar Empleado a Lote', ['test' => 'test_asignar_empleado_a_lote']);

        $this->lote->empleados()->attach($this->empleado->id_empleado);

        $this->assertDatabaseHas('lote_empleado', [
            'id_lote' => $this->lote->id_lote,
            'id_empleado' => $this->empleado->id_empleado,
        ]);

        Log::info('✓ ÉXITO: Empleado asignado a lote', [
            'lote_id' => $this->lote->id_lote,
            'empleado_id' => $this->empleado->id_empleado,
        ]);
    }

    // ============================================================================
    // PRUEBAS CRUD - PARTES DIARIOS
    // ============================================================================

    public function test_crear_parte_diario()
    {
        Log::info('TEST: Crear Parte Diario', ['test' => 'test_crear_parte_diario']);

        $datos = [
            'id_lote' => $this->lote->id_lote,
            'fecha' => today(),
            'es_dia_caido' => false,
            'costo_insumos' => 500.00,
            'costo_maquinaria' => 1200.00,
            'costo_mano_obra' => 800.00,
            'costo_total_dia' => 2500.00,
        ];

        $parteDiario = ParteDiario::create($datos);

        $this->assertDatabaseHas('parte_diarios', [
            'id_lote' => $this->lote->id_lote,
            'costo_total_dia' => 2500.00,
        ]);

        Log::info('✓ ÉXITO: Parte diario creado', [
            'parte_diario_id' => $parteDiario->id_parte_diario,
            'costo_total' => $datos['costo_total_dia'],
        ]);
    }

    public function test_asignar_empleado_a_parte_diario()
    {
        Log::info('TEST: Asignar Empleado a Parte Diario', ['test' => 'test_asignar_empleado_a_parte_diario']);

        $parteDiario = ParteDiario::create([
            'id_lote' => $this->lote->id_lote,
            'fecha' => today(),
            'es_dia_caido' => false,
            'costo_insumos' => 500,
            'costo_maquinaria' => 1200,
            'costo_mano_obra' => 800,
            'costo_total_dia' => 2500,
        ]);

        $parteDiario->empleados()->attach($this->empleado->id_empleado);

        $this->assertDatabaseHas('parte_diario_empleado', [
            'id_parte_diario' => $parteDiario->id_parte_diario,
            'id_empleado' => $this->empleado->id_empleado,
        ]);

        Log::info('✓ ÉXITO: Empleado asignado a parte diario', [
            'parte_diario_id' => $parteDiario->id_parte_diario,
            'empleado_id' => $this->empleado->id_empleado,
        ]);
    }

    public function test_crear_carga_en_lote()
    {
        Log::info('TEST: Crear Carga en Lote', ['test' => 'test_crear_carga_en_lote']);

        $datos = [
            'id_lote' => $this->lote->id_lote,
            'fecha_carga' => today(),
            'peso_neto' => 8000, // 8 toneladas
            'descripcion' => 'Carga de madera A',
        ];

        $carga = Carga::create($datos);

        $this->assertDatabaseHas('cargas', [
            'id_lote' => $this->lote->id_lote,
            'peso_neto' => 8000,
        ]);

        Log::info('✓ ÉXITO: Carga creada', [
            'carga_id' => $carga->id_carga,
            'peso_neto_tn' => 8,
        ]);
    }

    // ============================================================================
    // PRUEBAS DE MANTENIMIENTO PREVENTIVO
    // ============================================================================

    public function test_crear_tipo_mantenimiento()
    {
        Log::info('TEST: Crear Tipo de Mantenimiento', ['test' => 'test_crear_tipo_mantenimiento']);

        $datos = [
            'nombre' => 'Cambio de Aceite',
            'descripcion' => 'Cambio periódico de aceite',
            'intervalo_toneladas' => 200,
        ];

        $tipoMant = TipoMantenimiento::create($datos);

        $this->assertDatabaseHas('tipo_mantenimientos', ['nombre' => 'Cambio de Aceite']);

        Log::info('✓ ÉXITO: Tipo de mantenimiento creado', [
            'tipo_id' => $tipoMant->id_tipo_mantenimiento,
            'nombre' => $datos['nombre'],
        ]);
    }

    public function test_crear_mantenimiento_preventivo()
    {
        Log::info('TEST: Crear Mantenimiento Preventivo', ['test' => 'test_crear_mantenimiento_preventivo']);

        $tipoMant = TipoMantenimiento::create([
            'nombre' => 'Revisión General',
            'descripcion' => 'Revisión completa',
            'intervalo_toneladas' => 500,
        ]);

        $datos = [
            'id_maquinaria' => $this->maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipoMant->id_tipo_mantenimiento,
            'fecha_inicio' => now(),
            'fecha_programada' => now()->addDays(7),
            'estado' => 'programado',
        ];

        $mantenimiento = Mantenimiento::create($datos);

        $this->assertDatabaseHas('mantenimientos', [
            'id_maquinaria' => $this->maquinaria->id_maquinaria,
            'estado' => 'programado',
        ]);

        Log::info('✓ ÉXITO: Mantenimiento preventivo creado', [
            'mantenimiento_id' => $mantenimiento->id_mantenimiento,
            'estado' => $datos['estado'],
        ]);
    }

    public function test_verificar_stock_para_aprobar_mantenimiento()
    {
        Log::info('TEST: Verificar Stock para Aprobar Mantenimiento', ['test' => 'test_verificar_stock_para_aprobar_mantenimiento']);

        $tipoMant = TipoMantenimiento::create([
            'nombre' => 'Cambio de Filtro',
            'descripcion' => 'Cambio de filtros',
            'intervalo_toneladas' => 250,
        ]);

        $unidadMedida = \App\Models\UnidadMedida::create([
            'nombre' => 'Unidad',
            'abreviatura' => 'u',
        ]);

        $insumo = Insumo::create([
            'nombre' => 'Filtro Aire',
            'descripcion' => 'Filtro de aire para maquinaria',
            'costo_unitario' => 150.00,
            'id_unidad_medida' => $unidadMedida->id_unidad_medida,
        ]);

        // Crear kit de mantenimiento
        KitMantenimientoPreventivo::create([
            'id_tipo_maquinaria' => $this->tipoMaquinaria->id_tipo_maquinaria,
            'id_tipo_mantenimiento' => $tipoMant->id_tipo_mantenimiento,
            'id_insumo' => $insumo->id_insumo,
            'cantidad_requerida' => 2,
            'es_obligatorio' => true,
        ]);

        // Registrar movimiento de entrada
        MovimientoStock::create([
            'id_insumo' => $insumo->id_insumo,
            'tipo' => 'entrada',
            'cantidad' => 5,
            'motivo' => 'Compra inicial',
            'fecha' => now(),
        ]);

        $mantenimiento = Mantenimiento::create([
            'id_maquinaria' => $this->maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipoMant->id_tipo_mantenimiento,
            'fecha_inicio' => now(),
            'fecha_programada' => now()->addDays(7),
            'estado' => 'programado',
        ]);

        $service = new MantenimientoService;
        $resultado = $service->verificarStockParaAprobacion($mantenimiento->id_mantenimiento);

        $this->assertTrue($resultado['puede_aprobar']);
        $this->assertEmpty($resultado['insuficientes']);

        Log::info('✓ ÉXITO: Stock verificado correctamente', [
            'mantenimiento_id' => $mantenimiento->id_mantenimiento,
            'puede_aprobar' => true,
        ]);
    }

    public function test_completar_mantenimiento()
    {
        Log::info('TEST: Completar Mantenimiento', ['test' => 'test_completar_mantenimiento']);

        $tipoMant = TipoMantenimiento::create([
            'nombre' => 'Cambio de Aceite',
            'descripcion' => 'Cambio de aceite motor',
            'intervalo_toneladas' => 300,
        ]);

        $unidadMedida = \App\Models\UnidadMedida::create([
            'nombre' => 'Litro',
            'abreviatura' => 'l',
        ]);

        $insumo = Insumo::create([
            'nombre' => 'Aceite Premium',
            'descripcion' => 'Aceite de motor premium',
            'costo_unitario' => 500.00,
            'id_unidad_medida' => $unidadMedida->id_unidad_medida,
        ]);

        // Registrar stock
        MovimientoStock::create([
            'id_insumo' => $insumo->id_insumo,
            'tipo' => 'entrada',
            'cantidad' => 10,
            'motivo' => 'Compra',
            'fecha' => now(),
        ]);

        $mantenimiento = Mantenimiento::create([
            'id_maquinaria' => $this->maquinaria->id_maquinaria,
            'id_tipo_mantenimiento' => $tipoMant->id_tipo_mantenimiento,
            'fecha_inicio' => now(),
            'fecha_programada' => now()->addDays(3),
            'estado' => 'aprobado',
        ]);

        $service = new MantenimientoService;
        $resultado = $service->completarMantenimiento(
            $mantenimiento->id_mantenimiento,
            [
                [
                    'id_insumo' => $insumo->id_insumo,
                    'cantidad_utilizada' => 2,
                    'costo_unitario' => 500,
                ],
            ],
            1000 // costo de mano de obra
        );

        $this->assertTrue($resultado['success']);
        $this->assertEquals(2000, $resultado['costo_total']); // 2*500 + 1000

        $this->assertDatabaseHas('mantenimientos', [
            'id_mantenimiento' => $mantenimiento->id_mantenimiento,
            'estado' => 'completado',
        ]);

        Log::info('✓ ÉXITO: Mantenimiento completado', [
            'mantenimiento_id' => $mantenimiento->id_mantenimiento,
            'costo_total' => $resultado['costo_total'],
        ]);
    }

    // ============================================================================
    // PRUEBAS DE NOTIFICACIONES
    // ============================================================================

    public function test_crear_notificacion_sistema()
    {
        Log::info('TEST: Crear Notificación del Sistema', ['test' => 'test_crear_notificacion_sistema']);

        $datos = [
            'user_id' => $this->usuario->id,
            'titulo' => 'Mantenimiento Requerido',
            'mensaje' => 'Se requiere mantenimiento preventivo en maquinaria CAT 320',
            'tipo' => 'mantenimiento_vencido',
            'referencia_id' => $this->maquinaria->id_maquinaria,
            'leida' => false,
        ];

        $notificacion = NotificacionSistema::create($datos);

        $this->assertDatabaseHas('notificaciones_sistema', [
            'user_id' => $this->usuario->id,
            'tipo' => 'mantenimiento_vencido',
        ]);

        Log::info('✓ ÉXITO: Notificación creada', [
            'notificacion_id' => $notificacion->id_notificacion,
            'tipo' => $datos['tipo'],
        ]);
    }

    public function test_marcar_notificacion_como_leida()
    {
        Log::info('TEST: Marcar Notificación como Leída', ['test' => 'test_marcar_notificacion_como_leida']);

        $notificacion = NotificacionSistema::create([
            'user_id' => $this->usuario->id,
            'titulo' => 'Prueba',
            'mensaje' => 'Notificación de prueba',
            'tipo' => 'umbral_alcanzado',
            'leida' => false,
        ]);

        $notificacion->update(['leida' => true]);

        $this->assertTrue($notificacion->fresh()->leida);

        Log::info('✓ ÉXITO: Notificación marcada como leída', [
            'notificacion_id' => $notificacion->id_notificacion,
        ]);
    }

    public function test_listar_notificaciones_no_leidas()
    {
        Log::info('TEST: Listar Notificaciones No Leídas', ['test' => 'test_listar_notificaciones_no_leidas']);

        NotificacionSistema::create([
            'user_id' => $this->usuario->id,
            'titulo' => 'Notificación 1',
            'mensaje' => 'Primera notificación',
            'tipo' => 'umbral_alcanzado',
            'leida' => false,
        ]);

        NotificacionSistema::create([
            'user_id' => $this->usuario->id,
            'titulo' => 'Notificación 2',
            'mensaje' => 'Segunda notificación',
            'tipo' => 'umbral_alcanzado',
            'leida' => true,
        ]);

        $noLeidas = NotificacionSistema::where('user_id', $this->usuario->id)
            ->where('leida', false)
            ->get();

        $this->assertEquals(1, $noLeidas->count());

        Log::info('✓ ÉXITO: Notificaciones no leídas listadas', [
            'total' => $noLeidas->count(),
        ]);
    }

    // ============================================================================
    // PRUEBAS DE LIQUIDACIÓN DE PERSONAL
    // ============================================================================

    public function test_calcular_pago_empleado_por_rango()
    {
        Log::info('TEST: Calcular Pago de Empleado por Rango', ['test' => 'test_calcular_pago_empleado_por_rango']);

        // Crear parte diario con día caído
        ParteDiario::create([
            'id_lote' => $this->lote->id_lote,
            'fecha' => now()->subDays(5),
            'es_dia_caido' => true,
            'costo_insumos' => 0,
            'costo_maquinaria' => 0,
            'costo_mano_obra' => 100,
            'costo_total_dia' => 100,
        ])->empleados()->attach($this->empleado->id_empleado);

        // Crear carga y asignar empleado
        $carga = Carga::create([
            'id_lote' => $this->lote->id_lote,
            'fecha_carga' => now()->subDays(4),
            'peso_neto' => 10000, // 10 toneladas
            'descripcion' => 'Carga de prueba',
        ]);

        $parteDiario = ParteDiario::create([
            'id_lote' => $this->lote->id_lote,
            'fecha' => now()->subDays(4),
            'es_dia_caido' => false,
            'costo_insumos' => 500,
            'costo_maquinaria' => 1000,
            'costo_mano_obra' => 500,
            'costo_total_dia' => 2000,
        ]);

        $carga->empleados()->attach($this->empleado->id_empleado);
        $parteDiario->empleados()->attach($this->empleado->id_empleado);

        $pago = EmpleadoPagoService::calcularPagoRango(
            $this->empleado,
            now()->subDays(10)->toDateString(),
            now()->toDateString()
        );

        $this->assertGreaterThan(0, $pago['cantidad_dias_caidos']);
        $this->assertGreaterThan(0, $pago['total_pagar_final']);

        Log::info('✓ ÉXITO: Pago calculado correctamente', [
            'empleado_id' => $this->empleado->id_empleado,
            'dias_caidos' => $pago['cantidad_dias_caidos'],
            'total_pagar' => $pago['total_pagar_final'],
        ]);
    }

    // ============================================================================
    // PRUEBAS DE CLIMA Y ESTADÍSTICAS
    // ============================================================================

    public function test_clima_decision_service_sin_coordenadas()
    {
        Log::info('TEST: ClimaDecisionService sin Coordenadas', ['test' => 'test_clima_decision_service_sin_coordenadas']);

        $loteSinCoord = Lote::create([
            'propietario' => 'Propietario sin coord',
            'condicion_compra' => 'comprado',
            'estado' => 'activo',
            'ubicacion' => 'Desconocida',
            'especie' => 'Pino',
            'superficie' => 50,
            'latitud' => null,
            'longitud' => null,
        ]);

        $service = app(ClimaDecisionService::class);
        $resultado = $service->analizarYRecomendar($loteSinCoord);

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('coordenadas', strtolower($resultado['error']));

        Log::info('✓ ÉXITO: Error capturado correctamente sin coordenadas', [
            'lote_id' => $loteSinCoord->id_lote,
            'error' => $resultado['error'],
        ]);
    }

    public function test_forestal_stats_service()
    {
        Log::info('TEST: ForestalStatsService - Precio Promedio Venta', ['test' => 'test_forestal_stats_service']);

        // Crear cargas con ventas
        $carga1 = Carga::create([
            'id_lote' => $this->lote->id_lote,
            'fecha_carga' => today(),
            'peso_neto' => 5000, // 5 toneladas
            'descripcion' => 'Carga 1',
        ]);

        $service = new ForestalStatsService;
        $precioPromedio = $service->getPrecioPromedioVenta($this->lote);

        // Sin ventas registradas, debería ser 0
        $this->assertEquals(0.0, $precioPromedio);

        Log::info('✓ ÉXITO: Estadísticas forestales calculadas', [
            'lote_id' => $this->lote->id_lote,
            'precio_promedio' => $precioPromedio,
        ]);
    }

    public function test_costo_promedio_por_tonelada()
    {
        Log::info('TEST: Costo Promedio por Tonelada', ['test' => 'test_costo_promedio_por_tonelada']);

        ParteDiario::create([
            'id_lote' => $this->lote->id_lote,
            'fecha' => today(),
            'es_dia_caido' => false,
            'costo_insumos' => 500,
            'costo_maquinaria' => 1000,
            'costo_mano_obra' => 300,
            'costo_total_dia' => 1800,
        ]);

        Carga::create([
            'id_lote' => $this->lote->id_lote,
            'fecha_carga' => today(),
            'peso_neto' => 3000, // 3 toneladas
            'descripcion' => 'Carga de prueba',
        ]);

        $service = new ForestalStatsService;
        $costoProm = $service->getCostoPromedioPorTn($this->lote);

        $this->assertGreaterThan(0, $costoProm);

        Log::info('✓ ÉXITO: Costo promedio por tonelada calculado', [
            'lote_id' => $this->lote->id_lote,
            'costo_promedio_tn' => $costoProm,
        ]);
    }

    // ============================================================================
    // RESUMEN FINAL
    // ============================================================================

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        Log::info('═══ FIN DE PRUEBAS DE CAJA BLANCA ═══');
    }
}
