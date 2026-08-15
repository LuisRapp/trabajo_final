<?php

namespace App\Http\Livewire;

use App\Models\Insumo;
use App\Services\InventarioService;
use Livewire\Component;

class MovimientoForm extends Component
{
    public $movimiento_id_insumo;

    public $movimiento_cantidad;

    public $movimiento_motivo = 'Producción';

    public $movimiento_observaciones;

    public $movimientos = [];

    public $stock_disponible_insumo = null;

    // Props from parent
    public $insumos;

    public function mount($insumos = null, $movimientos = []): void
    {
        $this->insumos = $insumos ?? collect();
        $this->movimientos = $movimientos;
    }

    public function agregarMovimiento(): void
    {
        try {
            $this->validate([
                'movimiento_id_insumo' => 'required|exists:insumos,id_insumo',
                'movimiento_cantidad' => 'required|numeric|min:0.01',
                'movimiento_motivo' => 'required|in:Producción,Mantenimiento,Varios',
            ], [
                'movimiento_id_insumo.required' => 'Debe seleccionar un insumo',
                'movimiento_cantidad.required' => 'La cantidad es obligatoria',
                'movimiento_cantidad.min' => 'La cantidad debe ser mayor a 0',
                'movimiento_motivo.required' => 'El motivo es obligatorio',
            ]);

            $stockDisponible = InventarioService::stockDisponible($this->movimiento_id_insumo);

            if ($this->movimiento_cantidad > $stockDisponible) {
                $this->dispatch('mostrarError', mensaje: "Stock insuficiente. Disponible: {$stockDisponible}");

                return;
            }

            $insumo = Insumo::with('unidadMedida')->find($this->movimiento_id_insumo);

            $this->movimientos[] = [
                'id_insumo' => $insumo->id_insumo,
                'nombre_insumo' => $insumo->nombre,
                'tipo' => 'salida',
                'cantidad' => $this->movimiento_cantidad,
                'motivo' => $this->movimiento_motivo,
                'observaciones' => $this->movimiento_observaciones,
                'unidad' => $insumo->unidadMedida->nombre ?? 'Unidad',
            ];

            $this->dispatch('movimientoAgregado', movData: end($this->movimientos));
            $this->dispatch('mostrarExito', mensaje: 'Insumo agregado correctamente');
            $this->resetMovimientoForm();

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error en agregarMovimiento: '.$e->getMessage());
            $this->dispatch('mostrarError', mensaje: 'Error al agregar el insumo. Intente nuevamente.');
        }
    }

    public function eliminarMovimiento(int $index): void
    {
        unset($this->movimientos[$index]);
        $this->movimientos = array_values($this->movimientos);
        $this->dispatch('movimientoEliminado', index: $index);
    }

    public function updatedMovimientoIdInsumo($value): void
    {
        if ($value) {
            $this->stock_disponible_insumo = InventarioService::stockDisponible($value);
        } else {
            $this->stock_disponible_insumo = null;
        }
    }

    private function resetMovimientoForm(): void
    {
        $this->movimiento_id_insumo = null;
        $this->movimiento_cantidad = null;
        $this->movimiento_motivo = 'Producción';
        $this->movimiento_observaciones = null;
        $this->stock_disponible_insumo = null;
    }

    public function render()
    {
        return view('livewire.movimiento-form');
    }
}
