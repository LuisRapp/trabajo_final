<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CategoriaMaderaController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\TipoMaquinariaController;
use App\Http\Controllers\RolLaboralController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\MaquinariaController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\ParteDiarioController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\AdelantoController;
use App\Http\Controllers\ReciboController;
use App\Http\Controllers\HistoricoCostosMaquinariaController;
// Livewire ABMs
use App\Http\Livewire\HistoricoRolesLaborales;
use App\Http\Controllers\CargaController;
use App\Http\Controllers\ChoferController;

// --- RUTAS PÚBLICAS ---
// Página principal (pública)
Route::get('/', function () {
    return view('index');
})->name('home');

// Las rutas de autenticación (login, register, etc.) deben estar PÚBLICAS
require __DIR__.'/auth.php';


// --- RUTAS PROTEGIDAS (Requieren Iniciar Sesión) ---
Route::middleware(['auth'])->group(function () {

    // Dashboard y configuración (ya estaban protegidos)
    Route::view('dashboard', 'dashboard')
        ->middleware(['verified'])
        ->name('dashboard');

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

    // --- ABMs PRINCIPALES (Ahora protegidos) ---
    Route::get('/lotes', [LoteController::class, 'index'])->name('lotes.index');
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
    Route::get('/categorias-madera', [CategoriaMaderaController::class, 'index'])->name('categorias-madera.index');
    Route::get('/unidades-medida', [UnidadMedidaController::class, 'index'])->name('unidades-medida.index');
    Route::get('/tipos-maquinaria', [TipoMaquinariaController::class, 'index'])->name('tipos-maquinaria.index');
    Route::get('/roles-laborales', [RolLaboralController::class, 'index'])->name('roles-laborales.index');
    Route::get('/insumos', [InsumoController::class, 'index'])->name('insumos.index');
    Route::get('/maquinarias', [MaquinariaController::class, 'index'])->name('maquinarias.index');
    Route::get('/empleados', [EmpleadoController::class, 'index'])->name('empleados.index');
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('/mantenimientos', [MantenimientoController::class, 'index'])->name('mantenimientos.index');
    Route::get('/partes-diarios', [ParteDiarioController::class, 'index'])->name('partes-diarios.index');
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/adelantos', [AdelantoController::class, 'index'])->name('adelantos.index');
    Route::get('/recibos', [ReciboController::class, 'index'])->name('recibos.index');

    Route::get('/historico-costos-maquinarias', [HistoricoCostosMaquinariaController::class, 'index'])->name('historico-costos-maquinarias.index');
    Route::view('/historico-roles-laborales', 'historico-roles-laborales.index')->name('historico-roles-laborales.index');
    Route::view('/lista-precios', 'lista-precios.index')->name('lista-precios.index');
    
    // ABM Cargas
    Route::get('/cargas', [CargaController::class, 'index'])->name('cargas.index');
    Route::get('/cargas/create', [CargaController::class, 'create'])->name('cargas.create');
    Route::post('/cargas', [CargaController::class, 'store'])->name('cargas.store');
    Route::get('/cargas/{carga}/edit', [CargaController::class, 'edit'])->name('cargas.edit');
    Route::put('/cargas/{carga}', [CargaController::class, 'update'])->name('cargas.update');
    Route::delete('/cargas/{carga}', [CargaController::class, 'destroy'])->name('cargas.destroy');

    // ABM Choferes
    Route::get('/choferes', [ChoferController::class, 'index'])->name('choferes.index');
    Route::get('/choferes/create', [ChoferController::class, 'create'])->name('choferes.create');
    Route::post('/choferes', [ChoferController::class, 'store'])->name('choferes.store');
    Route::get('/choferes/{chofer}/edit', [ChoferController::class, 'edit'])->name('choferes.edit');
    Route::put('/choferes/{chofer}', [ChoferController::class, 'update'])->name('choferes.update');
    Route::delete('/choferes/{chofer}', [ChoferController::class, 'destroy'])->name('choferes.destroy');

    // --- RUTAS DE ADMINISTRACIÓN (Protegidas por Permiso) ---
    // Esta ruta ya estaba protegida, la mantenemos dentro del grupo auth.
    Route::view('/roles-permisos', 'roles-permisos.index')->middleware(['permission:gestionar-permisos'])->name('roles-permisos.index');

});

