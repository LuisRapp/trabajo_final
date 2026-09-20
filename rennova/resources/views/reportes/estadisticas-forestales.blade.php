@extends('layouts.app')

@section('content')
<div class="w-full py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
                <flux:icon.chart-bar class="size-6 text-pino" />
                Estadísticas Forestales
            </h1>
            <p class="mt-1 text-sm text-tinta-suave">Análisis de costos, ingresos y rentabilidad por lote</p>
        </div>
        <p class="text-xs text-tinta-suave">Rango actual: <span class="font-semibold text-tinta">{{ $rango_label ?? '' }}</span></p>
    </div>

    @if($lotes->isEmpty())
        <x-ui.alert variant="warning" class="mb-6">
            <span class="font-semibold text-tinta">No hay lotes activos.</span> Creá un lote primero para ver estadísticas.
        </x-ui.alert>
    @else
        <x-ui.card class="mb-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between p-4">
                <div>
                    <h2 class="text-sm font-semibold text-tinta">Reportes PDF</h2>
                    <p class="mt-1 text-xs text-tinta-suave">Generá reportes por lote o global con rango de fechas.</p>
                </div>
                <x-ui.button type="button" id="openReporteModal" icon="document-arrow-down">
                    Generar reporte
                </x-ui.button>
            </div>
        </x-ui.card>

        <div id="reporteModal"
            style="position:fixed; inset:0; z-index:9999; display:none; align-items:center; justify-content:center; background:rgba(28,25,23,0.55); padding:16px;">
            <div style="width:100%; max-width:640px; background:#ffffff; border:1px solid #d6d3cd; border-radius:12px; padding:20px; box-shadow:0 20px 40px rgba(28,25,23,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding-bottom:12px; border-bottom:1px solid #d6d3cd;">
                    <h3 style="font-size:14px; font-weight:700; color:#1c1917; margin:0;">Generar reporte PDF</h3>
                    <button type="button" id="closeReporteModal" style="background:transparent; border:none; font-size:16px; color:#57534e; cursor:pointer;">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <form id="reporteForm" method="GET" action="{{ route('reportes.estadisticas-forestales.pdf') }}" target="_blank" style="display:grid; gap:16px; margin-top:16px;">
                    <div style="border:1px solid #d6d3cd; border-radius:12px; padding:16px;">
                        <div style="margin-bottom:12px; font-size:12px; font-weight:700; color:#1c1917;">Configuración del reporte</div>
                        <div style="display:grid; gap:12px;">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:600; color:#57534e; margin-bottom:6px;">Lote (opcional)</label>
                                <input type="text" id="loteSearch" list="lotesList" placeholder="Buscar lote por nombre o ID"
                                    style="width:100%; border:1px solid #d6d3cd; border-radius:8px; padding:8px 10px; font-size:13px; color:#1c1917; background:#ffffff;" />
                                <datalist id="lotesList">
                                    <option value="Todos los lotes"></option>
                                    @foreach($lotes as $lote)
                                        <option value="Lote #{{ $lote->id_lote }} - {{ $lote->ubicacion ?? $lote->propietario ?? 'Sin nombre' }}" data-id="{{ $lote->id_lote }}"></option>
                                    @endforeach
                                </datalist>
                                <input type="hidden" name="id_lote" id="idLoteValue" />
                                <p style="margin-top:6px; font-size:11px; color:#57534e;">Si elegís un lote, se genera el reporte de ese lote. Si lo dejás vacío, es global.</p>
                                <p id="loteSearchStatus" style="margin-top:6px; font-size:11px; color:#991b1b; display:none;"></p>
                            </div>
                            <div style="display:grid; gap:12px; grid-template-columns: repeat(2, minmax(0, 1fr));">
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:600; color:#57534e; margin-bottom:6px;">Desde</label>
                                    <input type="date" id="fechaDesde" name="desde" style="width:100%; border:1px solid #d6d3cd; border-radius:8px; padding:8px 10px; font-size:13px; color:#1c1917; background:#ffffff;" />
                                </div>
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:600; color:#57534e; margin-bottom:6px;">Hasta</label>
                                    <input type="date" id="fechaHasta" name="hasta" style="width:100%; border:1px solid #d6d3cd; border-radius:8px; padding:8px 10px; font-size:13px; color:#1c1917; background:#ffffff;" />
                                </div>
                            </div>
                            <p id="dateRangeStatus" style="margin-top:4px; font-size:11px; color:#991b1b; display:none;"></p>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                        <x-ui.button type="button" id="cancelReporteModal" variant="secondary" size="sm">
                            Cancelar
                        </x-ui.button>
                        <x-ui.button type="submit" size="sm" icon="document-arrow-down">
                            Generar reporte
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
            <x-ui.card class="p-4">
                <p class="text-xs font-medium text-tinta-suave">Precio Promedio Venta</p>
                <p class="mt-2 text-lg font-bold text-tinta">${{ number_format($estadisticas_globales['precio_promedio'], 2) }}/tn</p>
            </x-ui.card>
            <x-ui.card class="p-4">
                <p class="text-xs font-medium text-tinta-suave">Costo Promedio</p>
                <p class="mt-2 text-lg font-bold text-tinta">${{ number_format($estadisticas_globales['costo_promedio'], 2) }}/tn</p>
            </x-ui.card>
            <x-ui.card class="p-4">
                <p class="text-xs font-medium text-tinta-suave">Punto de Equilibrio</p>
                <p class="mt-2 text-lg font-bold text-tinta">${{ number_format($estadisticas_globales['punto_equilibrio'], 2) }}/tn</p>
            </x-ui.card>
            <x-ui.card class="p-4">
                <p class="text-xs font-medium text-tinta-suave">Rentabilidad Promedio</p>
                <p class="mt-2 text-lg font-bold {{ $estadisticas_globales['rentabilidad_promedio'] >= 0 ? 'text-musgo' : 'text-tierra' }}">
                    ${{ number_format($estadisticas_globales['rentabilidad_promedio'], 2) }}/tn
                </p>
            </x-ui.card>
        </div>

        <!-- GRÁFICO 1: Producción vs Punto de Equilibrio -->
        <div class="mb-6">
            <x-ui.card>
                <div class="border-b border-arena px-4 py-3">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <h2 class="text-sm font-semibold text-tinta">Producción vs Punto de Equilibrio ({{ $rango_label ?? '' }})</h2>
                        <form method="GET" action="{{ route('reportes.estadisticas-forestales') }}" class="flex flex-nowrap items-center gap-2">
                            <input type="date" name="desde" value="{{ $filtro_desde ?? '' }}" max="{{ now()->toDateString() }}"
                                class="form-input h-8 w-32 min-w-[8rem] px-2 text-xs" />
                            <span class="text-xs text-arena-oscura">—</span>
                            <input type="date" name="hasta" value="{{ $filtro_hasta ?? '' }}" max="{{ now()->toDateString() }}"
                                class="form-input h-8 w-32 min-w-[8rem] px-2 text-xs" />
                            <x-ui.button type="submit" size="sm">
                                Aplicar
                            </x-ui.button>
                        </form>
                    </div>
                </div>
                <div class="p-4">
                    <div id="chartProduccion"></div>
                </div>
            </x-ui.card>
        </div>

        <!-- GRÁFICOS 2 y 3 -->
        <div class="grid grid-cols-1 gap-4 mb-6 lg:grid-cols-2">
            <x-ui.card>
                <div class="border-b border-arena px-4 py-3">
                    <h2 class="text-sm font-semibold text-tinta">Distribución de Costos</h2>
                </div>
                <div class="p-4">
                    <div id="chartDistribucion"></div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="border-b border-arena px-4 py-3">
                    <h2 class="text-sm font-semibold text-tinta">Evolución Costo por Tonelada ({{ $rango_label ?? '' }})</h2>
                </div>
                <div class="p-4">
                    <div id="chartEvolucion"></div>
                </div>
            </x-ui.card>
        </div>

        <!-- TABLA: Detalle por Lote -->
        <x-ui.card>
            <div class="border-b border-arena px-4 py-3">
                <h2 class="text-sm font-semibold text-tinta">Detalle por Lote</h2>
            </div>
            <x-ui.table-container>
                <table class="data-table">
                    <colgroup>
                        <col style="width: 25%;">
                        <col style="width: 10%;">
                        <col style="width: 12%;">
                        <col style="width: 12%;">
                        <col style="width: 12%;">
                        <col style="width: 12%;">
                        <col style="width: 17%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Nombre del Lote</th>
                            <th>Hectáreas</th>
                            <th>Precio Promedio</th>
                            <th>Costo Promedio</th>
                            <th>Punto Equilibrio</th>
                            <th>Rentabilidad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lotes_estadisticas as $stat)
                            <tr>
                                <td class="font-semibold text-tinta break-words">
                                    {{ $stat['nombre'] ?? 'Sin nombre' }}
                                </td>
                                <td class="text-tinta-suave">{{ number_format($stat['hectareas'], 2) }} ha</td>
                                <td>
                                    <x-ui.badge variant="success">${{ number_format($stat['precio_promedio'], 2) }}/tn</x-ui.badge>
                                </td>
                                <td>
                                    <x-ui.badge variant="warning">${{ number_format($stat['costo_promedio'], 2) }}/tn</x-ui.badge>
                                </td>
                                <td>
                                    <x-ui.badge variant="info">${{ number_format($stat['punto_equilibrio'], 2) }}/tn</x-ui.badge>
                                </td>
                                <td>
                                    <x-ui.badge variant="{{ $stat['rentabilidad'] >= 0 ? 'success' : 'danger' }}">
                                        ${{ number_format($stat['rentabilidad'], 2) }}/tn
                                    </x-ui.badge>
                                </td>
                                <td>
                                    <x-ui.badge variant="{{ $stat['rentabilidad'] >= 0 ? 'success' : 'danger' }}">
                                        {{ $stat['rentabilidad'] >= 0 ? 'Rentable' : 'No Rentable' }}
                                    </x-ui.badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-ui.table-container>
        </x-ui.card>

        <!-- Información y Recomendaciones -->
        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <x-ui.card class="p-4">
                <h3 class="text-sm font-semibold text-tinta">Cómo interpretar los datos</h3>
                <ul class="mt-3 space-y-2 text-sm text-tinta-suave">
                    <li><span class="font-semibold text-tinta">Precio Promedio:</span> Precio unitario esperado por tonelada.</li>
                    <li><span class="font-semibold text-tinta">Costo Promedio:</span> Costo operacional por tonelada (insumos + maquinaria + mano de obra).</li>
                    <li><span class="font-semibold text-tinta">Punto de Equilibrio:</span> Precio mínimo para no perder dinero.</li>
                    <li><span class="font-semibold text-tinta">Rentabilidad:</span> Diferencia entre ingreso y costo (Precio - Costo).</li>
                </ul>
            </x-ui.card>
            <x-ui.card class="p-4">
                <h3 class="text-sm font-semibold text-tinta">Recomendaciones</h3>
                <ul class="mt-3 space-y-2 text-sm text-tinta-suave">
                    <li>Si rentabilidad &gt; 0: El lote es rentable.</li>
                    <li>Si rentabilidad ≈ 0: Estar atento a variaciones de costo.</li>
                    <li>Si rentabilidad &lt; 0: El lote está perdiendo dinero.</li>
                    <li>Revisar constantemente para optimizar operaciones.</li>
                </ul>
            </x-ui.card>
        </div>

        <x-ui.card class="mt-6 p-4 text-sm text-tinta-suave">
            <span class="font-semibold text-tinta">Resumen:</span> Analizando {{ $estadisticas_globales['total_lotes'] }} lote(s) activo(s)
            con rentabilidad promedio de <span class="font-semibold text-tinta">${{ number_format($estadisticas_globales['rentabilidad_promedio'], 2) }}/tn</span>.
        </x-ui.card>
    @endif
</div>

<!-- ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>

<script>
    const openReporteModal = document.getElementById('openReporteModal');
    const closeReporteModal = document.getElementById('closeReporteModal');
    const cancelReporteModal = document.getElementById('cancelReporteModal');
    const reporteModal = document.getElementById('reporteModal');

    if (openReporteModal && closeReporteModal && cancelReporteModal && reporteModal) {
        const closeModal = () => {
            reporteModal.style.display = 'none';
        };

        openReporteModal.addEventListener('click', () => {
            reporteModal.style.display = 'flex';
        });

        closeReporteModal.addEventListener('click', closeModal);
        cancelReporteModal.addEventListener('click', closeModal);

        reporteModal.addEventListener('click', (event) => {
            if (event.target === reporteModal) {
                closeModal();
            }
        });
    }

    const loteSearch = document.getElementById('loteSearch');
    const idLoteValue = document.getElementById('idLoteValue');
    const lotesList = document.getElementById('lotesList');
    const loteSearchStatus = document.getElementById('loteSearchStatus');
    const reporteForm = document.getElementById('reporteForm');
    const fechaDesde = document.getElementById('fechaDesde');
    const fechaHasta = document.getElementById('fechaHasta');
    const dateRangeStatus = document.getElementById('dateRangeStatus');

    const getTodayIso = () => new Date().toISOString().split('T')[0];
    const applyMaxDate = () => {
        const today = getTodayIso();
        if (fechaDesde) fechaDesde.max = today;
        if (fechaHasta) fechaHasta.max = today;
    };

    applyMaxDate();

    if (loteSearch && idLoteValue && lotesList && loteSearchStatus) {
        const setIdFromValue = () => {
            const value = loteSearch.value.trim();
            if (value === '' || value.toLowerCase() === 'todos los lotes') {
                idLoteValue.value = '';
                loteSearchStatus.style.display = 'none';
                return;
            }

            const option = Array.from(lotesList.options).find(opt => opt.value === value);
            if (option && option.dataset.id) {
                idLoteValue.value = option.dataset.id;
                loteSearchStatus.style.display = 'none';
            } else {
                idLoteValue.value = '';
                loteSearchStatus.textContent = 'No se encontró el lote seleccionado.';
                loteSearchStatus.style.display = 'block';
            }
        };

        loteSearch.addEventListener('change', setIdFromValue);
        loteSearch.addEventListener('blur', setIdFromValue);
    }

    if (reporteForm && fechaDesde && fechaHasta && dateRangeStatus) {
        const validateDates = () => {
            const today = getTodayIso();
            const desde = fechaDesde.value;
            const hasta = fechaHasta.value;

            let message = '';
            if (desde && desde > today) {
                message = 'La fecha "Desde" no puede ser futura.';
            } else if (hasta && hasta > today) {
                message = 'La fecha "Hasta" no puede ser futura.';
            }

            if (message) {
                dateRangeStatus.textContent = message;
                dateRangeStatus.style.display = 'block';
                return false;
            }

            dateRangeStatus.textContent = '';
            dateRangeStatus.style.display = 'none';
            return true;
        };

        fechaDesde.addEventListener('change', validateDates);
        fechaHasta.addEventListener('change', validateDates);

        reporteForm.addEventListener('submit', (event) => {
            applyMaxDate();
            if (!validateDates()) {
                event.preventDefault();
                return;
            }

            if (reporteModal) {
                reporteModal.style.display = 'none';
            }
        });
    }

    const puntoEquilibrio = {{ $estadisticas_globales['punto_equilibrio'] }};
    const colorPino = '#2f5233';
    const colorMusgo = '#3f6212';
    const colorResina = '#b45309';
    const colorTierra = '#991b1b';
    const colorCorteza = '#5d4e37';

    // ========== GRÁFICO 1: Producción vs Punto de Equilibrio ==========
    const optionsProduccion = {
        chart: {
            type: 'bar',
            height: 400,
            toolbar: { show: true, tools: { download: true, selection: true, zoom: true, zoomin: true, zoomout: true, pan: true, reset: true } }
        },
        colors: [colorMusgo],
        plotOptions: {
            bar: {
                columnWidth: '70%',
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: (val) => `${val.toFixed(1)}tn`,
            offsetY: -20,
            style: { fontSize: '11px', fontWeight: 600 }
        },
        xaxis: {
            categories: @json($fechas_30_dias),
            title: { text: 'Fechas ({{ $rango_label ?? '' }})' }
        },
        yaxis: {
            title: { text: 'Toneladas' },
            min: 0
        },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        fill: { opacity: 0.8 },
        tooltip: {
            y: {
                formatter: (val) => `${val.toFixed(1)} tn`
            }
        },
        annotations: {
            yaxis: [
                {
                    y: puntoEquilibrio,
                    borderColor: colorTierra,
                    label: {
                        borderColor: colorTierra,
                        style: {
                            color: '#ffffff',
                            background: colorTierra,
                            fontSize: '12px',
                            fontWeight: 600
                        },
                        text: `Punto de Equilibrio: ${puntoEquilibrio.toFixed(2)} tn`,
                        position: 'right'
                    }
                }
            ]
        }
    };

    const seriesProduccion = [
        {
            name: 'Producción (tn)',
            data: @json($produccion_30_dias)
        }
    ];

    const chartProduccion = new ApexCharts(document.querySelector("#chartProduccion"),
        { ...optionsProduccion, series: seriesProduccion });
    chartProduccion.render();

    // ========== GRÁFICO 2: Distribución de Costos (Donut) ==========
    const distribucionData = @json($distribucion_costos);
    const categoriasCostos = distribucionData.map(d => d.name);
    const valoresCostos = distribucionData.map(d => parseFloat(d.value));

    const optionsDistribucion = {
        chart: {
            type: 'donut',
            height: 350
        },
        colors: [colorResina, colorTierra, colorPino],
        labels: categoriasCostos,
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        name: {
                            fontSize: '14px',
                            fontWeight: 600
                        },
                        value: {
                            fontSize: '16px',
                            fontWeight: 600,
                            formatter: (val) => `$${parseFloat(val).toFixed(2)}`
                        },
                        total: {
                            show: true,
                            label: 'Costo Total',
                            fontSize: '14px',
                            formatter: function() {
                                return '$' + valoresCostos.reduce((a, b) => a + b, 0).toFixed(2);
                            }
                        }
                    }
                }
            }
        },
        tooltip: {
            y: {
                formatter: (val) => `$${val.toFixed(2)}`
            }
        },
        legend: {
            position: 'bottom',
            fontSize: '13px'
        }
    };

    const chartDistribucion = new ApexCharts(document.querySelector("#chartDistribucion"),
        { ...optionsDistribucion, series: valoresCostos });
    chartDistribucion.render();

    // ========== GRÁFICO 3: Evolución Costo por Tonelada (Área) ==========
    const optionsEvolucion = {
        chart: {
            type: 'area',
            height: 350,
            toolbar: { show: true },
            zoom: { enabled: true }
        },
        colors: [colorPino],
        dataLabels: { enabled: false },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        xaxis: {
            categories: @json($fechas_6_meses),
            title: { text: 'Período ({{ $rango_label ?? '' }})' }
        },
        yaxis: {
            title: { text: 'Costo por Tonelada ($/tn)' }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.1,
                stops: [0, 100]
            }
        },
        tooltip: {
            y: {
                formatter: (val) => `$${val.toFixed(2)}/tn`
            }
        },
        legend: {
            position: 'top'
        }
    };

    const seriesEvolucion = [
        {
            name: 'Costo Promedio',
            data: @json($evolucion_6_meses)
        }
    ];

    const chartEvolucion = new ApexCharts(document.querySelector("#chartEvolucion"),
        { ...optionsEvolucion, series: seriesEvolucion });
    chartEvolucion.render();
</script>

@endsection
