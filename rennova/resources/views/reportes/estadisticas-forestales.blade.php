@extends('layouts.app')

@section('content')
<div class="min-h-screen !bg-slate-50 px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold !text-slate-900">Estadísticas Forestales</h1>
            <p class="mt-1 text-sm !text-slate-500">Análisis de costos, ingresos y rentabilidad por lote</p>
        </div>
        <p class="text-xs !text-slate-500">Rango actual: <span class="font-semibold !text-slate-700">{{ $rango_label ?? '' }}</span></p>
    </div>

    @if($lotes->isEmpty())
        <div class="rounded-lg border !border-slate-200 !bg-white p-4 !text-slate-700 shadow-sm">
            <p class="text-sm"><span class="font-semibold !text-slate-900">No hay lotes activos.</span> Crea un lote primero para ver estadísticas.</p>
        </div>
    @else
        <div class="mb-6 rounded-lg border !border-slate-200 !bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-sm font-semibold !text-slate-900">Reportes PDF</h2>
                    <p class="mt-1 text-xs !text-slate-500">Genera reportes por lote o global con rango de fechas.</p>
                </div>
                <button type="button" id="openReporteModal" class="inline-flex items-center justify-center rounded-md !bg-slate-900 px-4 py-2.5 text-sm font-semibold !text-white shadow-sm hover:!bg-slate-800 whitespace-nowrap flex-shrink-0" style="display:inline-flex; align-items:center; justify-content:center; background:#0f172a; color:#fff; border:1px solid #0f172a; border-radius:8px; padding:10px 16px; font-size:13px; font-weight:700; line-height:1; white-space:nowrap; min-height:36px;">
                    Generar reporte
                </button>
            </div>
        </div>

        <div id="reporteModal"
            style="position:fixed; inset:0; z-index:9999; display:none; align-items:center; justify-content:center; background:rgba(15,23,42,0.55); padding:16px;">
            <div style="width:100%; max-width:640px; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; box-shadow:0 20px 40px rgba(15,23,42,0.25);">
                <div style="display:flex; align-items:center; justify-content:space-between; padding-bottom:12px; border-bottom:1px solid #e2e8f0;">
                    <h3 style="font-size:14px; font-weight:700; color:#0f172a; margin:0;">Generar reporte PDF</h3>
                    <button type="button" id="closeReporteModal" style="background:transparent; border:none; font-size:16px; color:#64748b; cursor:pointer;">✕</button>
                </div>

                <form id="reporteForm" method="GET" action="{{ route('reportes.estadisticas-forestales.pdf') }}" target="_blank" style="display:grid; gap:16px; margin-top:16px;">
                    <div style="border:1px solid #e2e8f0; border-radius:12px; padding:16px;">
                        <div style="margin-bottom:12px; font-size:12px; font-weight:700; color:#334155;">Configuración del reporte</div>
                        <div style="display:grid; gap:12px;">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px;">Lote (opcional)</label>
                                <input type="text" id="loteSearch" list="lotesList" placeholder="Buscar lote por nombre o ID"
                                    style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:8px 10px; font-size:13px; color:#0f172a; background:#fff;" />
                                <datalist id="lotesList">
                                    <option value="Todos los lotes"></option>
                                    @foreach($lotes as $lote)
                                        <option value="Lote #{{ $lote->id_lote }} - {{ $lote->ubicacion ?? $lote->propietario ?? 'Sin nombre' }}" data-id="{{ $lote->id_lote }}"></option>
                                    @endforeach
                                </datalist>
                                <input type="hidden" name="id_lote" id="idLoteValue" />
                                <p style="margin-top:6px; font-size:11px; color:#64748b;">Si elegís un lote, se genera el reporte de ese lote. Si lo dejás vacío, es global.</p>
                                <p id="loteSearchStatus" style="margin-top:6px; font-size:11px; color:#dc2626; display:none;"></p>
                            </div>
                            <div style="display:grid; gap:12px; grid-template-columns: repeat(2, minmax(0, 1fr));">
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px;">Desde</label>
                                    <input type="date" id="fechaDesde" name="desde" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:8px 10px; font-size:13px; color:#0f172a; background:#fff;" />
                                </div>
                                <div>
                                    <label style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px;">Hasta</label>
                                    <input type="date" id="fechaHasta" name="hasta" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:8px 10px; font-size:13px; color:#0f172a; background:#fff;" />
                                </div>
                            </div>
                            <p id="dateRangeStatus" style="margin-top:4px; font-size:11px; color:#dc2626; display:none;"></p>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                        <button type="button" id="cancelReporteModal" style="border:1px solid #cbd5e1; background:#fff; color:#334155; border-radius:8px; padding:8px 12px; font-size:12px; font-weight:600; cursor:pointer;">Cancelar</button>
                        <button type="submit" style="background:#0f172a; color:#fff; border:none; border-radius:8px; padding:8px 12px; font-size:12px; font-weight:700; cursor:pointer;">Generar reporte</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="rounded-lg !bg-white p-4 shadow-sm">
                <p class="text-xs font-medium !text-slate-500">Precio Promedio Venta</p>
                <div class="mt-2 text-lg font-semibold !text-slate-900">${{ number_format($estadisticas_globales['precio_promedio'], 2) }}/tn</div>
            </div>
            <div class="rounded-lg !bg-white p-4 shadow-sm">
                <p class="text-xs font-medium !text-slate-500">Costo Promedio</p>
                <div class="mt-2 text-lg font-semibold !text-slate-900">${{ number_format($estadisticas_globales['costo_promedio'], 2) }}/tn</div>
            </div>
            <div class="rounded-lg !bg-white p-4 shadow-sm">
                <p class="text-xs font-medium !text-slate-500">Punto de Equilibrio</p>
                <div class="mt-2 text-lg font-semibold !text-slate-900">${{ number_format($estadisticas_globales['punto_equilibrio'], 2) }}/tn</div>
            </div>
            <div class="rounded-lg !bg-white p-4 shadow-sm">
                <p class="text-xs font-medium !text-slate-500">Rentabilidad Promedio</p>
                <div class="mt-2 text-lg font-semibold {{ $estadisticas_globales['rentabilidad_promedio'] >= 0 ? '!text-emerald-600' : '!text-rose-600' }}">
                    ${{ number_format($estadisticas_globales['rentabilidad_promedio'], 2) }}/tn
                </div>
            </div>
        </div>

        <!-- GRÁFICO 1: Producción vs Punto de Equilibrio (últimos 30 días) -->
        <div class="mb-6">
            <div class="rounded-lg !bg-white shadow-sm">
                <div class="border-b !border-slate-200 px-4 py-3">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <h2 class="text-sm font-semibold !text-slate-900">Producción vs Punto de Equilibrio ({{ $rango_label ?? '' }})</h2>
                        <form method="GET" action="{{ route('reportes.estadisticas-forestales') }}" class="flex flex-nowrap items-center gap-2">
                            <input type="date" name="desde" value="{{ $filtro_desde ?? '' }}" max="{{ now()->toDateString() }}"
                                class="h-8 w-32 min-w-[8rem] rounded-md border !border-slate-300 px-2 text-xs !text-slate-900" />
                            <span class="text-xs !text-slate-400">—</span>
                            <input type="date" name="hasta" value="{{ $filtro_hasta ?? '' }}" max="{{ now()->toDateString() }}"
                                class="h-8 w-32 min-w-[8rem] rounded-md border !border-slate-300 px-2 text-xs !text-slate-900" />
                            <button type="submit" class="h-8 rounded-md px-3 text-xs font-semibold !text-white hover:!bg-slate-800 whitespace-nowrap" style="background:#0f172a; border:1px solid #0f172a;">
                                Aplicar
                            </button>
                        </form>
                    </div>
                </div>
                <div class="p-4">
                    <div id="chartProduccion"></div>
                </div>
            </div>
        </div>

        <!-- GRÁFICOS 2 y 3 en una fila -->
        <div class="grid grid-cols-1 gap-4 mb-6 lg:grid-cols-2">
            <!-- GRÁFICO 2: Distribución de Costos -->
            <div class="rounded-lg !bg-white shadow-sm">
                <div class="border-b !border-slate-200 px-4 py-3">
                    <h2 class="text-sm font-semibold !text-slate-900">Distribución de Costos</h2>
                </div>
                <div class="p-4">
                    <div id="chartDistribucion"></div>
                </div>
            </div>

            <!-- GRÁFICO 3: Evolución Costo por Tonelada -->
            <div class="rounded-lg !bg-white shadow-sm">
                <div class="border-b !border-slate-200 px-4 py-3">
                    <h2 class="text-sm font-semibold !text-slate-900">Evolución Costo por Tonelada ({{ $rango_label ?? '' }})</h2>
                </div>
                <div class="p-4">
                    <div id="chartEvolucion"></div>
                </div>
            </div>
        </div>

        <!-- TABLA: Detalle por Lote -->
        <div class="rounded-lg !bg-white shadow-sm">
            <div class="border-b !border-slate-200 px-4 py-3">
                <h2 class="text-sm font-semibold !text-slate-900">Detalle por Lote</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full table-fixed !divide-y !divide-slate-200">
                        <colgroup>
                            <col style="width: 25%;">
                            <col style="width: 10%;">
                            <col style="width: 12%;">
                            <col style="width: 12%;">
                            <col style="width: 12%;">
                            <col style="width: 12%;">
                            <col style="width: 17%;">
                        </colgroup>
                        <thead class="!bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider !text-slate-500">Nombre del Lote</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider !text-slate-500">Hectáreas</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider !text-slate-500">Precio Promedio</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider !text-slate-500">Costo Promedio</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider !text-slate-500">Punto Equilibrio</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider !text-slate-500">Rentabilidad</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider !text-slate-500">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="!divide-y !divide-slate-100">
                            @foreach($lotes_estadisticas as $stat)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold !text-slate-900 break-words">
                                        {{ $stat['nombre'] ?? 'Sin nombre' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm !text-slate-700">{{ number_format($stat['hectareas'], 2) }} ha</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex items-center rounded-full !bg-emerald-100 px-2.5 py-1 text-xs font-semibold !text-emerald-700">
                                            ${{ number_format($stat['precio_promedio'], 2) }}/tn
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex items-center rounded-full !bg-amber-100 px-2.5 py-1 text-xs font-semibold !text-amber-700">
                                            ${{ number_format($stat['costo_promedio'], 2) }}/tn
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex items-center rounded-full !bg-sky-100 px-2.5 py-1 text-xs font-semibold !text-sky-700">
                                            ${{ number_format($stat['punto_equilibrio'], 2) }}/tn
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex items-center rounded-full {{ $stat['rentabilidad'] >= 0 ? '!bg-emerald-100 !text-emerald-700' : '!bg-rose-100 !text-rose-700' }} px-2.5 py-1 text-xs font-semibold">
                                            ${{ number_format($stat['rentabilidad'], 2) }}/tn
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex items-center rounded-full {{ $stat['rentabilidad'] >= 0 ? '!bg-emerald-100 !text-emerald-700' : '!bg-rose-100 !text-rose-700' }} px-2.5 py-1 text-xs font-semibold">
                                            {{ $stat['rentabilidad'] >= 0 ? 'Rentable' : 'No Rentable' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
            </div>
        </div>

        <!-- Información y Recomendaciones -->
        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-lg border !border-slate-200 !bg-white p-4 shadow-sm">
                <h3 class="text-sm font-semibold !text-slate-900">Cómo interpretar los datos</h3>
                <ul class="mt-3 space-y-2 text-sm !text-slate-600">
                    <li><span class="font-semibold !text-slate-900">Precio Promedio:</span> Precio unitario esperado por tonelada.</li>
                    <li><span class="font-semibold !text-slate-900">Costo Promedio:</span> Costo operacional por tonelada (insumos + maquinaria + mano de obra).</li>
                    <li><span class="font-semibold !text-slate-900">Punto de Equilibrio:</span> Precio mínimo para no perder dinero.</li>
                    <li><span class="font-semibold !text-slate-900">Rentabilidad:</span> Diferencia entre ingreso y costo (Precio - Costo).</li>
                </ul>
            </div>
            <div class="rounded-lg border !border-slate-200 !bg-white p-4 shadow-sm">
                <h3 class="text-sm font-semibold !text-slate-900">Recomendaciones</h3>
                <ul class="mt-3 space-y-2 text-sm !text-slate-600">
                    <li>Si rentabilidad &gt; 0: El lote es rentable.</li>
                    <li>Si rentabilidad ≈ 0: Estar atento a variaciones de costo.</li>
                    <li>Si rentabilidad &lt; 0: El lote está perdiendo dinero.</li>
                    <li>Revisar constantemente para optimizar operaciones.</li>
                </ul>
            </div>
        </div>

        <div class="mt-6 rounded-lg border !border-slate-200 !bg-white p-4 text-sm !text-slate-700 shadow-sm">
            <span class="font-semibold !text-slate-900">Resumen:</span> Analizando {{ $estadisticas_globales['total_lotes'] }} lote(s) activo(s) 
            con rentabilidad promedio de <span class="font-semibold !text-slate-900">${{ number_format($estadisticas_globales['rentabilidad_promedio'], 2) }}/tn</span>.
        </div>
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
    const colorRojo = '#EF4444';
    const colorVerde = '#10B981';
    const colorAzul = '#3B82F6';
    const colorAmarillo = '#F59E0B';

    // ========== GRÁFICO 1: Producción vs Punto de Equilibrio ==========
    const optionsProduccion = {
        chart: {
            type: 'bar',
            height: 400,
            toolbar: { show: true, tools: { download: true, selection: true, zoom: true, zoomin: true, zoomout: true, pan: true, reset: true } }
        },
        colors: [colorVerde],
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
            title: { text: 'Fechas ({{ $rango_label ?? "" }})' }
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
                    borderColor: colorRojo,
                    label: {
                        borderColor: colorRojo,
                        style: {
                            color: '#fff',
                            background: colorRojo,
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
        colors: ['#F59E0B', '#EF4444', '#3B82F6'],
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
        colors: [colorAzul],
        dataLabels: { enabled: false },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        xaxis: {
            categories: @json($fechas_6_meses),
            title: { text: 'Período ({{ $rango_label ?? "" }})' }
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
