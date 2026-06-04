<?php

namespace App\Http\Livewire;

use App\Models\Adelanto;
use App\Models\Empleado;
use App\Models\Recibo;
use App\Services\EmpleadoPagoService;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class LiquidacionPagos extends Component
{
    public $empleados = [];

    public $id_empleado;

    public $fecha_inicio;

    public $fecha_fin;

    // Datos calculados
    public $calculo = null;

    public $empleado_seleccionado = null;

    public $adelantos_pendientes = [];

    public $total_adelantos = 0;

    // Datos editables para el recibo
    public $monto_bruto;

    public $descuentos = 0;

    public $monto_neto;

    public $observaciones;

    // Control de flujo
    public $mostrar_liquidacion = false;

    public $recibo_generado = false;

    public $liquidar_todos = false;

    protected $rules = [
        'id_empleado' => 'required|exists:empleados,id_empleado',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        'monto_bruto' => 'required|numeric|min:0',
        'descuentos' => 'nullable|numeric|min:0',
        'observaciones' => 'nullable|string|max:150',
    ];

    protected $messages = [
        'id_empleado.required' => 'Debe seleccionar un empleado',
        'fecha_inicio.required' => 'Debe ingresar una fecha de inicio',
        'fecha_fin.required' => 'Debe ingresar una fecha de fin',
        'fecha_fin.after_or_equal' => 'La fecha fin debe ser posterior o igual a la fecha de inicio',
        'monto_bruto.required' => 'El monto bruto es requerido',
        'monto_bruto.min' => 'El monto bruto no puede ser negativo',
        'descuentos.min' => 'Los descuentos no pueden ser negativos',
    ];

    public function mount()
    {
        $this->empleados = Empleado::with('rolLaboral')
            ->whereNull('fecha_fin_actividades')
            ->orderBy('apellido')
            ->get();

        // Valores por defecto: mes actual
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function calcularLiquidacion()
    {
        if ($this->liquidar_todos) {
            $this->liquidarTodos();

            return;
        }

        $this->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $this->empleado_seleccionado = Empleado::with('rolLaboral')->find($this->id_empleado);

        if (! $this->empleado_seleccionado) {
            session()->flash('error', 'Empleado no encontrado');

            return;
        }

        // Ejecutar el cálculo
        $this->calculo = EmpleadoPagoService::calcularPagoRango(
            $this->empleado_seleccionado,
            $this->fecha_inicio,
            $this->fecha_fin
        );

        // Buscar adelantos pendientes en el rango de fechas
        $this->adelantos_pendientes = Adelanto::where('id_empleado', $this->id_empleado)
            ->where('estado', 'pendiente')
            ->whereBetween('fecha_emision', [$this->fecha_inicio, $this->fecha_fin])
            ->get();

        $this->total_adelantos = $this->adelantos_pendientes->sum('monto');

        // Cargar valores editables
        $this->monto_bruto = $this->calculo['total_pagar_final'];
        $this->descuentos = $this->total_adelantos; // Descuento automático por adelantos
        $this->monto_neto = $this->monto_bruto - $this->descuentos;

        // Generar observaciones automáticas
        $obs_base = sprintf(
            'Liquidación período %s a %s - %d días caídos + %.2f ton',
            Carbon::parse($this->fecha_inicio)->format('d/m/Y'),
            Carbon::parse($this->fecha_fin)->format('d/m/Y'),
            $this->calculo['cantidad_dias_caidos'],
            $this->calculo['total_peso_toneladas'] ?? 0
        );

        if ($this->total_adelantos > 0) {
            $obs_base .= sprintf(' | Adelantos: $%.2f', $this->total_adelantos);
        }

        $this->observaciones = $obs_base;

        $this->mostrar_liquidacion = true;
        $this->recibo_generado = false;
    }

    public function liquidarTodos()
    {
        $this->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $empleados = Empleado::with('rolLaboral')
            ->whereNull('fecha_fin_actividades')
            ->orderBy('apellido')
            ->get();

        if ($empleados->isEmpty()) {
            session()->flash('error', 'No hay empleados activos para liquidar.');

            return;
        }

        $periodo = Carbon::parse($this->fecha_inicio)->format('d/m/Y').' a '.Carbon::parse($this->fecha_fin)->format('d/m/Y');
        $recibosParaEmail = [];

        try {
            \DB::beginTransaction();
            $generadoPor = auth()->user()->name ?? auth()->user()->email ?? 'Usuario';
            $fechaGeneracion = Carbon::now()->format('d/m/Y H:i');

            foreach ($empleados as $empleado) {
                $calculo = EmpleadoPagoService::calcularPagoRango($empleado, $this->fecha_inicio, $this->fecha_fin);

                $adelantosPendientes = Adelanto::where('id_empleado', $empleado->id_empleado)
                    ->where('estado', 'pendiente')
                    ->whereBetween('fecha_emision', [$this->fecha_inicio, $this->fecha_fin])
                    ->get();

                $totalAdelantos = $adelantosPendientes->sum('monto');

                $montoBruto = $calculo['total_pagar_final'];
                $descuentos = $totalAdelantos;
                $montoNeto = max(0, $montoBruto - $descuentos);

                $obs_base = sprintf(
                    'Liquidación período %s a %s - %d días caídos + %.2f ton',
                    Carbon::parse($this->fecha_inicio)->format('d/m/Y'),
                    Carbon::parse($this->fecha_fin)->format('d/m/Y'),
                    $calculo['cantidad_dias_caidos'],
                    $calculo['total_peso_toneladas'] ?? 0
                );

                if ($totalAdelantos > 0) {
                    $obs_base .= sprintf(' | Adelantos: $%.2f', $totalAdelantos);
                }

                $recibo = Recibo::create([
                    'id_empleado' => $empleado->id_empleado,
                    'fecha_emision' => now(),
                    'monto_bruto' => $montoBruto,
                    'descuentos' => $descuentos,
                    'monto' => $montoNeto,
                    'observaciones' => $obs_base,
                ]);

                if ($adelantosPendientes->isNotEmpty()) {
                    foreach ($adelantosPendientes as $adelanto) {
                        $adelanto->estado = 'pagado';
                        $adelanto->save();
                    }
                }

                $recibosParaEmail[] = [
                    'recibo' => $recibo,
                    'empleado_nombre' => trim(($empleado->apellido ?? '').' '.($empleado->nombre ?? '')) ?: 'N/A',
                    'empleado_rol' => $empleado->rolLaboral->nombre ?? $empleado->rolLaboral->descripcion ?? 'N/A',
                    'empleado_dni' => $empleado->dni ?? null,
                ];
            }

            \DB::commit();

            try {
                $options = new Options;
                $options->set('defaultFont', 'DejaVu Sans');

                $body = 'Se generaron '.count($recibosParaEmail)." comprobantes de pago.\n";
                $body .= "Período: {$periodo}\n";

                Mail::raw($body, function ($message) use ($recibosParaEmail, $periodo, $options, $generadoPor, $fechaGeneracion) {
                    $message->to('contabilidad@rennova.com')
                        ->subject('Comprobantes de pago - Liquidación masiva ('.$periodo.')');

                    foreach ($recibosParaEmail as $item) {
                        $dompdf = new Dompdf($options);
                        $html = view('recibos.pdf.comprobante', [
                            'recibo' => $item['recibo'],
                            'empleado_nombre' => $item['empleado_nombre'],
                            'empleado_rol' => $item['empleado_rol'],
                            'empleado_dni' => $item['empleado_dni'],
                            'periodo' => $periodo,
                            'generado_por' => $generadoPor,
                            'fecha_generacion' => $fechaGeneracion,
                        ])->render();
                        $dompdf->loadHtml($html, 'UTF-8');
                        $dompdf->setPaper('A4', 'portrait');
                        $dompdf->render();

                        $pdfOutput = $dompdf->output();
                        $pdfFilename = 'comprobante-recibo-'.$item['recibo']->id_recibo.'.pdf';
                        $message->attachData($pdfOutput, $pdfFilename, ['mime' => 'application/pdf']);
                    }
                });
            } catch (\Throwable $mailException) {
                session()->flash('error', 'Se generaron los recibos, pero no se pudo enviar el correo a contabilidad.');
            }

            session()->flash('message', 'Se generaron '.count($recibosParaEmail).' recibos y se enviaron a contabilidad.');
            $this->dispatch('reciboGenerado');
        } catch (\Throwable $e) {
            \DB::rollBack();
            session()->flash('error', 'Error al liquidar a todos: '.$e->getMessage());
        }
    }

    public function updatedDescuentos()
    {
        $this->calcularMontoNeto();
    }

    public function updatedMontoBruto()
    {
        $this->calcularMontoNeto();
    }

    private function calcularMontoNeto()
    {
        $this->monto_neto = max(0, ($this->monto_bruto ?? 0) - ($this->descuentos ?? 0));
    }

    public function generarRecibo()
    {
        $this->validate([
            'monto_bruto' => 'required|numeric|min:0',
            'descuentos' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:150',
        ]);

        try {
            \DB::beginTransaction();

            $recibo = Recibo::create([
                'id_empleado' => $this->empleado_seleccionado->id_empleado,
                'fecha_emision' => now(),
                'monto_bruto' => $this->monto_bruto,
                'descuentos' => $this->descuentos ?? 0,
                'monto' => $this->monto_neto,
                'observaciones' => $this->observaciones,
            ]);

            // Marcar adelantos como pagados
            if ($this->adelantos_pendientes && count($this->adelantos_pendientes) > 0) {
                foreach ($this->adelantos_pendientes as $adelanto) {
                    $adelanto->estado = 'pagado';
                    $adelanto->save();
                }
            }

            \DB::commit();

            $this->recibo_generado = true;

            $mensaje = 'Recibo #'.$recibo->id_recibo.' generado correctamente.';
            if (count($this->adelantos_pendientes) > 0) {
                $mensaje .= ' Se marcaron '.count($this->adelantos_pendientes).' adelanto(s) como pagado(s).';
            }

            try {
                $empleadoNombre = trim(($this->empleado_seleccionado->apellido ?? '').' '.($this->empleado_seleccionado->nombre ?? ''));
                $empleadoRol = $this->empleado_seleccionado->rolLaboral->nombre ?? $this->empleado_seleccionado->rolLaboral->descripcion ?? 'N/A';
                $empleadoDni = $this->empleado_seleccionado->dni ?? null;
                $periodo = Carbon::parse($this->fecha_inicio)->format('d/m/Y').' a '.Carbon::parse($this->fecha_fin)->format('d/m/Y');

                $generadoPor = auth()->user()->name ?? auth()->user()->email ?? 'Usuario';
                $fechaGeneracion = Carbon::now()->format('d/m/Y H:i');

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
                $pdfOutput = $dompdf->output();
                $pdfFilename = 'comprobante-recibo-'.$recibo->id_recibo.'.pdf';

                $body = "Se generó un comprobante de pago.\n\n";
                $body .= "Recibo: #{$recibo->id_recibo}\n";
                $body .= 'Empleado: '.($empleadoNombre ?: 'N/A')."\n";
                $body .= "Período: {$periodo}\n";
                $body .= 'Monto bruto: ARS '.number_format($this->monto_bruto, 2)."\n";
                $body .= 'Descuentos: ARS '.number_format($this->descuentos ?? 0, 2)."\n";
                $body .= 'Monto neto: ARS '.number_format($this->monto_neto, 2)."\n";
                $body .= 'Detalle: '.($this->observaciones ?? 'Sin detalle')."\n";
                $body .= 'Fecha emisión: '.Carbon::parse($recibo->fecha_emision)->format('d/m/Y H:i')."\n";

                Mail::raw($body, function ($message) use ($recibo, $empleadoNombre, $pdfOutput, $pdfFilename) {
                    $asunto = 'Comprobante de pago - Recibo #'.$recibo->id_recibo;
                    if (! empty($empleadoNombre)) {
                        $asunto .= ' - '.$empleadoNombre;
                    }

                    $message->to('contabilidad@rennova.com')
                        ->subject($asunto)
                        ->attachData($pdfOutput, $pdfFilename, ['mime' => 'application/pdf']);
                });
            } catch (\Throwable $mailException) {
                session()->flash('error', 'El recibo se generó, pero no se pudo enviar el correo a contabilidad.');
            }

            session()->flash('message', $mensaje);

            // Limpiar formulario después de 2 segundos (opcional)
            $this->dispatch('reciboGenerado');

        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Error al generar el recibo: '.$e->getMessage());
        }
    }

    public function nuevaLiquidacion()
    {
        $this->reset([
            'id_empleado',
            'calculo',
            'empleado_seleccionado',
            'adelantos_pendientes',
            'total_adelantos',
            'monto_bruto',
            'descuentos',
            'monto_neto',
            'observaciones',
            'mostrar_liquidacion',
            'recibo_generado',
        ]);
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.liquidacion-pagos');
    }
}
