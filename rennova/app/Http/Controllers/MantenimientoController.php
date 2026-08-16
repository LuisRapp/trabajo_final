<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento;
use App\Services\MantenimientoService;
use Illuminate\Http\Request;

class MantenimientoController extends Controller
{
    protected $mantenimientoService;

    public function __construct(MantenimientoService $mantenimientoService)
    {
        $this->mantenimientoService = $mantenimientoService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('mantenimientos.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Mantenimiento $mantenimiento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mantenimiento $mantenimiento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mantenimiento $mantenimiento)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mantenimiento $mantenimiento)
    {
        //
    }

    /**
     * Aprobar una orden de mantenimiento.
     * Delega la regla de negocio al servicio.
     */
    public function aprobar($id)
    {
        $resultado = $this->mantenimientoService->aprobarMantenimiento((int) $id);

        if ($resultado['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Orden aprobada exitosamente',
                'mantenimiento' => $resultado['mantenimiento'],
            ]);
        }

        $codigo = isset($resultado['insumos_insuficientes']) ? 422 : 400;

        return response()->json([
            'success' => false,
            'message' => $resultado['message'],
            'insumos_insuficientes' => $resultado['insumos_insuficientes'] ?? null,
        ], $codigo);
    }

    /**
     * Completar un mantenimiento.
     * Valida el request y delega la operación al servicio.
     */
    public function completar(Request $request, $id)
    {
        $request->validate([
            'insumos' => 'required|array|min:1',
            'insumos.*.id_insumo' => 'required|exists:insumos,id_insumo',
            'insumos.*.cantidad_utilizada' => 'required|numeric|min:0.01',
            'insumos.*.costo_unitario' => 'nullable|numeric|min:0',
            'costo_mano_obra' => 'nullable|numeric|min:0',
        ]);

        $resultado = $this->mantenimientoService->completarMantenimiento(
            (int) $id,
            $request->insumos,
            (float) ($request->costo_mano_obra ?? 0)
        );

        if ($resultado['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Mantenimiento completado exitosamente',
                'mantenimiento' => $resultado['mantenimiento'],
                'costo_total' => $resultado['costo_total'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $resultado['message'],
        ], 400);
    }
}
