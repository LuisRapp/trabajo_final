<?php

namespace App\Http\Livewire;

use App\Models\Lote;
use App\Services\ClimaDecisionService;
use Carbon\Carbon;
use Livewire\Component;

class SelectorLote extends Component
{
    public $loteSeleccionado = null;

    public $lotes = [];

    public $pronosticoData = null;

    protected ClimaDecisionService $climaService;

    public function boot(ClimaDecisionService $climaService): void
    {
        // En boot(), no en mount(): mount solo corre en el primer render y
        // cualquier request posterior (cambio de lote) encontraba la propiedad nula.
        $this->climaService = $climaService;
    }

    public function mount(): void
    {
        // Cargar solo lotes activos
        $this->lotes = Lote::whereIn('estado', ['activo', 'en_proceso'])
            ->orderByDesc('created_at')
            ->get()
            ->toArray();

        // Cargar el primer lote por defecto
        if (count($this->lotes) > 0) {
            $this->loteSeleccionado = $this->lotes[0]['id_lote'];
            $this->actualizarPronostico();
        }
    }

    public function updatedLoteSeleccionado()
    {
        $this->actualizarPronostico();
    }

    public function actualizarPronostico()
    {
        if (! $this->loteSeleccionado) {
            $this->pronosticoData = null;

            return;
        }

        $lote = Lote::where('id_lote', $this->loteSeleccionado)->first();

        if (! $lote) {
            $this->pronosticoData = null;

            return;
        }

        // Verificar que tiene coordenadas
        if (! $lote->latitud || ! $lote->longitud) {
            $this->pronosticoData = null;

            return;
        }

        // Obtener pronóstico (mock si es lote demo con lluvia)
        if ($this->esLoteDemoLluvia($lote)) {
            $climaData = $this->mockPronosticoLluvia($lote);
        } else {
            $climaData = $this->climaService->analizarYRecomendar($lote);
        }

        if ($climaData && ! isset($climaData['error'])) {
            // Mapear pronostico (dias_detalle) al formato del componente
            $pronosticoFormateado = [];
            $diasDetalle = $climaData['pronostico'] ?? $climaData['dias_detalle'] ?? [];

            if (is_array($diasDetalle)) {
                foreach ($diasDetalle as $dia) {
                    $fechaDia = isset($dia['fecha']) ? Carbon::parse($dia['fecha']) : null;
                    // El servicio marca el fin de semana como INACTIVO, pero no es un
                    // dia perdido por clima: es no laboral. No debe contar como tal.
                    $noLaboral = $fechaDia?->isWeekend() ?? false;
                    $estado = $dia['estado'] ?? 'OPERATIVO';
                    $inactivoPorClima = ! $noLaboral && $estado === 'INACTIVO';

                    $icono = match (true) {
                        $noLaboral => 'calendar',
                        $estado === 'OPERATIVO' => 'sun',
                        $estado === 'OPERATIVO_CONDICIONAL' => 'cloud',
                        default => isset($dia['razon']) && str_contains($dia['razon'], 'Lluvia') ? 'storm' : 'cloud',
                    };

                    // El nombre del dia se calcula desde la fecha con locale es:
                    // el servicio puede devolver el dato cacheado en ingles.
                    $diaSemana = $fechaDia
                        ? ucfirst(rtrim($fechaDia->copy()->locale('es')->isoFormat('ddd'), '.'))
                        : ucfirst(mb_substr((string) ($dia['dia_semana'] ?? ''), 0, 3, 'UTF-8'));

                    $pronosticoFormateado[] = [
                        'fecha' => $fechaDia?->toDateString(),
                        'label' => $diaSemana.' ('.$dia['fecha_str'].')',
                        'estado' => match (true) {
                            $noLaboral => 'NO LABORAL',
                            $estado === 'INACTIVO' => 'NO OPERATIVO',
                            $estado === 'OPERATIVO_CONDICIONAL' => 'OPERATIVO COND.',
                            default => 'OPERATIVO',
                        },
                        'icono' => $icono,
                        'inactivo' => $inactivoPorClima,
                        'no_laboral' => $noLaboral,
                        'suelo' => $dia['razon'] ?? null,
                    ];
                }
            }

            $diasPerdidos = count(array_filter(
                $pronosticoFormateado,
                fn (array $dia): bool => $dia['inactivo']
            ));

            $datos = $climaData['datos_calculados'] ?? [];
            $estrategia = (string) ($climaData['estrategia'] ?? '');
            $accionRecomendada = $climaData['accion_recomendada'] ?? null;
            $nivelUrgencia = $climaData['nivel_urgencia'] ?? 'BAJA';

            $diasCriticos = array_values(array_map(
                fn (array $dia): string => $dia['label'],
                array_filter($pronosticoFormateado, fn (array $dia): bool => $dia['inactivo'])
            ));

            $diasOperativos = count(array_filter(
                $pronosticoFormateado,
                fn (array $dia): bool => ! $dia['inactivo'] && ! $dia['no_laboral']
            ));

            $primerOperativo = null;
            $hoy = Carbon::today()->toDateString();
            foreach ($pronosticoFormateado as $dia) {
                if (! $dia['inactivo'] && ! $dia['no_laboral'] && ($dia['fecha'] ?? '') >= $hoy) {
                    $primerOperativo = $dia['label'];
                    break;
                }
            }

            $deficit = round((float) ($datos['volumen_riesgo'] ?? 0), 0);
            $porcentaje = round((float) ($datos['aumento_necesario_pct'] ?? 0));
            $nuevaMeta = isset($datos['nueva_meta_diaria']) ? (float) $datos['nueva_meta_diaria'] : null;
            $residual = round((float) ($datos['deficit_residual'] ?? 0), 0);
            $recuperable = round((float) ($datos['volumen_recuperable'] ?? 0), 0);
            $metaFuente = $datos['meta_fuente'] ?? 'sin_datos';
            $mantenimiento = $datos['maquinarias_mantenimiento'] ?? [];

            $tipoAlerta = match (true) {
                $estrategia === 'SIN_HISTORIAL' => 'SIN HISTORIAL',
                $nivelUrgencia === 'CRITICA' && $accionRecomendada === 'MANTENIMIENTO_PREVENTIVO' => 'MANTENIMIENTO',
                $nivelUrgencia === 'CRITICA' => 'REPROGRAMAR',
                $nivelUrgencia === 'ALTA' && $residual > 0.01 => 'PRIORIZAR',
                in_array($nivelUrgencia, ['ALTA', 'MEDIA'], true) => 'COMPENSAR',
                default => 'NORMAL',
            };

            $corto = fn (string $label): string => preg_replace('/ \((\d{2}\/\d{2})\/\d{4}\)/', ' $1', $label);

            $diasTexto = $diasPerdidos === 1 ? '1 día' : $diasPerdidos.' días';
            $operativosTexto = $diasOperativos === 1 ? '1 día operativo' : $diasOperativos.' días operativos';
            $listaDias = $diasCriticos !== [] ? ' ('.implode(', ', array_map($corto, $diasCriticos)).')' : '';
            $listaMantenimiento = implode(', ', array_map(
                fn (array $maq): string => $maq['nombre'].' ('.$maq['motivo'].')',
                $mantenimiento
            ));
            $textoMantenimiento = $mantenimiento !== []
                ? ' Aprovechá el día no operativo para mantenimiento: '.$listaMantenimiento.'.'
                : '';

            $recomendacion = match ($tipoAlerta) {
                'NORMAL' => 'No se pronostican lluvias en la ventana. Mantené el ritmo actual.',
                'SIN HISTORIAL' => 'Hay '.$diasTexto.' no operativos'.$listaDias.', pero el lote no tiene producción histórica para estimar el tonelaje en riesgo.',
                'MANTENIMIENTO' => 'No quedan días operativos en la ventana. Aprovechá para mantenimiento preventivo: '.$listaMantenimiento.'.',
                'REPROGRAMAR' => 'No hay días operativos en la ventana. Reprogramá las tareas y revisá el pronóstico en 24 h.'.$textoMantenimiento,
                'PRIORIZAR' => 'Se pierden '.$diasTexto.' no operativos'.$listaDias.'. Con +25% en '.$operativosTexto
                    .' recuperás '.number_format($recuperable, 0, ',', '.').' tn; quedan '.number_format($residual, 0, ',', '.')
                    .' tn sin cubrir. Evaluá horas extras o priorizá cargas de mayor valor.'.$textoMantenimiento,
                default => 'Se pierden '.$diasTexto.' no operativos'.$listaDias.'. Sumá '.$porcentaje.'%'
                    .($nuevaMeta ? ' ('.number_format($nuevaMeta, 0, ',', '.').' tn/día)' : '')
                    .' desde '.($primerOperativo ? $corto($primerOperativo) : 'el próximo día operativo')
                    .' para recuperar '.number_format($deficit, 0, ',', '.').' tn.'.$textoMantenimiento,
            };

            $this->pronosticoData = [
                'alerta' => $tipoAlerta,
                'pronostico' => $pronosticoFormateado,
                'analisisImpacto' => [
                    'diasPerdidos' => $diasPerdidos,
                    'deficitTn' => $deficit,
                    'accionPorcentaje' => $porcentaje,
                    'diasOperativos' => $diasOperativos,
                    'primerOperativo' => $primerOperativo,
                    'diasCriticos' => $diasCriticos,
                    'nuevaMetaDiaria' => $nuevaMeta,
                    'residual' => $residual,
                    'recuperable' => $recuperable,
                    'mantenimiento' => $mantenimiento,
                    'metaFuente' => $metaFuente,
                ],
                'loteNombre' => $lote->nombre ?? $lote->propietario ?? ('Lote #'.$lote->id_lote),
                'recomendacionDetallada' => $recomendacion,
                'generadoEn' => isset($climaData['generado_en']) ? Carbon::parse($climaData['generado_en'])->format('H:i') : Carbon::now()->format('H:i'),
            ];
        } else {
            $this->pronosticoData = null;
        }
    }

    public function render()
    {
        return view('livewire.selector-lote');
    }

    private function esLoteDemoLluvia(Lote $lote): bool
    {
        if (! app()->environment(['local', 'testing'])) {
            return false;
        }
        $nombre = strtolower((string) ($lote->nombre ?? $lote->propietario ?? ''));

        return str_contains($nombre, 'lluvia');
    }

    private function mockPronosticoLluvia(Lote $lote): array
    {
        $diasDetalle = [];
        foreach (range(0, 6) as $i) {
            $fecha = Carbon::now()->addDays($i);
            $estado = 'OPERATIVO';
            $razon = null;

            if ($i === 1) {
                $estado = 'INACTIVO';
                $razon = 'Lluvia diurna > 5mm (06-18)';
            }
            if ($i === 2) {
                $estado = 'INACTIVO';
                $razon = 'Saturación alta + nubosidad + poco viento';
            }
            if ($i === 4) {
                $estado = 'OPERATIVO_CONDICIONAL';
                $razon = 'Lluvia nocturna';
            }

            $diasDetalle[] = [
                'fecha' => $fecha,
                'fecha_str' => $fecha->format('d/m/Y'),
                'dia_semana' => $fecha->isoFormat('dddd'),
                'estado' => $estado,
                'razon' => $razon,
            ];
        }

        return [
            'success' => true,
            'nivel_urgencia' => 'MEDIA',
            'recomendacion' => 'Se detecta lluvia en la ventana operativa. Ajustar planificación y evaluar anticipación.',
            'dias_detalle' => $diasDetalle,
            'datos_calculados' => [
                'volumen_riesgo' => 120,
                'aumento_necesario_pct' => 18,
            ],
        ];
    }
}
