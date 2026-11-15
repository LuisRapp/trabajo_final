<?php

namespace App\Services;

use App\Models\CategoriaClientePrecio;
use App\Models\CategoriaMadera;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ListaPrecioService
{
    public function listarClientes()
    {
        return Cliente::orderBy('razon_social')->get();
    }

    public function listarCategorias()
    {
        return CategoriaMadera::orderBy('nombre')->get();
    }

    public function listar(?string $busqueda = null, bool $mostrarHistorico = false): LengthAwarePaginator
    {
        $query = CategoriaClientePrecio::with(['cliente', 'categoria']);

        if (! $mostrarHistorico) {
            $query->actuales();
        }

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->whereHas('cliente', function ($qc) use ($busq) {
                    $qc->where('razon_social', 'ILIKE', '%'.$busq.'%');
                })
                    ->orWhereHas('categoria', function ($qcat) use ($busq) {
                        $qcat->where('nombre', 'ILIKE', '%'.$busq.'%');
                    });
            });
        }

        return $query->orderByRaw('fecha_hasta IS NULL DESC')
            ->orderBy('fecha_desde', 'desc')
            ->paginate(15);
    }

    public function reglas(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id_cliente',
            'categoria_id' => 'required|exists:categoria_maderas,id_categoria_madera',
            'precio' => 'required|numeric|min:0',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ];
    }

    /**
     * Crea o actualiza un precio, aplicando las reglas de solapamiento y cierre del precio anterior.
     *
     * @return array{success: bool, message?: string, precio?: CategoriaClientePrecio}
     *
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): array
    {
        $validator = Validator::make($datos, $this->reglas());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $clienteId = $datos['cliente_id'];
        $categoriaId = $datos['categoria_id'];
        $fechaDesde = $datos['fecha_desde'];
        $fechaHasta = $datos['fecha_hasta'] ?? null;

        if ($this->haySolapamiento($clienteId, $categoriaId, $fechaDesde, $fechaHasta, $id)) {
            return [
                'success' => false,
                'message' => 'Ya existe un precio vigente para este cliente y categoría en el período seleccionado. Las fechas no pueden solaparse.',
            ];
        }

        $precio = DB::transaction(function () use ($id, $datos, $clienteId, $categoriaId, $fechaDesde, $fechaHasta) {
            // Si creamos un precio actual (sin fecha hasta), cerramos el precio anterior
            if (! $id && ! $fechaHasta) {
                $precioAnterior = CategoriaClientePrecio::where('cliente_id', $clienteId)
                    ->where('categoria_id', $categoriaId)
                    ->whereNull('fecha_hasta')
                    ->first();

                if ($precioAnterior) {
                    $fechaCierre = Carbon::parse($fechaDesde)->subDay()->toDateString();
                    $precioAnterior->update(['fecha_hasta' => $fechaCierre]);
                }
            }

            return CategoriaClientePrecio::updateOrCreate(
                ['id' => $id],
                [
                    'cliente_id' => $clienteId,
                    'categoria_id' => $categoriaId,
                    'precio' => $datos['precio'],
                    'fecha_desde' => $fechaDesde,
                    'fecha_hasta' => $fechaHasta,
                ]
            );
        });

        return ['success' => true, 'precio' => $precio];
    }

    private function haySolapamiento(int $clienteId, int $categoriaId, string $fechaDesde, ?string $fechaHasta, ?int $id): bool
    {
        return CategoriaClientePrecio::where('cliente_id', $clienteId)
            ->where('categoria_id', $categoriaId)
            ->when($id, fn ($q) => $q->where('id', '!=', $id))
            ->where(function ($query) use ($fechaDesde, $fechaHasta) {
                $query->where(function ($q) use ($fechaDesde) {
                    $q->where('fecha_desde', '<=', $fechaDesde)
                        ->where(function ($subq) use ($fechaDesde) {
                            $subq->whereNull('fecha_hasta')
                                ->orWhere('fecha_hasta', '>=', $fechaDesde);
                        });
                })
                    ->orWhere(function ($q) use ($fechaHasta) {
                        if ($fechaHasta) {
                            $q->where('fecha_desde', '<=', $fechaHasta)
                                ->where(function ($subq) use ($fechaHasta) {
                                    $subq->whereNull('fecha_hasta')
                                        ->orWhere('fecha_hasta', '>=', $fechaHasta);
                                });
                        }
                    })
                    ->orWhere(function ($q) use ($fechaDesde, $fechaHasta) {
                        if ($fechaHasta) {
                            $q->where('fecha_desde', '>=', $fechaDesde)
                                ->where('fecha_desde', '<=', $fechaHasta);
                        }
                    });
            })
            ->exists();
    }

    public function obtener(int $id): CategoriaClientePrecio
    {
        return CategoriaClientePrecio::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        CategoriaClientePrecio::findOrFail($id)->delete();
    }
}
