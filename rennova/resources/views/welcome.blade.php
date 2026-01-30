<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rennova - Gestión Forestal Inteligente</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Lado Izquierdo - Imagen (Oculto en móviles) -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
            <img src="{{ asset('images/welcome.jpeg') }}" alt="Operaciones Forestales" class="w-full h-full object-cover opacity-80">
            
            <!-- Overlay verde oscuro -->
            <div class="absolute inset-0 bg-gradient-to-r from-green-950/90 to-green-900/80"></div>
            
            <!-- Contenido -->
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-8">
                <!-- Logo -->
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-500/30 rounded-2xl">
                        <svg class="w-12 h-12 text-green-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Título -->
                <h1 class="text-5xl font-bold text-white mb-2">Rennova</h1>
                <h2 class="text-xl text-green-200 mb-6">Gestión Forestal Inteligente</h2>
                
            </div>
        </div>

        <!-- Lado Derecho - Login -->
        <div id="login" class="w-full lg:w-1/2 bg-gradient-to-br from-gray-50 to-emerald-50 flex items-center justify-center px-6 py-12 lg:py-0">
            <div class="w-full max-w-sm">
                <!-- Encabezado móvil -->
                <div class="lg:hidden text-center mb-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-600 rounded-xl mb-4">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">Rennova</h1>
                </div>

                <!-- Título -->
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Ingresar</h2>
                <p class="text-gray-600 text-sm mb-8">Accede a tu plataforma de gestión forestal</p>

                <!-- Formulario -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            Correo Electrónico
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                <svg class="h-4.5 w-4.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="2" ry="2" />
                                    <path d="M3 7l9 6 9-6" />
                                </svg>
                            </div>
                            <input 
                                type="email" 
                                id="email" 
                                name="email"
                                value="{{ old('email') }}"
                                required 
                                autofocus
                                class="w-full pl-11 pr-4 py-2.5 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent placeholder:text-gray-400 autofill:bg-white autofill:text-gray-900 @error('email') border-red-500 @enderror"
                                placeholder="correo@ejemplo.com"
                            >
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                <svg class="h-4.5 w-4.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <rect x="4" y="11" width="16" height="9" rx="2" />
                                    <path d="M8 11V8a4 4 0 1 1 8 0v3" />
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                name="password"
                                required
                                class="w-full pl-11 pr-4 py-2.5 bg-white text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent placeholder:text-gray-400 autofill:bg-white autofill:text-gray-900 @error('password') border-red-500 @enderror"
                                placeholder="••••••••"
                            >
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                   

                    <!-- Botón -->
                    <button 
                        type="submit"
                        class="w-full inline-flex items-center justify-center !bg-green-600 hover:!bg-green-700 !text-white font-bold py-2.5 rounded-lg shadow-md transition duration-200"
                    >
                        Ingresar
                    </button>
                </form>

            </div>
        </div>
    </div>
</body>
</html>
