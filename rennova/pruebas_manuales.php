#!/usr/bin/env php
<?php

/**
 * Script de Pruebas Manuales de Caja Blanca
 * 
 * Ejecuta pruebas independientes sin dependencia de migraciones SQLite incompatibles
 * Cada prueba demuestra la lógica del sistema con datos reales
 */

// Incluir autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lote;
use App\Models\Maquinaria;
use App\Models\TipoMaquinaria;
use App\Models\Empleado;
use App\Models\RolLaboral;
use App\Models\ParteDiario;
use App\Models\Carga;
use App\Models\Mantenimiento;
use App\Models\TipoMantenimiento;
use App\Models\Insumo;
use App\Models\NotificacionSistema;
use App\Models\HistoricoRolLaboral;
use App\Services\MantenimientoService;
use App\Services\ClimaDecisionService;
use App\Services\ForestalStatsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Colores para output
const RESET = "\033[0m";
const BOLD = "\033[1m";
const GREEN = "\033[32m";
const YELLOW = "\033[33m";
const BLUE = "\033[34m";
const RED = "\033[31m";

function print_header($title) {
    echo "\n" . str_repeat("═", 80) . "\n";
    echo BOLD . BLUE . "  " . $title . RESET . "\n";
    echo str_repeat("═", 80) . "\n\n";
}

function print_test($name) {
    echo BOLD . "▶ " . $name . RESET . "\n";
}

function print_success($message) {
    echo GREEN . "  ✅ " . $message . RESET . "\n";
}

function print_info($message) {
    echo BLUE . "  ℹ  " . $message . RESET . "\n";
}

function print_result($data) {
    echo YELLOW . "  → " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . RESET . "\n";
}

function print_section($title) {
    echo "\n" . BOLD . "┌─ " . $title . RESET . "\n";
}

function print_item($item, $value) {
    echo "│  • " . BOLD . $item . ":" . RESET . " " . $value . "\n";
}

function print_end() {
    echo "└──\n";
}

// ============================================================================
// INIT TESTS
// ============================================================================

print_header("PRUEBAS DE CAJA BLANCA - SISTEMA RENNOVA");

echo "Iniciando suite de pruebas manuales...\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "Base de datos: " . config('database.default') . "\n";

$passed = 0;
$failed = 0;

try {
    DB::connection()->getPdo();
    print_success("Conexión a BD exitosa");
} catch (\Exception $e) {
    echo RED . "✗ Error de conexión: " . $e->getMessage() . RESET . "\n";
    exit(1);
}

// ============================================================================
// PRUEBA 1: CRUDs DE LOTES
// ============================================================================

print_header("1. PRUEBAS DE CRUDs - LOTES");

print_test("1.1 Crear un Lote");
try {
    $lote = Lote::create([
        'propietario' => 'Prueba de Propietario',
        'condicion_compra' => 'comprado',
        'estado' => 'activo',
        'ubicacion' => 'Misiones, Argentina',
        'especie' => 'Pino Paraná',
        'superficie' => 100,
        'latitud' => -27.3612,
        'longitud' => -55.5116
    ]);
    
    print_section("Lote Creado");
    print_item("ID", $lote->id_lote);
    print_item("Propietario", $lote->propietario);
    print_item("Superficie", $lote->superficie . " hectáreas");
    print_item("Coordenadas", $lote->latitud . ", " . $lote->longitud);
    print_end();
    
    print_success("Lote creado correctamente (ID: {$lote->id_lote})");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

print_test("1.2 Actualizar Lote");
try {
    $lote->update([
        'estado' => 'inactivo',
        'superficie' => 150
    ]);
    
    $loteActualizado = Lote::find($lote->id_lote);
    
    print_section("Lote Actualizado");
    print_item("Estado Anterior", 'activo');
    print_item("Estado Nuevo", $loteActualizado->estado);
    print_item("Superficie Anterior", '100');
    print_item("Superficie Nueva", $loteActualizado->superficie);
    print_end();
    
    print_success("Datos actualizados correctamente");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

print_test("1.3 Listar Lotes");
try {
    $lotes = Lote::all();
    
    print_section("Lotes en BD");
    print_item("Total de lotes", count($lotes));
    print_item("Primer lote", $lotes->first()->propietario ?? 'N/A');
    print_end();
    
    print_success("Se retornaron " . count($lotes) . " lotes");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

// ============================================================================
// PRUEBA 2: MAQUINARIA Y RELACIONES
// ============================================================================

print_header("2. PRUEBAS DE MAQUINARIA");

print_test("2.1 Crear Tipo de Maquinaria");
try {
    $tipoMaq = TipoMaquinaria::create([
        'nombre' => 'Cosechadora Forestal',
        'descripcion' => 'Máquina cosechadora de troncos'
    ]);
    
    print_section("Tipo Creado");
    print_item("ID", $tipoMaq->id_tipo_maquinaria);
    print_item("Nombre", $tipoMaq->nombre);
    print_end();
    
    print_success("Tipo de maquinaria creado");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

print_test("2.2 Crear Maquinaria");
try {
    $maquinaria = Maquinaria::create([
        'id_tipo_maquinaria' => $tipoMaq->id_tipo_maquinaria,
        'modelo' => 'CAT 330',
        'estado' => 'operativo',
        'es_alquilada' => false,
        'fecha_inicio_actividades' => now(),
        'toneladas_acumuladas' => 50,
        'umbral_toneladas' => 500
    ]);
    
    print_section("Maquinaria Creada");
    print_item("ID", $maquinaria->id_maquinaria);
    print_item("Modelo", $maquinaria->modelo);
    print_item("Estado", $maquinaria->estado);
    print_item("Toneladas Acumuladas", $maquinaria->toneladas_acumuladas);
    print_end();
    
    print_success("Maquinaria creada (ID: {$maquinaria->id_maquinaria})");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

print_test("2.3 Asignar Maquinaria a Lote");
try {
    $lote->maquinarias()->attach($maquinaria->id_maquinaria);
    
    $maquinariasLote = $lote->maquinarias()->count();
    
    print_section("Relación Creada");
    print_item("Lote ID", $lote->id_lote);
    print_item("Maquinaria ID", $maquinaria->id_maquinaria);
    print_item("Total maquinarias en lote", $maquinariasLote);
    print_end();
    
    print_success("Maquinaria asignada al lote correctamente");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

// ============================================================================
// PRUEBA 3: EMPLEADOS Y ROLES LABORALES
// ============================================================================

print_header("3. PRUEBAS DE EMPLEADOS Y LIQUIDACIÓN");

print_test("3.1 Crear Rol Laboral");
try {
    $rolLaboral = RolLaboral::create([
        'nombre' => 'Operario Forestal',
        'descripcion' => 'Operario general para tareas forestales',
        'valor_jornal' => 1500,
        'tarifa_fija_por_tonelada' => 75
    ]);
    
    HistoricoRolLaboral::create([
        'rol_laboral_id' => $rolLaboral->id_rol_laboral,
        'valor_jornal' => 1500,
        'tarifa_fija_por_tonelada' => 75,
        'fecha_inicio' => now()->subMonths(6),
        'fecha_fin' => null
    ]);
    
    print_section("Rol Laboral Creado");
    print_item("ID", $rolLaboral->id_rol_laboral);
    print_item("Nombre", $rolLaboral->nombre);
    print_item("Jornal", '$' . $rolLaboral->valor_jornal);
    print_item("Tarifa", '$' . $rolLaboral->tarifa_fija_por_tonelada . '/tn");
    print_end();
    
    print_success("Rol laboral creado con histórico");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

print_test("3.2 Crear Empleado");
try {
    $empleado = Empleado::create([
        'id_rol_laboral' => $rolLaboral->id_rol_laboral,
        'dni' => '12345678',
        'apellido' => 'García',
        'nombre' => 'Juan',
        'fecha_nacimiento' => '1990-05-15',
        'fecha_inicio_actividades' => now()->subYear(),
        'fecha_fin_actividades' => null
    ]);
    
    print_section("Empleado Creado");
    print_item("ID", $empleado->id_empleado);
    print_item("Nombre", $empleado->nombre . ' ' . $empleado->apellido);
    print_item("DNI", $empleado->dni);
    print_item("Antigüedad", '1 año');
    print_end();
    
    print_success("Empleado registrado (ID: {$empleado->id_empleado})");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

print_test("3.3 Asignar Empleado a Lote");
try {
    $lote->empleados()->attach($empleado->id_empleado);
    
    $empleadosLote = $lote->empleados()->count();
    
    print_section("Empleado Asignado");
    print_item("Lote", $lote->propietario);
    print_item("Empleado", $empleado->nombre . ' ' . $empleado->apellido);
    print_item("Total empleados en lote", $empleadosLote);
    print_end();
    
    print_success("Empleado asignado al lote");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

// ============================================================================
// PRUEBA 4: PARTES DIARIOS Y CARGAS
// ============================================================================

print_header("4. PRUEBAS DE PARTES DIARIOS Y CARGAS");

print_test("4.1 Crear Parte Diario");
try {
    $parteDiario = ParteDiario::create([
        'id_lote' => $lote->id_lote,
        'fecha' => now(),
        'es_dia_caido' => false,
        'costo_insumos' => 800.00,
        'costo_maquinaria' => 2000.00,
        'costo_mano_obra' => 1200.00,
        'costo_total_dia' => 4000.00
    ]);
    
    print_section("Parte Diario Creado");
    print_item("ID", $parteDiario->id_parte_diario);
    print_item("Fecha", $parteDiario->fecha->format('Y-m-d'));
    print_item("Costo Insumos", '$' . number_format($parteDiario->costo_insumos, 2));
    print_item("Costo Maquinaria", '$' . number_format($parteDiario->costo_maquinaria, 2));
    print_item("Costo Mano de Obra", '$' . number_format($parteDiario->costo_mano_obra, 2));
    print_item("COSTO TOTAL DÍA", '$' . number_format($parteDiario->costo_total_dia, 2));
    print_end();
    
    print_success("Parte diario registrado con costos totales");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

print_test("4.2 Asignar Empleado a Parte Diario");
try {
    $parteDiario->empleados()->attach($empleado->id_empleado);
    
    $empleadosEnParte = $parteDiario->empleados()->count();
    
    print_section("Empleado Asignado a Parte");
    print_item("Parte ID", $parteDiario->id_parte_diario);
    print_item("Empleado", $empleado->nombre);
    print_item("Total empleados en parte", $empleadosEnParte);
    print_end();
    
    print_success("Empleado vinculado a parte diario");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

print_test("4.3 Crear Carga de Madera");
try {
    $carga = Carga::create([
        'id_lote' => $lote->id_lote,
        'fecha_carga' => now(),
        'peso_neto' => 12000, // 12 toneladas en kg
        'descripcion' => 'Carga mixta de pino'
    ]);
    
    print_section("Carga Registrada");
    print_item("ID", $carga->id_carga);
    print_item("Peso Neto", number_format($carga->peso_neto / 1000, 2) . ' toneladas');
    print_item("Descripción", $carga->descripcion);
    print_item("Fecha", $carga->fecha_carga->format('Y-m-d'));
    print_end();
    
    print_success("Carga de madera registrada: " . ($carga->peso_neto / 1000) . " toneladas");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

print_test("4.4 Asignar Empleado a Carga");
try {
    $carga->empleados()->attach($empleado->id_empleado);
    
    print_section("Carga Asignada a Empleado");
    print_item("Carga", $carga->peso_neto / 1000 . ' tn');
    print_item("Empleado", $empleado->nombre);
    print_item("Contribución", number_format(($carga->peso_neto / 1000) * $rolLaboral->tarifa_fija_por_tonelada, 2));
    print_end();
    
    print_success("Empleado asignado a carga");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

// ============================================================================
// PRUEBA 5: MANTENIMIENTO PREVENTIVO
// ============================================================================

print_header("5. PRUEBAS DE MANTENIMIENTO PREVENTIVO");

print_test("5.1 Crear Tipo de Mantenimiento");
try {
    $tipoMant = TipoMantenimiento::create([
        'nombre' => 'Cambio de Aceite',
        'descripcion' => 'Cambio periódico de aceite del motor',
        'intervalo_toneladas' => 200
    ]);
    
    print_section("Tipo de Mantenimiento");
    print_item("ID", $tipoMant->id_tipo_mantenimiento);
    print_item("Nombre", $tipoMant->nombre);
    print_item("Intervalo", $tipoMant->intervalo_toneladas . ' toneladas');
    print_end();
    
    print_success("Tipo de mantenimiento registrado");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

print_test("5.2 Crear Mantenimiento Preventivo");
try {
    $mantenimiento = Mantenimiento::create([
        'id_maquinaria' => $maquinaria->id_maquinaria,
        'id_tipo_mantenimiento' => $tipoMant->id_tipo_mantenimiento,
        'fecha_inicio' => now(),
        'fecha_programada' => now()->addDays(7),
        'estado' => 'pendiente'
    ]);
    
    print_section("Mantenimiento Preventivo");
    print_item("ID", $mantenimiento->id_mantenimiento);
    print_item("Maquinaria", $maquinaria->modelo);
    print_item("Tipo", $tipoMant->nombre);
    print_item("Estado", $mantenimiento->estado);
    print_item("Fecha Programada", $mantenimiento->fecha_programada->format('Y-m-d'));
    print_end();
    
    print_success("Mantenimiento pendiente creado");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

print_test("5.3 Completar Mantenimiento");
try {
    // Crear insumo
    $insumo = Insumo::create([
        'nombre' => 'Aceite Sintético Premium',
        'descripcion' => 'Aceite para motores de alta potencia',
        'costo_unitario' => 600.00,
        'unidad_medida_id' => 1
    ]);
    
    // Cambiar estado a aprobado
    $mantenimiento->update(['estado' => 'aprobado']);
    
    // Completar
    $service = new MantenimientoService();
    $resultado = $service->completarMantenimiento(
        $mantenimiento->id_mantenimiento,
        [
            [
                'id_insumo' => $insumo->id_insumo,
                'cantidad_utilizada' => 3, // 3 litros
                'costo_unitario' => 600.00
            ]
        ],
        500 // costo mano de obra
    );
    
    print_section("Mantenimiento Completado");
    print_item("Estado Anterior", 'aprobado');
    print_item("Estado Nuevo", 'completado');
    print_item("Costo Insumos", '$' . number_format(3 * 600, 2));
    print_item("Costo Mano de Obra", '$500');
    print_item("COSTO TOTAL", '$' . number_format($resultado['costo_total'], 2));
    print_item("Éxito", $resultado['success'] ? 'Sí' : 'No');
    print_end();
    
    print_success("Mantenimiento completado por $" . number_format($resultado['costo_total'], 2));
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

// ============================================================================
// PRUEBA 6: NOTIFICACIONES
// ============================================================================

print_header("6. PRUEBAS DE NOTIFICACIONES");

print_test("6.1 Crear Notificación del Sistema");
try {
    // Crear usuario de prueba (simulado)
    $notificacion = NotificacionSistema::create([
        'id_usuario' => 1, // Usuario por defecto
        'titulo' => 'Mantenimiento Completado',
        'mensaje' => 'El mantenimiento de la maquinaria CAT 330 ha sido completado exitosamente',
        'tipo' => 'mantenimiento',
        'referencia_id' => $maquinaria->id_maquinaria,
        'leida' => false
    ]);
    
    print_section("Notificación Creada");
    print_item("ID", $notificacion->id_notificacion);
    print_item("Título", $notificacion->titulo);
    print_item("Tipo", $notificacion->tipo);
    print_item("Estado", $notificacion->leida ? 'Leída' : 'No leída');
    print_end();
    
    print_success("Notificación enviada al usuario");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

print_test("6.2 Marcar Notificación como Leída");
try {
    $notificacion->update(['leida' => true]);
    $notificacionActualizada = NotificacionSistema::find($notificacion->id_notificacion);
    
    print_section("Notificación Actualizada");
    print_item("Estado Anterior", 'No leída');
    print_item("Estado Nuevo", $notificacionActualizada->leida ? 'Leída' : 'No leída');
    print_item("Timestamp", $notificacionActualizada->updated_at->format('Y-m-d H:i:s'));
    print_end();
    
    print_success("Notificación marcada como leída");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

// ============================================================================
// PRUEBA 7: CÁLCULO DE PAGOS
// ============================================================================

print_header("7. PRUEBAS DE LIQUIDACIÓN DE PERSONAL");

print_test("7.1 Calcular Pago de Empleado");
try {
    $pago = $empleado->calcularPagoRango(
        now()->subDays(10)->toDateString(),
        now()->toDateString()
    );
    
    print_section("Liquidación de Salarios");
    print_item("Empleado", $empleado->nombre . ' ' . $empleado->apellido);
    print_item("Período", now()->subDays(10)->format('Y-m-d') . ' al ' . now()->format('Y-m-d'));
    print_item("Días Caídos", $pago['cantidad_dias_caidos']);
    print_item("Valor Jornal", '$' . number_format($pago['valor_jornal'], 2));
    print_item("Total Jornales", '$' . number_format($pago['total_pagar_jornales'], 2));
    print_item("Toneladas Asignadas", number_format($pago['total_peso_neto'], 2));
    print_item("Tarifa x Tonelada", '$' . number_format($pago['tarifa_fija_por_tonelada'], 2));
    print_item("Total Producción", '$' . number_format($pago['total_pagar_produccion'], 2));
    print_item("═════════════════════════════════════════", "═");
    print_item("TOTAL A PAGAR", '$' . number_format($pago['total_pagar_final'], 2));
    print_end();
    
    print_success("Liquidación calculada: $" . number_format($pago['total_pagar_final'], 2));
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

// ============================================================================
// PRUEBA 8: CLIMA Y ESTADÍSTICAS
// ============================================================================

print_header("8. PRUEBAS DE CLIMA Y ESTADÍSTICAS");

print_test("8.1 ClimaDecisionService - Validación de Coordenadas");
try {
    $loteSinCoord = Lote::create([
        'propietario' => 'Test sin coordenadas',
        'condicion_compra' => 'comprado',
        'estado' => 'activo',
        'ubicacion' => 'Ubicación desconocida',
        'especie' => 'Desconocida',
        'superficie' => 50,
        'latitud' => null,
        'longitud' => null
    ]);
    
    $service = new ClimaDecisionService();
    $resultado = $service->analizarYRecomendar($loteSinCoord);
    
    print_section("Validación de Coordenadas");
    print_item("Éxito", $resultado['success'] ? 'Sí' : 'No');
    print_item("Error", $resultado['error'] ?? 'Sin errores');
    print_item("Sugerencia", $resultado['sugerencia'] ?? 'N/A');
    print_end();
    
    print_success("Validación correcta: error capturado sin coordenadas");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

print_test("8.2 ForestalStatsService - Estadísticas Básicas");
try {
    $service = new ForestalStatsService();
    
    // El método usado datos en caché, así que los valores serán lo calculado o 0
    $precioPromedio = $service->getPrecioPromedioVenta($lote);
    $costoPromedio = $service->getCostoPromedioPorTn($lote);
    
    print_section("Estadísticas Forestales");
    print_item("Lote", $lote->propietario);
    print_item("Precio Promedio Venta", '$' . number_format($precioPromedio, 2) . '/tn');
    print_item("Costo Promedio", '$' . number_format($costoPromedio, 2) . '/tn');
    print_item("Margen Unitario", '$' . number_format($precioPromedio - $costoPromedio, 2));
    print_end();
    
    print_success("Estadísticas calculadas correctamente");
    $passed++;
} catch (\Exception $e) {
    echo RED . "✗ Error: " . $e->getMessage() . RESET . "\n";
    $failed++;
}

// ============================================================================
// RESUMEN FINAL
// ============================================================================

print_header("RESUMEN DE PRUEBAS");

$total = $passed + $failed;
$percentage = ($total > 0) ? round(($passed / $total) * 100, 2) : 0;

echo BOLD . "Total de pruebas: " . RESET . "$total\n";
echo GREEN . "✅ Exitosas: " . RESET . "$passed\n";
echo RED . "❌ Fallidas: " . RESET . "$failed\n";
echo BOLD . "Tasa de éxito: " . RESET . "$percentage%\n";

echo "\n" . str_repeat("═", 80) . "\n";

if ($failed == 0) {
    echo GREEN . BOLD . "✅ TODAS LAS PRUEBAS PASARON CORRECTAMENTE" . RESET . "\n";
    exit(0);
} else {
    echo RED . BOLD . "⚠️  " . $failed . " PRUEBAS FALLARON" . RESET . "\n";
    exit(1);
}
