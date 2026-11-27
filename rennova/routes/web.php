<?php

use App\Http\Controllers\AdelantoController;
use App\Http\Controllers\CargaController;
use App\Http\Controllers\CategoriaMaderaController;
use App\Http\Controllers\ChoferController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\HistoricoCostosMaquinariaController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\MaquinariaController;
use App\Http\Controllers\PanelControlController;
use App\Http\Controllers\ParteDiarioController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReciboController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RolLaboralController;
use App\Http\Controllers\TipoMantenimientoController;
use App\Http\Controllers\TipoMaquinariaController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;
// Livewire ABMs
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// --- RUTAS PÚBLICAS ---
// Página de bienvenida / login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('welcome');

// Las rutas de autenticación (login, register, etc.) deben estar PÚBLICAS
require __DIR__.'/auth.php';

// --- RUTAS PROTEGIDAS (Requieren Iniciar Sesión) ---
Route::middleware(['auth'])->group(function () {

    // Dashboard (usa mismo controlador/flujo que inicio; protegido si se accede por esta ruta)
    Route::get('dashboard', [PanelControlController::class, 'index'])
        ->middleware(['verified'])
        ->name('dashboard');

    // Vistas de módulos principales
    Route::view('/modulos/maquinaria', 'modulos.maquinaria')->name('modulos.maquinaria');
    Route::view('/modulos/inventario-forestal', 'modulos.inventario-forestal')->name('modulos.inventario-forestal');
    Route::view('/modulos/personal', 'modulos.personal')->name('modulos.personal');
    Route::view('/modulos/operaciones', 'modulos.operaciones')->name('modulos.operaciones');
    Route::view('/modulos/administracion', 'modulos.administracion')->name('modulos.administracion');

    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // --- ABMs PRINCIPALES (Ahora protegidos por ver-*) ---
    Route::get('/lotes', [LoteController::class, 'index'])->middleware(['permission:ver-lotes'])->name('lotes.index');
    Route::get('/lotes/{loteId}', function ($loteId) {
        return view('lotes.ficha', ['loteId' => (int) $loteId]);
    })->middleware(['permission:ver-lotes'])->name('lotes.ficha');

    Route::get('/lotes/{loteId}/recomendaciones', function ($loteId) {
        return view('lotes.recomendaciones', ['loteId' => (int) $loteId]);
    })->middleware(['permission:ver-lotes'])->name('lotes.recomendaciones');
    Route::get('/lotes/{loteId}/tareas', function ($loteId) {
        return view('lotes.planificar-tareas', ['loteId' => (int) $loteId]);
    })->middleware(['permission:ver-lotes'])->name('lotes.tareas');
    Route::get('/clientes', [ClienteController::class, 'index'])->middleware(['permission:ver-clientes'])->name('clientes.index');
    Route::get('/proveedores', [ProveedorController::class, 'index'])->middleware(['permission:ver-proveedores'])->name('proveedores.index');
    Route::get('/categorias-madera', [CategoriaMaderaController::class, 'index'])->middleware(['permission:ver-categorias-madera'])->name('categorias-madera.index');
    Route::get('/unidades-medida', [UnidadMedidaController::class, 'index'])->middleware(['permission:ver-unidades-medida'])->name('unidades-medida.index');
    Route::get('/tipos-maquinaria', [TipoMaquinariaController::class, 'index'])->middleware(['permission:ver-tipos-maquinaria'])->name('tipos-maquinaria.index');
    Route::get('/tipos-mantenimiento', [TipoMantenimientoController::class, 'index'])->middleware(['permission:ver-tipos-mantenimiento'])->name('tipos-mantenimiento.index');
    Route::get('/roles-laborales', [RolLaboralController::class, 'index'])->middleware(['permission:ver-roles-laborales'])->name('roles-laborales.index');
    Route::get('/insumos', [InsumoController::class, 'index'])->middleware(['permission:ver-insumos'])->name('insumos.index');
    Route::get('/maquinarias', [MaquinariaController::class, 'index'])->middleware(['permission:ver-maquinarias'])->name('maquinarias.index');
    Route::get('/empleados', [EmpleadoController::class, 'index'])->middleware(['permission:ver-empleados'])->name('empleados.index');
    Route::get('/usuarios', [UsuarioController::class, 'index'])->middleware(['permission:gestionar-usuarios'])->name('usuarios.index');

    // Mantenimientos - Componente Livewire y endpoints de gestión
    Route::view('/mantenimientos', 'mantenimientos.index')->middleware(['permission:ver-mantenimientos'])->name('mantenimientos.index');
    Route::post('/mantenimientos/{id}/aprobar', [MantenimientoController::class, 'aprobar'])
        ->middleware(['permission:confirmar-mantenimiento'])
        ->name('mantenimientos.aprobar');
    Route::post('/mantenimientos/{id}/completar', [MantenimientoController::class, 'completar'])
        ->middleware(['permission:completar-mantenimiento'])
        ->name('mantenimientos.completar');

    // Configuración de Notificaciones de Mantenimiento
    Route::view('/configuracion-notificaciones-mantenimiento', 'configuracion-notificaciones.index')
        ->middleware(['permission:configurar-notificaciones-mantenimiento'])
        ->name('configuracion-notificaciones.index');

    // Configuración de Horarios de Mantenimiento
    Route::view('/configuracion-mantenimiento', 'configuracion-mantenimiento.index')
        ->middleware(['permission:configurar-mantenimiento'])
        ->name('configuracion-mantenimiento.index');

    // Configuración de Parámetros Climáticos
    Route::view('/configuracion-clima', 'configuracion-clima.index')
        ->middleware(['permission:configurar-clima'])
        ->name('configuracion-clima.index');

    // Notificaciones del Sistema
    Route::view('/notificaciones', 'notificaciones.index')->name('notificaciones.index');

    // Programar Mantenimiento (alta de orden) o confirmar orden generada por notificacion
    Route::get('/programar-mantenimiento/{notificacionId?}', function ($notificacionId = null) {
        return view('programar-mantenimiento.index', ['notificacionId' => $notificacionId ?? null]);
    })->middleware(['permission:ver-mantenimientos'])->name('programar-mantenimiento');

    // Configuración de Kits de Mantenimiento Preventivo (UI original)
    Route::view('/kits-mantenimiento', 'kits-mantenimiento.index')->middleware(['permission:ver-kits-mantenimiento'])->name('kits-mantenimiento.index');

    Route::get('/partes-diarios', [ParteDiarioController::class, 'index'])->middleware(['permission:ver-partes-diarios'])->name('partes-diarios.index');
    Route::get('/ventas', [VentaController::class, 'index'])->middleware(['permission:ver-ventas'])->name('ventas.index');
    Route::get('/ventas/{venta}/comprobante', [VentaController::class, 'comprobante'])
        ->middleware(['permission:ver-ventas'])->name('ventas.comprobante');
    Route::get('/adelantos', [AdelantoController::class, 'index'])->middleware(['permission:ver-adelantos'])->name('adelantos.index');
    Route::get('/recibos', [ReciboController::class, 'index'])->middleware(['permission:ver-recibos'])->name('recibos.index');
    Route::get('/recibos/{recibo}/pdf', [ReciboController::class, 'pdf'])->middleware(['permission:ver-recibos'])->name('recibos.pdf');

    Route::get('/historico-costos-maquinarias', [HistoricoCostosMaquinariaController::class, 'index'])->middleware(['permission:ver-historico-costos-maquinarias'])->name('historico-costos-maquinarias.index');
    Route::view('/historico-roles-laborales', 'historico-roles-laborales.index')->middleware(['permission:ver-roles-laborales'])->name('historico-roles-laborales.index');
    Route::view('/lista-precios', 'lista-precios.index')->middleware(['permission:ver-lista-precios'])->name('lista-precios.index');

    // Auditorías
    Route::view('/auditorias', 'auditorias.index')->middleware(['permission:ver-auditoria'])->name('auditorias.index');

    // Estado de procesos automatizados (monitoreo: clima, cola, tareas programadas)
    Route::view('/estado-procesos', 'estado-procesos.index')
        ->middleware(['permission:gestionar-usuarios'])
        ->name('estado-procesos.index');

    // Reportes - Estadísticas Forestales
    Route::get('/reportes/estadisticas-forestales', [ReporteController::class, 'estadisticasForestales'])->middleware(['permission:ver-reportes'])->name('reportes.estadisticas-forestales');
    Route::get('/reportes/estadisticas-forestales/pdf', [ReporteController::class, 'estadisticasForestalesPdf'])->middleware(['permission:ver-reportes'])->name('reportes.estadisticas-forestales.pdf');
    Route::get('/reportes/clima-lluvias/pdf', [ReporteController::class, 'climaLluviasPdf'])->middleware(['permission:ver-reportes'])->name('reportes.clima-lluvias.pdf');

    // Liquidación de Pagos
    Route::view('/liquidacion-pagos', 'liquidacion-pagos.index')->middleware(['permission:ver-liquidacion-pagos'])->name('liquidacion-pagos.index');

    // Asignaciones por Lote (Empleados y Maquinaria)
    Route::view('/asignaciones-lote', 'asignaciones-lote.index')->middleware(['permission:ver-asignaciones-lote'])->name('asignaciones-lote.index');

    // Propuestas automáticas de asignación (basadas en histórico)
    Route::view('/propuestas-asignacion', 'propuestas-asignacion.index')->middleware(['permission:ver-propuestas-asignacion'])->name('propuestas-asignacion.index');

    // Gestión de Stock (FIFO) dentro del módulo Operaciones
    Route::view('/modulos/operaciones/gestionstock', 'modulos.operaciones.gestionstock')->middleware(['permission:ver-gestion-stock'])->name('modulos.operaciones.gestionstock');
    Route::get('/reportes/stock', [\App\Http\Controllers\StockReporteController::class, 'exportar'])
        ->middleware(['permission:ver-gestion-stock'])->name('reportes.stock');

    // ABM Cargas
    Route::get('/cargas', [CargaController::class, 'index'])->middleware(['permission:ver-cargas'])->name('cargas.index');
    Route::get('/cargas/create', [CargaController::class, 'create'])->middleware(['permission:crear-cargas'])->name('cargas.create');
    Route::post('/cargas', [CargaController::class, 'store'])->middleware(['permission:crear-cargas'])->name('cargas.store');
    Route::get('/cargas/{carga}/edit', [CargaController::class, 'edit'])->middleware(['permission:editar-cargas'])->name('cargas.edit');
    Route::put('/cargas/{carga}', [CargaController::class, 'update'])->middleware(['permission:editar-cargas'])->name('cargas.update');
    Route::delete('/cargas/{carga}', [CargaController::class, 'destroy'])->middleware(['permission:eliminar-cargas'])->name('cargas.destroy');

    // ABM Choferes
    Route::get('/choferes', [ChoferController::class, 'index'])->middleware(['permission:ver-choferes'])->name('choferes.index');
    Route::get('/choferes/create', [ChoferController::class, 'create'])->middleware(['permission:crear-choferes'])->name('choferes.create');
    Route::post('/choferes', [ChoferController::class, 'store'])->middleware(['permission:crear-choferes'])->name('choferes.store');
    Route::get('/choferes/{chofer}/edit', [ChoferController::class, 'edit'])->middleware(['permission:editar-choferes'])->name('choferes.edit');
    Route::put('/choferes/{chofer}', [ChoferController::class, 'update'])->middleware(['permission:editar-choferes'])->name('choferes.update');
    Route::delete('/choferes/{chofer}', [ChoferController::class, 'destroy'])->middleware(['permission:eliminar-choferes'])->name('choferes.destroy');

    // --- RUTAS DE ADMINISTRACIÓN (Protegidas por Permiso) ---
    // Esta ruta ya estaba protegida, la mantenemos dentro del grupo auth.
    Route::view('/roles-permisos', 'roles-permisos.index')->middleware(['permission:gestionar-permisos'])->name('roles-permisos.index');

});
