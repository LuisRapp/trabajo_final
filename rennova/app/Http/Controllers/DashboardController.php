<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\ParteDiario;
use App\Services\InventarioService;

class DashboardController extends Controller
{
    public function index()
    {
        $lotesActivos = Lote::where('estado', 'activo')->get();

        $totalMaquinarias = Maquinaria::where('estado', 'activo')->count();

        // Stock es un accessor computado — se delega al Servicio
        $insumosCriticos = InventarioService::contarInsumosCriticos();

        $partesDiariosMes = ParteDiario::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->count();

        $mantenimientosPendientes = Mantenimiento::where('estado', 'pendiente')->count();

        return view('dashboard', compact(
            'lotesActivos',
            'totalMaquinarias',
            'insumosCriticos',
            'partesDiariosMes',
            'mantenimientosPendientes'
        ));
    }
}
