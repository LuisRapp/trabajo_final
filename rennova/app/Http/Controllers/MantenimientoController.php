<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento;
use App\Services\MantenimientoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
     * Aprobar una orden de mantenimiento
     * BLOQUEA si no hay stock suficiente de los insumos del kit
     */
    public function approve($id)
    {
        try {
            $mantenimiento = Mantenimiento::findOrFail($id);

            // Verificar estado actual
            if ($mantenimiento->estado !== 'programado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden aprobar órdenes en estado "programado"'
                ], 400);
            }

            // Verificar stock disponible
            $verificacion = $this->mantenimientoService->verificarStockParaAprobacion($id);

            if (!$verificacion['puede_aprobar']) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay stock suficiente para aprobar esta orden',
                    'insumos_insuficientes' => $verificacion['insuficientes']
                ], 422);
            }

            // Aprobar la orden (cambiar estado a "en curso")
            $mantenimiento->update([
                'estado' => 'en curso'
            ]);

            Log::info("Orden de mantenimiento aprobada", [
                'mantenimiento_id' => $id,
                'maquinaria_id' => $mantenimiento->id_maquinaria
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Orden aprobada exitosamente',
                'mantenimiento' => $mantenimiento
            ]);

        } catch (\Exception $e) {
            Log::error("Error aprobando mantenimiento", [
                'mantenimiento_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar la orden: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Completar un mantenimiento
     * Descuenta insumos, calcula costos y toma snapshot
     */
    public function complete(Request $request, $id)
    {
        $request->validate([
            'insumos' => 'required|array|min:1',
            'insumos.*.id_insumo' => 'required|exists:insumos,id_insumo',
            'insumos.*.cantidad_utilizada' => 'required|numeric|min:0.01',
            'insumos.*.costo_unitario' => 'nullable|numeric|min:0',
            'costo_mano_obra' => 'nullable|numeric|min:0'
        ]);

        try {
            $mantenimiento = Mantenimiento::findOrFail($id);

            // Verificar estado
            if ($mantenimiento->estado === 'completado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este mantenimiento ya está completado'
                ], 400);
            }

            // Completar mantenimiento usando el servicio
            $resultado = $this->mantenimientoService->completarMantenimiento(
                $id,
                $request->insumos,
                $request->costo_mano_obra ?? 0
            );

            if ($resultado['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mantenimiento completado exitosamente',
                    'mantenimiento' => $resultado['mantenimiento'],
                    'costo_total' => $resultado['costo_total']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message']
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error("Error completando mantenimiento", [
                'mantenimiento_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al completar el mantenimiento: ' . $e->getMessage()
            ], 500);
        }
    }
}
