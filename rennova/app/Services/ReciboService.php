<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Recibo;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReciboService
{
    public function listarEmpleados()
    {
        return Empleado::all();
    }

    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = Recibo::with('empleado');

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->whereRaw('CAST(monto AS TEXT) ILIKE ?', ['%'.$busq.'%'])
                    ->orWhereRaw('CAST(monto_bruto AS TEXT) ILIKE ?', ['%'.$busq.'%'])
                    ->orWhereDate('fecha_emision', $busq)
                    ->orWhereHas('empleado', function ($qe) use ($busq) {
                        $qe->where('apellido', 'ILIKE', '%'.$busq.'%')
                            ->orWhere('nombre', 'ILIKE', '%'.$busq.'%');
                    });
            });
        }

        return $query->orderBy('id_recibo', 'desc')->paginate(15);
    }

    public function reglas(): array
    {
        return [
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'fecha_emision' => 'required|date',
            'monto_bruto' => 'required|numeric|min:0',
            'descuentos' => 'nullable|numeric|min:0|lte:monto_bruto',
            'observaciones' => 'nullable|string|max:150',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): Recibo
    {
        $validator = Validator::make($datos, $this->reglas());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $montoBruto = (float) $datos['monto_bruto'];
        $descuentos = (float) ($datos['descuentos'] ?? 0);
        $monto = max(0, $montoBruto - $descuentos);

        return Recibo::updateOrCreate(
            ['id_recibo' => $id],
            [
                'id_empleado' => $datos['id_empleado'],
                'fecha_emision' => $datos['fecha_emision'],
                'monto_bruto' => $montoBruto,
                'descuentos' => $descuentos,
                'monto' => $monto,
                'observaciones' => $datos['observaciones'] ?? null,
            ]
        );
    }

    public function obtener(int $id): Recibo
    {
        return Recibo::findOrFail($id);
    }

    /**
     * Baja logica del recibo. Libera las cargas y jornales que habia pagado
     * para que puedan volver a liquidarse.
     */
    public function eliminar(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $recibo = Recibo::findOrFail($id);

            DB::table('carga_empleado')->where('id_recibo', $recibo->id_recibo)->update(['id_recibo' => null]);
            DB::table('parte_diario_empleado')->where('id_recibo', $recibo->id_recibo)->update(['id_recibo' => null]);

            $recibo->delete();
        });
    }

    /**
     * Genera el comprobante de pago en PDF del recibo.
     *
     * El periodo se toma de las observaciones (donde lo deja la liquidacion)
     * para que una reimpresion conserve el dato.
     *
     * @return array{output: string, filename: string}
     */
    public function comprobantePdf(Recibo $recibo, ?string $periodo = null, ?string $generadoPor = null, ?string $fechaGeneracion = null): array
    {
        $recibo->loadMissing(['empleado.rolLaboral']);
        $empleado = $recibo->empleado;

        $empleadoNombre = trim(($empleado?->apellido ?? '').' '.($empleado?->nombre ?? ''));
        $empleadoRol = $empleado?->rolLaboral?->nombre ?? 'N/A';
        $empleadoDni = $empleado?->dni;

        $periodo = $periodo
            ?: $this->periodoDesdeObservaciones($recibo->observaciones)
            ?: Carbon::parse($recibo->fecha_emision)->format('d/m/Y');
        $generadoPor = $generadoPor ?: (auth()->user()->name ?? auth()->user()->email ?? 'Sistema');
        $fechaGeneracion = $fechaGeneracion ?: Carbon::now()->format('d/m/Y H:i');

        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);

        $html = view('recibos.pdf.comprobante', [
            'recibo' => $recibo,
            'empleado_nombre' => $empleadoNombre ?: 'N/A',
            'empleado_rol' => $empleadoRol,
            'empleado_dni' => $empleadoDni,
            'periodo' => $periodo,
            'generado_por' => $generadoPor,
            'fecha_generacion' => $fechaGeneracion,
        ])->render();

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return [
            'output' => $dompdf->output(),
            'filename' => 'comprobante-recibo-'.$recibo->id_recibo.'.pdf',
        ];
    }

    private function periodoDesdeObservaciones(?string $observaciones): ?string
    {
        if ($observaciones && preg_match('/(\d{2}\/\d{2}\/\d{4}\s+a\s+\d{2}\/\d{2}\/\d{4})/', $observaciones, $coincidencia)) {
            return $coincidencia[1];
        }

        return null;
    }
}
