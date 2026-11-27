<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Models\Adelanto;
use App\Models\Empleado;
use App\Models\Recibo;
use App\Services\EmpleadoPagoService;
use App\Services\ReciboService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class LiquidacionPagos extends Component
{
    use MensajesErrorUsuario;

    public $empleados = [];

    public $id_empleado;

    public $fecha_inicio;

    public $fecha_fin;

    // Datos calculados
    public $calculo = null;

    public $empleado_seleccionado = null;

    public $adelantos_pendientes = [];

    public $total_adelantos = 0;

    public $saldo_adelantos = 0;

    public $descuento_adelantos = 0;

    // Datos editables para el recibo
    public $monto_bruto;

    public $descuentos = 0;

    public $monto_neto;

    public $observaciones;

    // Control de flujo
    public $mostrar_liquidacion = false;

    public $recibo_generado = false;

    public $recibo_generado_id;

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
        $this->authorize('crear-liquidacion-pagos');
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

        // Adelantos pendientes: todos, no solo los del periodo, porque el
        // saldo se arrastra a la liquidacion siguiente.
        $this->adelantos_pendientes = Adelanto::where('id_empleado', $this->id_empleado)
            ->where('estado', 'pendiente')
            ->orderBy('fecha_emision')
            ->get();

        $this->saldo_adelantos = round($this->adelantos_pendientes->sum(fn ($adelanto) => $adelanto->saldoPendiente()), 2);
        $this->total_adelantos = $this->saldo_adelantos;

        // Cargar valores editables
        $this->monto_bruto = $this->calculo['total_pagar_final'];
        $this->descuentos = 0; // otros descuentos; el de adelantos se calcula
        $this->descuento_adelantos = min($this->saldo_adelantos, round($this->monto_bruto * 0.5, 2));
        $this->monto_neto = max(0, $this->monto_bruto - $this->descuento_adelantos - $this->descuentos);

        // Generar observaciones automáticas
        $obs_base = sprintf(
            'Liquidación período %s a %s - %d días caídos + %.2f ton',
            Carbon::parse($this->fecha_inicio)->format('d/m/Y'),
            Carbon::parse($this->fecha_fin)->format('d/m/Y'),
            $this->calculo['cantidad_dias_caidos'],
            $this->calculo['total_peso_toneladas'] ?? 0
        );

        if ($this->descuento_adelantos > 0) {
            $obs_base .= sprintf(' | Adelantos: $%.2f', $this->descuento_adelantos);
        }

        $this->observaciones = $obs_base;

        $this->mostrar_liquidacion = true;
        $this->recibo_generado = false;
    }

    public function liquidarTodos()
    {
        $this->authorize('crear-liquidacion-pagos');
        $this->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $periodo = Carbon::parse($this->fecha_inicio)->format('d/m/Y').' a '.Carbon::parse($this->fecha_fin)->format('d/m/Y');

        try {
            $resultado = EmpleadoPagoService::liquidarTodos($this->fecha_inicio, $this->fecha_fin);

            if (empty($resultado['recibos'])) {
                session()->flash('error', 'No hay empleados activos para liquidar.');

                return;
            }

            $this->enviarRecibosMasivosPorEmail($resultado['recibos'], $periodo);

            $cantidad = count($resultado['recibos']);
            session()->flash('message', "Se generaron {$cantidad} recibos y se enviaron a contabilidad.");
            $this->dispatch('reciboGenerado');

        } catch (\Throwable $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'liquidar los pagos'));
        }
    }

    private function enviarRecibosMasivosPorEmail(array $recibosData, string $periodo): void
    {
        try {
            $generadoPor = auth()->user()->name ?? auth()->user()->email ?? 'Usuario';
            $fechaGeneracion = Carbon::now()->format('d/m/Y H:i');

            $body = 'Se generaron '.count($recibosData)." comprobantes de pago.\n";
            $body .= "Período: {$periodo}\n";

            Mail::raw($body, function ($message) use ($recibosData, $periodo, $generadoPor, $fechaGeneracion) {
                $message->to('contabilidad@rennova.com')
                    ->subject('Comprobantes de pago - Liquidación masiva ('.$periodo.')');

                foreach ($recibosData as $item) {
                    $pdf = app(ReciboService::class)->comprobantePdf($item['recibo'], $periodo, $generadoPor, $fechaGeneracion);
                    $message->attachData($pdf['output'], $pdf['filename'], ['mime' => 'application/pdf']);
                }
            });
        } catch (\Throwable $mailException) {
            session()->flash('error', 'Se generaron los recibos, pero no se pudo enviar el correo a contabilidad.');
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
        $bruto = (float) ($this->monto_bruto ?? 0);
        $this->descuento_adelantos = min((float) $this->saldo_adelantos, round($bruto * 0.5, 2));
        $this->monto_neto = max(0, round($bruto - $this->descuento_adelantos - (float) ($this->descuentos ?? 0), 2));
    }

    public function generarRecibo()
    {
        $this->authorize('crear-recibos');
        $this->validate([
            'monto_bruto' => 'required|numeric|min:0',
            'descuentos' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:150',
        ]);

        try {
            $recibo = EmpleadoPagoService::generarRecibo(
                $this->empleado_seleccionado->id_empleado,
                $this->monto_bruto,
                $this->descuentos ?? 0,
                $this->observaciones,
                $this->adelantos_pendientes ?? collect()
            );

            $this->recibo_generado = true;
            $this->recibo_generado_id = $recibo->id_recibo;

            $mensaje = 'Recibo #'.$recibo->id_recibo.' generado correctamente.';
            if ($this->descuento_adelantos > 0) {
                $restante = round($this->saldo_adelantos - $this->descuento_adelantos, 2);
                $mensaje .= ' Se descontaron $'.number_format($this->descuento_adelantos, 2, ',', '.').' de adelantos';
                $mensaje .= $restante > 0
                    ? '; quedan $'.number_format($restante, 2, ',', '.').' pendientes.'
                    : ' y quedaron saldados.';
            }

            $this->enviarReciboPorEmail($recibo);

            session()->flash('message', $mensaje);
            $this->dispatch('reciboGenerado');

        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'generar el recibo'));
        }
    }

    private function enviarReciboPorEmail(Recibo $recibo): void
    {
        try {
            $empleadoNombre = trim(($this->empleado_seleccionado->apellido ?? '').' '.($this->empleado_seleccionado->nombre ?? ''));
            $periodo = Carbon::parse($this->fecha_inicio)->format('d/m/Y').' a '.Carbon::parse($this->fecha_fin)->format('d/m/Y');

            $generadoPor = auth()->user()->name ?? auth()->user()->email ?? 'Usuario';
            $fechaGeneracion = Carbon::now()->format('d/m/Y H:i');

            $pdf = app(ReciboService::class)->comprobantePdf($recibo, $periodo, $generadoPor, $fechaGeneracion);
            $pdfOutput = $pdf['output'];
            $pdfFilename = $pdf['filename'];

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
    }

    public function nuevaLiquidacion()
    {
        $this->reset([
            'id_empleado',
            'calculo',
            'empleado_seleccionado',
            'adelantos_pendientes',
            'total_adelantos',
            'saldo_adelantos',
            'descuento_adelantos',
            'monto_bruto',
            'descuentos',
            'monto_neto',
            'observaciones',
            'mostrar_liquidacion',
            'recibo_generado',
            'recibo_generado_id',
        ]);
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.liquidacion-pagos');
    }
}
