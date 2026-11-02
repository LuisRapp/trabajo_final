<?php

namespace App\Services;

use App\Models\Carga;
use App\Models\CategoriaMadera;
use App\Models\Cliente;
use App\Models\Lote;
use App\Models\ParteDiario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CargaService
{
    public function listarLotes()
    {
        return Lote::all();
    }

    public function listarCategorias()
    {
        return CategoriaMadera::all();
    }

    public function listarPartes()
    {
        return ParteDiario::all();
    }

    public function listarClientes()
    {
        return Cliente::orderBy('razon_social')->get();
    }

    public function listar(?string $busqueda = null, int $pagina = 15): LengthAwarePaginator
    {
        $query = Carga::with(['lote', 'parteDiario', 'categoriaMadera', 'chofer', 'cliente']);

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('ticket', 'ILIKE', "%{$busq}%")
                    ->orWhereDate('fecha_carga', $busq)
                    ->orWhereHas('lote', function ($qr) use ($busq) {
                        $qr->where('propietario', 'ILIKE', "%{$busq}%")
                            ->orWhere('ubicacion', 'ILIKE', "%{$busq}%");
                    })
                    ->orWhereHas('categoriaMadera', function ($qr) use ($busq) {
                        $qr->where('nombre', 'ILIKE', "%{$busq}%");
                    })
                    ->orWhereHas('chofer', function ($qr) use ($busq) {
                        $qr->where('apellido', 'ILIKE', "%{$busq}%")
                            ->orWhere('nombre', 'ILIKE', "%{$busq}%");
                    })
                    ->orWhereHas('cliente', function ($qr) use ($busq) {
                        $qr->where('razon_social', 'ILIKE', "%{$busq}%");
                    });
            });
        }

        return $query->orderBy('id_carga', 'desc')->paginate($pagina);
    }

    public function reglas(): array
    {
        return [
            'id_lote' => 'required|exists:lotes,id_lote',
            'id_categoria_madera' => 'nullable|exists:categoria_maderas,id_categoria_madera',
            'id_chofer' => 'nullable|integer',
            'id_parte_diario' => 'nullable|exists:parte_diarios,id_parte_diario',
            'ticket' => 'nullable|string|max:20',
            'peso_bruto' => 'nullable|numeric|min:0',
            'tara' => 'nullable|numeric|min:0',
            'peso_neto' => 'nullable|numeric|min:0',
            'id_cliente' => 'nullable|exists:clientes,id_cliente',
            'fecha_carga' => 'required|date|before_or_equal:today',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): Carga
    {
        $validator = Validator::make($datos, $this->reglas());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Carga::updateOrCreate(
            ['id_carga' => $id],
            [
                'id_lote' => $datos['id_lote'],
                'id_categoria_madera' => $datos['id_categoria_madera'] ?? null,
                'id_chofer' => $datos['id_chofer'] ?? null,
                'id_parte_diario' => $datos['id_parte_diario'] ?? null,
                'id_cliente' => $datos['id_cliente'] ?? null,
                'ticket' => $datos['ticket'] ?? null,
                'peso_bruto' => $datos['peso_bruto'] ?? null,
                'tara' => $datos['tara'] ?? null,
                'peso_neto' => $datos['peso_neto'] ?? null,
                'fecha_carga' => $datos['fecha_carga'],
            ]
        );
    }

    public function obtener(int $id): Carga
    {
        return Carga::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        Carga::findOrFail($id)->delete();
    }
}
