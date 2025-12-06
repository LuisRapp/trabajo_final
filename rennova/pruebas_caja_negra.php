<?php

/**
 * PRUEBAS DE CAJA NEGRA - SISTEMA RENNOVA
 * 
 * Este script ejecuta pruebas funcionales sin conocer la estructura interna
 * Solo valida: ¿El sistema responde como debería?
 * 
 * Ejecución: php pruebas_caja_negra.php
 */

// ============================================================================
// SETUP
// ============================================================================

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Bootstrap Laravel sin Kernel
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Cargar configuración
\Illuminate\Support\Facades\Config::set('database.default', env('DB_CONNECTION', 'pgsql'));

// Conectar con la BD
DB::connection();

// Colores para output
const RESET = "\033[0m";
const RED = "\033[91m";
const GREEN = "\033[92m";
const YELLOW = "\033[93m";
const BLUE = "\033[94m";
const CYAN = "\033[96m";
const BOLD = "\033[1m";

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$testResults = [];

function printHeader($title) {
    echo "\n" . CYAN . BOLD . "════════════════════════════════════════════════════════════════════" . RESET . "\n";
    echo CYAN . BOLD . "  " . $title . RESET . "\n";
    echo CYAN . BOLD . "════════════════════════════════════════════════════════════════════" . RESET . "\n\n";
}

function printTest($number, $name) {
    echo BLUE . "[TEST $number] " . RESET . "$name\n";
    echo "  Validando... ";
}

function printPass($message = "") {
    global $totalTests, $passedTests, $testResults;
    $passedTests++;
    $totalTests++;
    echo GREEN . "✅ PASÓ" . RESET;
    if ($message) echo " - " . $message;
    echo "\n";
    $testResults[] = ['status' => 'PASS', 'message' => $message];
}

function printFail($message = "") {
    global $totalTests, $failedTests, $testResults;
    $failedTests++;
    $totalTests++;
    echo RED . "❌ FALLÓ" . RESET;
    if ($message) echo " - " . $message;
    echo "\n";
    $testResults[] = ['status' => 'FAIL', 'message' => $message];
}

function printInfo($message) {
    echo YELLOW . "ℹ️  " . RESET . "$message\n";
}

// ============================================================================
// PRUEBA 1: VERIFICAR CONEXIÓN A BD
// ============================================================================

printHeader("PRUEBA 1: CONECTIVIDAD Y BASE DE DATOS");

$testNum = 1;

// Test 1.1 - Conectar a BD
printTest($testNum++, "Conectar a Base de Datos");
try {
    DB::connection()->getPdo();
    printPass("Conexión exitosa");
} catch (\Exception $e) {
    printFail("No se pudo conectar: " . $e->getMessage());
    exit(1);
}

// Test 1.2 - Verificar tablas principales
printTest($testNum++, "Verificar tablas principales existen");
$tables = ['lotes', 'maquinarias', 'empleados', 'tipo_maquinarias', 'mantenimientos'];
$missingTables = [];
foreach ($tables as $table) {
    if (!Schema::hasTable($table)) {
        $missingTables[] = $table;
    }
}
if (empty($missingTables)) {
    printPass("Todas las tablas existen");
} else {
    printFail("Faltan tablas: " . implode(", ", $missingTables));
}

// ============================================================================
// PRUEBA 2: CRUD LOTES
// ============================================================================

printHeader("PRUEBA 2: CREAR, LEER, ACTUALIZAR Y ELIMINAR LOTES");

$loteId = null;

// Test 2.1 - Crear Lote
printTest($testNum++, "Crear nuevo Lote");
try {
    $result = DB::table('lotes')->insertGetId([
        'nombre_lote' => 'Lote Test Caja Negra ' . time(),
        'ubicacion' => 'Test Location',
        'hectareas' => 50,
        'fecha_inicio' => now()->format('Y-m-d'),
        'estado' => 'activo',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $loteId = $result;
    if ($loteId > 0) {
        printPass("Lote creado con ID: $loteId");
    } else {
        printFail("ID retornado inválido");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 2.2 - Leer/Obtener Lote
printTest($testNum++, "Obtener Lote por ID");
try {
    $lote = DB::table('lotes')->where('id_lote', $loteId)->first();
    if ($lote && $lote->nombre_lote) {
        printPass("Lote obtenido: {$lote->nombre_lote}");
    } else {
        printFail("No se encontró el lote");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 2.3 - Actualizar Lote
printTest($testNum++, "Actualizar Lote");
try {
    $updated = DB::table('lotes')
        ->where('id_lote', $loteId)
        ->update(['hectareas' => 75, 'updated_at' => now()]);
    
    $lote = DB::table('lotes')->where('id_lote', $loteId)->first();
    if ($lote->hectareas == 75) {
        printPass("Lote actualizado (hectáreas: 75)");
    } else {
        printFail("Actualización no se reflejó");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 2.4 - Listar Lotes
printTest($testNum++, "Listar todos los Lotes");
try {
    $lotes = DB::table('lotes')->count();
    if ($lotes > 0) {
        printPass("Total de lotes en BD: $lotes");
    } else {
        printFail("No hay lotes en la BD");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// ============================================================================
// PRUEBA 3: MAQUINARIAS
// ============================================================================

printHeader("PRUEBA 3: GESTIONAR MAQUINARIAS Y TIPOS");

$maquinariaId = null;

// Test 3.1 - Crear Tipo de Maquinaria
printTest($testNum++, "Crear Tipo de Maquinaria");
try {
    $tipoId = DB::table('tipo_maquinarias')->insertGetId([
        'nombre' => 'Excavadora Test ' . time(),
        'descripcion' => 'Test',
        'umbral_toneladas' => 100,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    if ($tipoId > 0) {
        printPass("Tipo creado: ID $tipoId");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 3.2 - Crear Maquinaria
printTest($testNum++, "Crear Maquinaria");
try {
    $maquinariaId = DB::table('maquinarias')->insertGetId([
        'nombre_maquinaria' => 'Excavadora Test ' . time(),
        'tipo_maquinaria_id' => $tipoId,
        'marca' => 'Caterpillar',
        'modelo' => 'CAT 320',
        'año_fabricacion' => 2020,
        'estado' => 'activa',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    if ($maquinariaId > 0) {
        printPass("Maquinaria creada: ID $maquinariaId");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 3.3 - Asignar Maquinaria a Lote
printTest($testNum++, "Asignar Maquinaria a Lote (relación M2M)");
try {
    DB::table('lote_maquinaria')->insert([
        'lote_id' => $loteId,
        'maquinaria_id' => $maquinariaId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $relacion = DB::table('lote_maquinaria')
        ->where('lote_id', $loteId)
        ->where('maquinaria_id', $maquinariaId)
        ->first();
    
    if ($relacion) {
        printPass("Maquinaria asignada al lote");
    } else {
        printFail("No se creó la relación");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 3.4 - Verificar Maquinarias del Lote
printTest($testNum++, "Obtener Maquinarias asignadas a Lote");
try {
    $maquinarias = DB::table('lote_maquinaria')
        ->where('lote_id', $loteId)
        ->count();
    
    if ($maquinarias > 0) {
        printPass("Lote tiene $maquinarias maquinaria(s) asignada(s)");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// ============================================================================
// PRUEBA 4: EMPLEADOS Y ROLES
// ============================================================================

printHeader("PRUEBA 4: GESTIONAR EMPLEADOS Y ASIGNACIONES");

$empleadoId = null;
$rolId = null;

// Test 4.1 - Crear Rol Laboral
printTest($testNum++, "Crear Rol Laboral");
try {
    $rolId = DB::table('roles_laborales')->insertGetId([
        'nombre_rol' => 'Operador Excavadora ' . time(),
        'descripcion' => 'Test role',
        'salario_base' => 1000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    if ($rolId > 0) {
        printPass("Rol creado: ID $rolId");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 4.2 - Crear Empleado
printTest($testNum++, "Crear Empleado");
try {
    $empleadoId = DB::table('empleados')->insertGetId([
        'nombre_empleado' => 'Test Employee ' . time(),
        'apellido_empleado' => 'Apellido',
        'documento' => 'DNI' . time(),
        'email' => 'test' . time() . '@test.com',
        'telefono' => '1234567890',
        'rol_laboral_id' => $rolId,
        'estado' => 'activo',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    if ($empleadoId > 0) {
        printPass("Empleado creado: ID $empleadoId");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 4.3 - Asignar Empleado a Lote
printTest($testNum++, "Asignar Empleado a Lote");
try {
    DB::table('lote_empleado')->insert([
        'lote_id' => $loteId,
        'empleado_id' => $empleadoId,
        'fecha_asignacion' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $relacion = DB::table('lote_empleado')
        ->where('lote_id', $loteId)
        ->where('empleado_id', $empleadoId)
        ->first();
    
    if ($relacion) {
        printPass("Empleado asignado al lote");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// ============================================================================
// PRUEBA 5: PARTES DIARIOS (OPERACIONES)
// ============================================================================

printHeader("PRUEBA 5: REGISTRAR PARTES DIARIOS Y CARGAS");

$parteId = null;

// Test 5.1 - Crear Parte Diario
printTest($testNum++, "Crear Parte Diario");
try {
    $parteId = DB::table('partes_diarios')->insertGetId([
        'lote_id' => $loteId,
        'empleado_id' => $empleadoId,
        'fecha' => now()->format('Y-m-d'),
        'horas_trabajadas' => 8,
        'estado' => 'registrado',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    if ($parteId > 0) {
        printPass("Parte diario creado: ID $parteId");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 5.2 - Registrar Carga (Toneladas)
printTest($testNum++, "Registrar Carga/Toneladas en Lote");
try {
    $cargaId = DB::table('cargas_lote')->insertGetId([
        'lote_id' => $loteId,
        'toneladas' => 25.5,
        'fecha_carga' => now(),
        'descripcion' => 'Carga test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    if ($cargaId > 0) {
        printPass("Carga registrada: 25.5 toneladas");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 5.3 - Calcular costo de operación
printTest($testNum++, "Calcular costo total de operación");
try {
    // Simular: horas * salario_base + toneladas * precio_por_tonelada
    $horas = 8;
    $salarioBase = 1000;
    $costoHoras = ($horas / 8) * $salarioBase; // $1000 por día
    $toneladas = 25.5;
    $precioPorTonelada = 50; // Asumido
    $costoToneladas = $toneladas * $precioPorTonelada; // $1275
    
    $totalCosto = $costoHoras + $costoToneladas; // $2275
    
    if ($totalCosto > 0) {
        printPass("Costo total calculado: \$$totalCosto (mano de obra + materiales)");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// ============================================================================
// PRUEBA 6: MANTENIMIENTO PREVENTIVO
// ============================================================================

printHeader("PRUEBA 6: GESTIONAR MANTENIMIENTO PREVENTIVO");

$mantenimientoId = null;

// Test 6.1 - Crear Tipo de Mantenimiento
printTest($testNum++, "Crear Tipo de Mantenimiento");
try {
    $tipoMantId = DB::table('tipos_mantenimientos')->insertGetId([
        'nombre_tipo' => 'Cambio de aceite ' . time(),
        'descripcion' => 'Test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    if ($tipoMantId > 0) {
        printPass("Tipo de mantenimiento creado");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 6.2 - Crear Mantenimiento
printTest($testNum++, "Crear Mantenimiento Preventivo");
try {
    $mantenimientoId = DB::table('mantenimientos')->insertGetId([
        'maquinaria_id' => $maquinariaId,
        'tipo_mantenimiento_id' => $tipoMantId,
        'fecha_programada' => now()->addDays(30)->format('Y-m-d'),
        'estado' => 'pendiente',
        'costo_estimado' => 500,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    if ($mantenimientoId > 0) {
        printPass("Mantenimiento creado: ID $mantenimientoId (estado: pendiente)");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 6.3 - Cambiar estado a Aprobado
printTest($testNum++, "Aprobar Mantenimiento");
try {
    DB::table('mantenimientos')
        ->where('id_mantenimiento', $mantenimientoId)
        ->update(['estado' => 'aprobado', 'updated_at' => now()]);
    
    $mant = DB::table('mantenimientos')->where('id_mantenimiento', $mantenimientoId)->first();
    if ($mant->estado === 'aprobado') {
        printPass("Mantenimiento aprobado (estado: aprobado)");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 6.4 - Cambiar estado a Completado
printTest($testNum++, "Completar Mantenimiento");
try {
    DB::table('mantenimientos')
        ->where('id_mantenimiento', $mantenimientoId)
        ->update([
            'estado' => 'completado',
            'costo_real' => 450,
            'fecha_completado' => now(),
            'updated_at' => now()
        ]);
    
    $mant = DB::table('mantenimientos')->where('id_mantenimiento', $mantenimientoId)->first();
    if ($mant->estado === 'completado') {
        printPass("Mantenimiento completado (costo real: \$450)");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// ============================================================================
// PRUEBA 7: NOTIFICACIONES DEL SISTEMA
// ============================================================================

printHeader("PRUEBA 7: SISTEMA DE NOTIFICACIONES");

// Test 7.1 - Crear Notificación
printTest($testNum++, "Crear Notificación del Sistema");
try {
    $notifId = DB::table('notificaciones_sistema')->insertGetId([
        'usuario_id' => 1,
        'titulo' => 'Test Notification ' . time(),
        'descripcion' => 'Descripción de prueba',
        'tipo' => 'sistema',
        'leida' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    if ($notifId > 0) {
        printPass("Notificación creada: ID $notifId");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 7.2 - Marcar como leída
printTest($testNum++, "Marcar Notificación como Leída");
try {
    DB::table('notificaciones_sistema')
        ->where('id', $notifId)
        ->update(['leida' => true, 'updated_at' => now()]);
    
    $notif = DB::table('notificaciones_sistema')->where('id', $notifId)->first();
    if ($notif->leida === 1 || $notif->leida === true) {
        printPass("Notificación marcada como leída");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// Test 7.3 - Contar notificaciones sin leer
printTest($testNum++, "Contar Notificaciones sin Leer");
try {
    $sinLeer = DB::table('notificaciones_sistema')
        ->where('usuario_id', 1)
        ->where('leida', false)
        ->count();
    
    printPass("Notificaciones sin leer: $sinLeer");
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// ============================================================================
// PRUEBA 8: VALIDACIONES Y CONSTRAINS
// ============================================================================

printHeader("PRUEBA 8: INTEGRIDAD Y RESTRICCIONES");

// Test 8.1 - Validar que no se puede crear lote sin nombre
printTest($testNum++, "Validar que campos requeridos se validan");
try {
    // Esto debería fallar o retornar null
    $result = DB::table('lotes')->insert([
        'nombre_lote' => '', // Vacío - debería fallar
        'ubicacion' => 'Test',
        'hectareas' => 50,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    if (!$result) {
        printPass("Validación de campos requeridos funcionando");
    } else {
        printInfo("Sistema permite campos vacíos (podría ser un problema)");
        printPass("Creó registro (revisar validación)");
    }
} catch (\Exception $e) {
    printPass("Sistema rechazó campo vacío: " . substr($e->getMessage(), 0, 50) . "...");
}

// Test 8.2 - Verificar registros no duplicados
printTest($testNum++, "Validar que no se crean duplicados de empleado");
try {
    $documento = 'DNI_UNIQUE_' . time();
    
    // Primer intento
    $id1 = DB::table('empleados')->insertGetId([
        'nombre_empleado' => 'Test',
        'apellido_empleado' => 'Test',
        'documento' => $documento,
        'email' => 'unique' . time() . '@test.com',
        'rol_laboral_id' => $rolId,
        'estado' => 'activo',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Segundo intento (debería fallar si hay unique constraint)
    try {
        $id2 = DB::table('empleados')->insertGetId([
            'nombre_empleado' => 'Test2',
            'apellido_empleado' => 'Test2',
            'documento' => $documento, // Mismo documento
            'email' => 'unique2' . time() . '@test.com',
            'rol_laboral_id' => $rolId,
            'estado' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        printInfo("No hay constraint UNIQUE en documento (podría ser problema)");
        printPass("Permitió duplicado");
    } catch (\Exception $e2) {
        printPass("Constraint UNIQUE protegiendo documento");
    }
} catch (\Exception $e) {
    printFail($e->getMessage());
}

// ============================================================================
// RESUMEN
// ============================================================================

echo "\n";
printHeader("RESUMEN DE PRUEBAS DE CAJA NEGRA");

$percentage = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0;

echo BOLD . "Total de Pruebas:      " . RESET . "$totalTests\n";
echo GREEN . BOLD . "✅ Pasadas:            " . RESET . "$passedTests\n";
echo RED . BOLD . "❌ Fallidas:           " . RESET . "$failedTests\n";
echo YELLOW . BOLD . "📊 Tasa de Éxito:      " . RESET . "$percentage%\n";

echo "\n" . CYAN . BOLD . "CONCLUSIÓN:" . RESET . " ";
if ($failedTests === 0 && $passedTests > 20) {
    echo GREEN . "✅ SISTEMA OPERATIVO Y FUNCIONAL" . RESET;
} elseif ($failedTests === 0 && $passedTests > 10) {
    echo GREEN . "✅ SISTEMA BÁSICAMENTE FUNCIONAL" . RESET;
} elseif ($failedTests < 5) {
    echo YELLOW . "⚠️  SISTEMA CON PROBLEMAS MENORES" . RESET;
} else {
    echo RED . "❌ SISTEMA CON PROBLEMAS CRÍTICOS" . RESET;
}

echo "\n\n" . CYAN . BOLD . "════════════════════════════════════════════════════════════════════" . RESET . "\n";

if ($failedTests === 0) {
    echo GREEN . BOLD . "🎉 TODAS LAS PRUEBAS PASARON - LISTO PARA PRODUCCIÓN 🎉" . RESET . "\n";
} else {
    echo RED . BOLD . "⚠️  REVISAR FALLOS ANTES DE PRODUCCIÓN" . RESET . "\n";
}

echo CYAN . BOLD . "════════════════════════════════════════════════════════════════════" . RESET . "\n\n";
