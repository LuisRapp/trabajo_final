<?php
    $lotes = \App\Models\Lote::query()->orderByDesc('created_at')->get();
    $loteSeleccionadoId = (int) request('lote');
    $lote = null;
    if ($loteSeleccionadoId) {
        $lote = \App\Models\Lote::find($loteSeleccionadoId);
    }
    if (!$lote) {
        $lote = $lotes->first();
    }
    $svc = app(\App\Services\ClimaDecisionService::class);
    $data = $lote ? $svc->analizarYRecomendar($lote) : null;
    
    // Extraer datos
    $diasPerdidos = $data['dias_perdidos'] ?? 0;
    $deficitTn = $data['deficit_tn'] ?? 0;
    $pronostico = $data['pronostico'] ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rennova - Panel de Control</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 antialiased">
    
    <!-- Navegación Superior -->
    <nav class="bg-green-800 border-b border-green-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-white text-2xl font-bold">Rennova</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-green-100 text-sm"><?php echo e(auth()->user()?->name ?? 'Usuario'); ?></span>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="text-green-100 hover:text-white text-sm">Cerrar Sesión</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Container Principal -->
    <div class="min-h-screen p-4 sm:p-6 lg:p-8">
        
        <!-- Header -->
        <div class="max-w-7xl mx-auto mb-8">
            <div class="bg-white rounded-2xl shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <!-- Título -->
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Panel de Control</h1>
                        <p class="text-gray-500 mt-1 text-sm">Gestión Forestal Rennova</p>
                    </div>
                    
                    <!-- Controles -->
                    <form method="GET" action="<?php echo e(route('dashboard')); ?>" class="flex flex-col sm:flex-row gap-3">
                        <div class="relative">
                            <select name="lote" class="appearance-none bg-white border border-gray-200 rounded-xl px-4 py-3 pr-10 text-sm font-medium text-gray-700 hover:border-green-700 focus:outline-none focus:ring-2 focus:ring-green-700 focus:border-transparent transition-all duration-200 cursor-pointer">
                                <?php if($lotes->count() > 0): ?>
                                    <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($op->id_lote); ?>" <?php if(optional($lote)->id_lote === $op->id_lote): echo 'selected'; endif; ?>>
                                            <?php echo e($op->propietario ?? $op->nombre ?? ('Lote #' . $op->id_lote)); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <option>No hay lotes disponibles</option>
                                <?php endif; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-3 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-center gap-2 active:scale-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Actualizar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php if($data): ?>
            <!-- Contenido Principal -->
            <div class="max-w-7xl mx-auto space-y-6">
                
                <!-- KPIs Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Días Perdidos -->
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 sm:p-8">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Días Perdidos</p>
                                <p class="text-5xl font-bold text-gray-900 mt-2"><?php echo e($diasPerdidos); ?></p>
                            </div>
                            <div class="bg-red-50 p-3 rounded-xl">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Período</p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700"><?php echo e($data['rango_perdidos'] ?? 'Últimos 30 días'); ?></span>
                                <?php if($diasPerdidos > 5): ?>
                                    <span class="bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">Alto</span>
                                <?php elseif($diasPerdidos > 2): ?>
                                    <span class="bg-orange-100 text-orange-700 text-xs font-semibold px-3 py-1 rounded-full">Moderado</span>
                                <?php else: ?>
                                    <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">Normal</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Déficit TN -->
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 sm:p-8">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Déficit TN</p>
                                <p class="text-5xl font-bold text-gray-900 mt-2"><?php echo e($deficitTn); ?></p>
                            </div>
                            <div class="bg-orange-50 p-3 rounded-xl">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                </svg>
                            </div>
                        </div>
                        
                        <?php if(isset($data['accion_porcentaje']) && $data['accion_porcentaje'] > 0): ?>
                            <div class="mt-6 pt-6 border-t border-gray-100">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Acción Recomendada</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700">Aumentar producción</span>
                                    <span class="text-sm font-semibold text-orange-700">+<?php echo e($data['accion_porcentaje']); ?>%</span>
                                </div>
                                <?php if(isset($data['accion_dias'])): ?>
                                    <p class="text-xs text-gray-500 mt-2"><?php echo e($data['accion_dias']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Widget de Clima -->
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Pronóstico de Operatividad</h2>
                        <span class="text-sm text-gray-500">Próximos <?php echo e(count($pronostico)); ?> días</span>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4">
                        <?php $__currentLoopData = $pronostico; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $esOperativo = strtoupper($dia['estado'] ?? 'OPERATIVO') === 'OPERATIVO';
                                $razon = $dia['razon'] ?? 'Normal';
                                $diaCorto = mb_substr($dia['dia_semana'] ?? 'Día', 0, 3);
                                $fecha = $dia['fecha_str'] ?? '';
                            ?>
                            <div class="flex flex-col items-center">
                                <p class="text-sm font-semibold text-gray-900 mb-1"><?php echo e(ucfirst($diaCorto)); ?></p>
                                <p class="text-xs text-gray-500 mb-3"><?php echo e($fecha); ?></p>
                                <div class="<?php echo e($esOperativo ? 'bg-green-50' : 'bg-red-50'); ?> p-4 rounded-xl mb-3">
                                    <?php if($esOperativo): ?>
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div class="w-full <?php echo e($esOperativo ? 'bg-green-500' : 'bg-red-500'); ?> h-2 rounded-full"></div>
                                <p class="text-xs font-medium <?php echo e($esOperativo ? 'text-green-700' : 'text-red-700'); ?> mt-2">
                                    <?php echo e($esOperativo ? 'Operativo' : $razon); ?>

                                </p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <!-- Accesos Directos -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <a href="<?php echo e(route('modulos.maquinaria')); ?>" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-8 hover:-translate-y-1">
                        <div class="flex flex-col items-center text-center">
                            <div class="bg-blue-50 group-hover:bg-blue-100 p-6 rounded-2xl mb-4 transition-colors duration-300">
                                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">Maquinaria</h3>
                            <p class="text-sm text-gray-500">Gestión de equipos</p>
                        </div>
                    </a>

                    <a href="<?php echo e(route('modulos.inventario-forestal')); ?>" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-8 hover:-translate-y-1">
                        <div class="flex flex-col items-center text-center">
                            <div class="bg-purple-50 group-hover:bg-purple-100 p-6 rounded-2xl mb-4 transition-colors duration-300">
                                <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-purple-600 transition-colors">Inventario</h3>
                            <p class="text-sm text-gray-500">Control de stock</p>
                        </div>
                    </a>

                    <a href="<?php echo e(route('modulos.personal')); ?>" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-8 hover:-translate-y-1">
                        <div class="flex flex-col items-center text-center">
                            <div class="bg-orange-50 group-hover:bg-orange-100 p-6 rounded-2xl mb-4 transition-colors duration-300">
                                <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-orange-600 transition-colors">Personal</h3>
                            <p class="text-sm text-gray-500">Gestión de empleados</p>
                        </div>
                    </a>

                    <a href="<?php echo e(route('modulos.operaciones')); ?>" class="group bg-gradient-to-br from-green-700 to-green-800 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 p-8 hover:-translate-y-1 relative overflow-hidden">
                        <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                        <div class="flex flex-col items-center text-center relative z-10">
                            <div class="bg-white bg-opacity-20 group-hover:bg-opacity-30 p-6 rounded-2xl mb-4 transition-all duration-300">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white mb-2 group-hover:scale-105 transition-transform">Registrar Operaciones</h3>
                            <p class="text-sm text-green-100">Añadir nueva operación</p>
                        </div>
                        <div class="absolute top-4 right-4">
                            <span class="bg-white bg-opacity-20 text-white text-xs font-semibold px-2 py-1 rounded-full">★</span>
                        </div>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-sm p-12">
                <div class="flex flex-col items-center text-center">
                    <div class="bg-gray-100 p-6 rounded-full mb-4">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No hay lotes configurados</h3>
                    <p class="text-gray-500 mb-6">Crea tu primer lote para comenzar</p>
                    <a href="<?php echo e(route('lotes.index')); ?>" class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-3 rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                        Crear Lote
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
<?php /**PATH D:\trabajo_final\rennova\resources\views\dashboard-standalone.blade.php ENDPATH**/ ?>