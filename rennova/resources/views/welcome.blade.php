<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rennova - Gestion Forestal Inteligente</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<body id="welcome-page" class="antialiased bg-tinta">
    <div class="welcome-shell min-h-screen flex flex-col lg:flex-row">
        <!-- Lado Izquierdo - Imagen (Oculto en moviles) -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
            <img src="{{ asset('images/welcome.jpeg') }}" alt="Operaciones Forestales" class="w-full h-full object-cover opacity-80">

            <!-- Overlay forestal oscuro -->
            <div class="absolute inset-0 bg-gradient-to-r from-pino-oscuro/90 to-pino/80"></div>

            <!-- Contenido -->
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-8">
                <!-- Logo -->
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-pino/30 rounded-2xl">
                        <svg class="w-12 h-12 text-hueso" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                </div>

                <!-- Titulo -->
                <h1 class="text-5xl font-bold text-hueso mb-2">Rennova</h1>
                <h2 class="text-xl text-arena mb-6">Gestion Forestal Inteligente</h2>
            </div>
        </div>

        <!-- Lado Derecho - Login -->
        <div id="login" class="welcome-panel relative w-full lg:w-1/2 overflow-hidden bg-tinta flex items-center justify-center px-6 py-12 lg:py-0">
            <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-pino/20 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-0 left-0 h-64 w-64 rounded-full bg-musgo/20 blur-3xl"></div>

            <div class="welcome-card relative z-10 w-full max-w-md rounded-2xl border border-corteza bg-corteza/90 p-8 shadow-2xl shadow-black/20 backdrop-blur lg:p-10">
                <div class="absolute inset-x-0 -top-px h-1 rounded-t-2xl bg-gradient-to-r from-pino via-musgo to-pino"></div>

                <!-- Encabezado movil -->
                <div class="lg:hidden text-center mb-10">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-pino rounded-2xl mb-4 shadow-lg shadow-black/20">
                        <svg class="w-8 h-8 text-hueso" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-tinta">Rennova</h1>
                </div>

                <!-- Titulo -->
                <h2 class="text-3xl font-bold text-tinta mb-2">Ingresar</h2>
                <p class="text-tinta-suave text-sm mb-8">Accede a tu plataforma de gestion forestal</p>

                <!-- Formulario -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-tinta-suave mb-2">
                            Correo Electronico
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                <svg class="h-4.5 w-4.5 text-tinta-suave" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
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
                                class="welcome-input w-full pl-11 pr-4 py-3 bg-tinta text-hueso border border-corteza rounded-xl shadow-sm focus:outline-hidden focus:ring-2 focus:ring-pino focus:border-pino placeholder:text-tinta-suave transition @error('email') border-tierra @enderror"
                                placeholder="correo@ejemplo.com"
                            >
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-tierra">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contrasena -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-tinta-suave mb-2">
                            Contrasena
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                <svg class="h-4.5 w-4.5 text-tinta-suave" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <rect x="4" y="11" width="16" height="9" rx="2" />
                                    <path d="M8 11V8a4 4 0 1 1 8 0v3" />
                                </svg>
                            </div>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                class="welcome-input w-full pl-11 pr-4 py-3 bg-tinta text-hueso border border-corteza rounded-xl shadow-sm focus:outline-hidden focus:ring-2 focus:ring-pino focus:border-pino placeholder:text-tinta-suave transition @error('password') border-tierra @enderror"
                                placeholder="********"
                            >
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-tierra">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Recuerdame -->
                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-sm text-tinta-suave">
                            <input
                                type="checkbox"
                                name="remember"
                                class="welcome-checkbox h-4 w-4 rounded border border-corteza bg-tinta text-pino focus:ring-pino"
                            >
                            Recuerdame
                        </label>
                    </div>

                    <!-- Boton -->
                    <button
                        type="submit"
                        class="welcome-button w-full inline-flex items-center justify-center rounded-xl bg-pino px-4 py-3 text-white font-bold shadow-lg shadow-black/20 transition hover:bg-pino-oscuro focus:outline-hidden focus:ring-2 focus:ring-pino"
                    >
                        Ingresar
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
