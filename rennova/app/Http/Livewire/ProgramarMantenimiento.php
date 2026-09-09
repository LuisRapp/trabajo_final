<?php

namespace App\Http\Livewire;

use App\Models\NotificacionSistema;
use App\Services\MantenimientoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProgramarMantenimiento extends Component
{
    public $notificacionId;

    public $notificacion;

    public $mantenimiento;

    public $fechaProgramada;

    public $fechaMinima;

    public $fechaMaxima;

    public $id_maquinaria;

    public $id_tipo_mantenimiento;

    public $maquinarias;

    public $tipos;

    public function mount($notificacionId = null)
    {
        $this->notificacionId = $notificacionId;
        $servicio = app(MantenimientoService::class);

        // Sin notificacion: alta de una orden de mantenimiento
        if (! $notificacionId) {
            $this->maquinarias = $servicio->obtenerMaquinariasActivas();
            $this->tipos = $servicio->obtenerTiposMantenimiento();
            $this->fechaProgramada = now()->addDay()->toDateString();

            return;
        }

        // Con notificacion: confirmar fecha de la orden ya generada por la notificacion
        $this->notificacion = NotificacionSistema::with([
            'mantenimiento.maquinaria',
            'mantenimiento.tipoMantenimiento',
        ])
            ->where('id', $notificacionId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (! $this->notificacion->mantenimiento_id || ! $this->notificacion->mantenimiento) {
            session()->flash('error', 'Esta notificacion no tiene un mantenimiento asociado.');

            return redirect()->route('notificaciones.index');
        }

        $this->mantenimiento = $this->notificacion->mantenimiento;

        $fechaNotificacion = $this->notificacion->created_at;
        $this->fechaMinima = $fechaNotificacion->format('Y-m-d');
        $this->fechaMaxima = $fechaNotificacion->copy()->addDays(7)->format('Y-m-d');

        $manana = now()->addDay();
        $fechaMin = Carbon::parse($this->fechaMinima);
        $this->fechaProgramada = $manana->gte($fechaMin) ? $manana->format('Y-m-d') : $this->fechaMinima;
    }

    protected function rules()
    {
        if ($this->notificacionId) {
            return [
                'fechaProgramada' => [
                    'required',
                    'date',
                    'after_or_equal:'.$this->fechaMinima,
                    'before_or_equal:'.$this->fechaMaxima,
                ],
            ];
        }

        return [
            'id_maquinaria' => 'required|exists:maquinarias,id_maquinaria',
            'id_tipo_mantenimiento' => 'required|exists:tipo_mantenimientos,id_tipo_mantenimiento',
            'fechaProgramada' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $mensaje = app(MantenimientoService::class)->validarFechaProgramadaNueva($value);

                    if ($mensaje) {
                        $fail($mensaje);
                    }
                },
            ],
        ];
    }

    protected $messages = [
        'id_maquinaria.required' => 'Debe seleccionar una maquinaria.',
        'id_tipo_mantenimiento.required' => 'Debe seleccionar un tipo de mantenimiento.',
        'fechaProgramada.required' => 'La fecha programada es obligatoria.',
        'fechaProgramada.date' => 'La fecha programada debe ser una fecha valida.',
        'fechaProgramada.after_or_equal' => 'La fecha programada debe estar dentro del rango permitido.',
        'fechaProgramada.before_or_equal' => 'La fecha programada debe estar dentro del rango permitido.',
    ];

    public function programarOrden()
    {
        $this->validate();

        $resultado = app(MantenimientoService::class)->guardarMantenimiento([
            'id_maquinaria' => $this->id_maquinaria,
            'id_tipo_mantenimiento' => $this->id_tipo_mantenimiento,
            'fecha_inicio' => $this->fechaProgramada,
            'fecha_programada' => $this->fechaProgramada,
            'estado' => 'programado',
        ]);

        if (! $resultado['success']) {
            session()->flash('error', $resultado['message']);

            return;
        }

        session()->flash('message', 'Orden de '.$resultado['tipoNombre'].' programada correctamente para '.$resultado['maquinaNombre'].'.');

        return redirect()->route('mantenimientos.index');
    }

    public function guardarFecha()
    {
        $this->validate();

        $resultado = app(MantenimientoService::class)->guardarMantenimiento(
            [
                'id_maquinaria' => $this->mantenimiento->id_maquinaria,
                'id_tipo_mantenimiento' => $this->mantenimiento->id_tipo_mantenimiento,
                'fecha_inicio' => $this->fechaProgramada,
                'fecha_programada' => $this->fechaProgramada,
                'estado' => 'programado',
            ],
            $this->mantenimiento->id_mantenimiento,
            Auth::id()
        );

        if (! $resultado['success']) {
            session()->flash('error', $resultado['message']);

            return;
        }

        session()->flash('message', 'Mantenimiento programado exitosamente para el '.Carbon::parse($this->fechaProgramada)->format('d/m/Y'));

        return redirect()->route('mantenimientos.index');
    }

    public function render()
    {
        return view('livewire.programar-mantenimiento');
    }
}
