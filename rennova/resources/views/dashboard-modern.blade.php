<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rennova - Panel de Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        forest: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#1e7e34',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 antialiased">
    
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
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Selector de Lotes -->
                        <div class="relative">
                            <select class="appearance-none bg-white border border-gray-200 rounded-xl px-4 py-3 pr-10 text-sm font-medium text-gray-700 hover:border-forest-600 focus:outline-none focus:ring-2 focus:ring-forest-500 focus:border-transparent transition-all duration-200 cursor-pointer">
                                <option>Todos los lotes</option>
                                <option>Lote A - Zona Norte</option>
                                <option>Lote B - Zona Sur</option>
                                <option>Lote C - Zona Este</option>
                                <option>Lote D - Zona Oeste</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Botón Actualizar -->
                        <button class="bg-forest-700 hover:bg-forest-800 text-white font-medium px-6 py-3 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-center gap-2 active:scale-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Actualizar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- KPIs Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Días Perdidos -->
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 sm:p-8">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Días Perdidos</p>
                            <p class="text-5xl font-bold text-gray-900 mt-2">12</p>
                        </div>
                        <div class="bg-red-50 p-3 rounded-xl">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Indicador de causa -->
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Causas Principales</p>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Lluvia intensa</span>
                                <span class="bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">8 días</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Mantenimiento</span>
                                <span class="bg-orange-100 text-orange-700 text-xs font-semibold px-3 py-1 rounded-full">4 días</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Déficit TN -->
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 sm:p-8">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Déficit TN</p>
                            <p class="text-5xl font-bold text-gray-900 mt-2">-245</p>
                        </div>
                        <div class="bg-orange-50 p-3 rounded-xl">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Meta -->
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Meta Mensual</span>
                            <span class="text-sm font-semibold text-gray-700">2,500 TN</span>
                        </div>
                        <!-- Barra de progreso -->
                        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-orange-500 to-orange-600 h-3 rounded-full transition-all duration-500" style="width: 90.2%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">2,255 TN producidas (90.2%)</p>
                    </div>
                </div>
            </div>

            <!-- Widget de Clima -->
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Pronóstico de Operatividad</h2>
                    <span class="text-sm text-gray-500">Próximos 7 días</span>
                </div>
                
                <!-- Cinta de 7 días -->
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4">
                    
                    <!-- Día 1 - Operativo -->
                    <div class="flex flex-col items-center">
                        <p class="text-sm font-semibold text-gray-900 mb-1">Lun</p>
                        <p class="text-xs text-gray-500 mb-3">28 Ene</p>
                        <div class="bg-green-50 p-4 rounded-xl mb-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="w-full bg-green-500 h-2 rounded-full"></div>
                        <p class="text-xs font-medium text-green-700 mt-2">Operativo</p>
                    </div>

                    <!-- Día 2 - Operativo -->
                    <div class="flex flex-col items-center">
                        <p class="text-sm font-semibold text-gray-900 mb-1">Mar</p>
                        <p class="text-xs text-gray-500 mb-3">29 Ene</p>
                        <div class="bg-green-50 p-4 rounded-xl mb-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                            </svg>
                        </div>
                        <div class="w-full bg-green-500 h-2 rounded-full"></div>
                        <p class="text-xs font-medium text-green-700 mt-2">Operativo</p>
                    </div>

                    <!-- Día 3 - No Operativo -->
                    <div class="flex flex-col items-center">
                        <p class="text-sm font-semibold text-gray-900 mb-1">Mié</p>
                        <p class="text-xs text-gray-500 mb-3">30 Ene</p>
                        <div class="bg-red-50 p-4 rounded-xl mb-3">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div class="w-full bg-red-500 h-2 rounded-full"></div>
                        <p class="text-xs font-medium text-red-700 mt-2">Lluvia</p>
                    </div>

                    <!-- Día 4 - No Operativo -->
                    <div class="flex flex-col items-center">
                        <p class="text-sm font-semibold text-gray-900 mb-1">Jue</p>
                        <p class="text-xs text-gray-500 mb-3">31 Ene</p>
                        <div class="bg-red-50 p-4 rounded-xl mb-3">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div class="w-full bg-red-500 h-2 rounded-full"></div>
                        <p class="text-xs font-medium text-red-700 mt-2">Lluvia</p>
                    </div>

                    <!-- Día 5 - Operativo -->
                    <div class="flex flex-col items-center">
                        <p class="text-sm font-semibold text-gray-900 mb-1">Vie</p>
                        <p class="text-xs text-gray-500 mb-3">01 Feb</p>
                        <div class="bg-green-50 p-4 rounded-xl mb-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="w-full bg-green-500 h-2 rounded-full"></div>
                        <p class="text-xs font-medium text-green-700 mt-2">Operativo</p>
                    </div>

                    <!-- Día 6 - Operativo -->
                    <div class="flex flex-col items-center">
                        <p class="text-sm font-semibold text-gray-900 mb-1">Sáb</p>
                        <p class="text-xs text-gray-500 mb-3">02 Feb</p>
                        <div class="bg-green-50 p-4 rounded-xl mb-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="w-full bg-green-500 h-2 rounded-full"></div>
                        <p class="text-xs font-medium text-green-700 mt-2">Operativo</p>
                    </div>

                    <!-- Día 7 - Operativo -->
                    <div class="flex flex-col items-center">
                        <p class="text-sm font-semibold text-gray-900 mb-1">Dom</p>
                        <p class="text-xs text-gray-500 mb-3">03 Feb</p>
                        <div class="bg-green-50 p-4 rounded-xl mb-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                            </svg>
                        </div>
                        <div class="w-full bg-green-500 h-2 rounded-full"></div>
                        <p class="text-xs font-medium text-green-700 mt-2">Operativo</p>
                    </div>
                </div>
            </div>

            <!-- Accesos Directos (Grid) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Maquinaria -->
                <a href="#" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-8 hover:-translate-y-1 cursor-pointer">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-blue-50 group-hover:bg-blue-100 p-6 rounded-2xl mb-4 transition-colors duration-300">
                            <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">Maquinaria</h3>
                        <p class="text-sm text-gray-500">Gestión de equipos y mantenimientos</p>
                    </div>
                </a>

                <!-- Inventario -->
                <a href="#" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-8 hover:-translate-y-1 cursor-pointer">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-purple-50 group-hover:bg-purple-100 p-6 rounded-2xl mb-4 transition-colors duration-300">
                            <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-purple-600 transition-colors">Inventario</h3>
                        <p class="text-sm text-gray-500">Control de stock y materiales</p>
                    </div>
                </a>

                <!-- Personal -->
                <a href="#" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 p-8 hover:-translate-y-1 cursor-pointer">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-orange-50 group-hover:bg-orange-100 p-6 rounded-2xl mb-4 transition-colors duration-300">
                            <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-orange-600 transition-colors">Personal</h3>
                        <p class="text-sm text-gray-500">Gestión de empleados y asistencia</p>
                    </div>
                </a>

                <!-- Registrar Operaciones - CTA Principal -->
                <a href="#" class="group bg-gradient-to-br from-forest-700 to-forest-800 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 p-8 hover:-translate-y-1 cursor-pointer relative overflow-hidden">
                    <!-- Efecto de brillo -->
                    <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                    
                    <div class="flex flex-col items-center text-center relative z-10">
                        <div class="bg-white bg-opacity-20 group-hover:bg-opacity-30 p-6 rounded-2xl mb-4 transition-all duration-300">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:scale-105 transition-transform">Registrar Operaciones</h3>
                        <p class="text-sm text-green-100">Añadir nueva operación forestal</p>
                    </div>
                    
                    <!-- Badge "Acción Principal" -->
                    <div class="absolute top-4 right-4">
                        <span class="bg-white bg-opacity-20 text-white text-xs font-semibold px-2 py-1 rounded-full">★</span>
                    </div>
                </a>
            </div>

        </div>
    </div>

</body>
</html>
